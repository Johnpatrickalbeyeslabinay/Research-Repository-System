<?php

namespace App\Services\Ai;

use App\Exceptions\AbstractGenerationException;
use App\Exceptions\AiQuotaExceededException;
use App\Models\AiUsageLog;
use App\Support\SubmissionAiQuota;
use App\Support\TokenEstimator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Drafts an abstract for a research PDF that has none, from a bounded
 * excerpt of the paper (SectionDetector::abstractSourceExcerpt()), only
 * when the student explicitly asks for it. The draft lands in the form's
 * preview box for the student to accept or edit — it is never submitted
 * on their behalf.
 *
 * The client never sends paper text here. StudentController::analyzePdf()
 * stores the excerpt server-side under the PDF's hash (storeSource()), and
 * generate() takes only that hash — so the prompt can't be swapped for
 * arbitrary text billed to our key, and the request stays tiny.
 *
 * Results are cached by PDF hash + prompt version: reloading the page and
 * re-picking the same file returns the same draft instantly, with no API
 * call and no charge against the student's SubmissionAiQuota.
 */
class AbstractGenerationService
{
    // Bump when the prompt or schema changes so old cached drafts are
    // treated as stale rather than silently reused.
    private const PROMPT_VERSION = 'v1';

    // How long analyzePdf()'s stored excerpt stays available for a
    // generate() call — long enough for a student to fill in the rest of
    // the form first.
    private const SOURCE_TTL_HOURS = 24;

    private const RESULT_TTL_DAYS = 7;

    // A draft sharing at least this share of its distinct words with the
    // submitted abstract still counts as AI-generated (the student was
    // told to review and edit it, so light edits are expected).
    private const SIMILARITY_THRESHOLD = 0.6;

    public function __construct(private TokenEstimator $tokens) {}

    public function storeSource(string $pdfHash, string $excerpt): void
    {
        Cache::put($this->sourceKey($pdfHash), $excerpt, now()->addHours(self::SOURCE_TTL_HOURS));
    }

    /**
     * @return array{abstract: string, model: string, cached: bool}
     *
     * @throws AbstractGenerationException
     * @throws AiQuotaExceededException
     */
    public function generate(string $pdfHash, string $title): array
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            throw AbstractGenerationException::notConfigured();
        }

        // Checked before the source: a cached draft is still valid after
        // the 24h source excerpt has expired.
        $cached = Cache::get($this->resultKey($pdfHash));

        if (is_array($cached)) {
            return ['cached' => true] + $cached;
        }

        $source = Cache::get($this->sourceKey($pdfHash));

        if (! is_string($source) || trim($source) === '') {
            throw AbstractGenerationException::sourceExpired();
        }

        SubmissionAiQuota::consume(Auth::id());

        $result = $this->callOpenAi($apiKey, $title, $source);

        Cache::put($this->resultKey($pdfHash), $result, now()->addDays(self::RESULT_TTL_DAYS));

        return ['cached' => false] + $result;
    }

    /**
     * Whether $abstract is (a lightly edited version of) the draft this
     * service generated for the PDF — used to record abstract_source
     * honestly on submit, without trusting a client-sent flag.
     */
    public function matchesGenerated(string $pdfHash, ?string $abstract): bool
    {
        $cached = Cache::get($this->resultKey($pdfHash));

        if (! is_array($cached) || ! isset($cached['abstract']) || $abstract === null) {
            return false;
        }

        $draftWords = $this->wordSet($cached['abstract']);
        $submittedWords = $this->wordSet($abstract);

        if ($draftWords === [] || $submittedWords === []) {
            return false;
        }

        $shared = count(array_intersect_key($draftWords, $submittedWords));
        $union = count($draftWords + $submittedWords);

        return $shared / $union >= self::SIMILARITY_THRESHOLD;
    }

    /**
     * @return array{abstract: string, model: string}
     *
     * @throws AbstractGenerationException
     */
    private function callOpenAi(string $apiKey, string $title, string $source): array
    {
        $requestId = (string) Str::uuid();
        $start = microtime(true);
        $model = config('services.openai.abstract_model');
        $maxInputTokens = (int) config('services.openai.abstract_max_input_tokens', 3000);
        $maxOutputTokens = (int) config('services.openai.abstract_max_output_tokens', 450);

        $userContent = "Title: {$title}\n\nExcerpts from the paper:\n{$source}";

        // Same converging trim as MetadataAnalysisService: measured with
        // TokenEstimator, cutting from the end (the later sections) first.
        while ($userContent !== '' && $this->tokens->estimate($userContent) > $maxInputTokens) {
            $overBy = $this->tokens->estimate($userContent) - $maxInputTokens;
            $userContent = mb_substr($userContent, 0, max(1, mb_strlen($userContent) - max(1, $overBy * 2)));
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'max_tokens' => $maxOutputTokens,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You write abstracts for student research papers. Using ONLY information '
                                .'present in the provided excerpts, write a single-paragraph academic abstract of '
                                .'150-250 words that states the purpose, the method, the key findings and the '
                                .'conclusion. Do not invent numbers, results, places or participants that are not in '
                                .'the excerpts; if something is not stated, leave it out. Write in the third person '
                                .'and past tense, with no heading and no citations.',
                        ],
                        ['role' => 'user', 'content' => $userContent],
                    ],
                    'response_format' => [
                        'type' => 'json_schema',
                        'json_schema' => [
                            'name' => 'research_abstract',
                            'strict' => true,
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'abstract' => ['type' => 'string'],
                                ],
                                'required' => ['abstract'],
                                'additionalProperties' => false,
                            ],
                        ],
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Abstract AI call failed', ['error' => $e->getMessage()]);

            $this->recordUsage($requestId, $model, null, $start, false, 'transport');

            throw AbstractGenerationException::unavailable();
        }

        if ($response->failed()) {
            Log::warning('Abstract AI call returned an error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $this->recordUsage($requestId, $model, null, $start, false, 'http_'.$response->status());

            throw AbstractGenerationException::unavailable();
        }

        $content = $response->json('choices.0.message.content');
        $parsed = is_string($content) ? json_decode($content, true) : null;
        $abstract = is_array($parsed) && is_string($parsed['abstract'] ?? null)
            ? trim(preg_replace('/\s+/', ' ', $parsed['abstract']))
            : '';

        if ($abstract === '') {
            $this->recordUsage($requestId, $model, $response, $start, false, 'invalid_response');

            throw AbstractGenerationException::invalidResponse();
        }

        $this->recordUsage($requestId, $model, $response, $start, true, null);

        // Same cap as the form's 'desc' rule (StoreResearchRequest).
        return ['abstract' => mb_substr($abstract, 0, 5000), 'model' => $model];
    }

    /**
     * Logged only from callOpenAi(), i.e. on a cache miss — see
     * MetadataAnalysisService::recordUsage() for why.
     *
     * @param  \Illuminate\Http\Client\Response|null  $response
     */
    private function recordUsage(string $requestId, string $model, $response, float $start, bool $success, ?string $errorCode): void
    {
        $promptTokens = $response?->json('usage.prompt_tokens');
        $completionTokens = $response?->json('usage.completion_tokens');

        AiUsageLog::record([
            'user_id' => Auth::id(),
            'request_id' => $requestId,
            'operation' => 'abstract',
            'model' => $model,
            'prompt_tokens' => $promptTokens,
            'cached_prompt_tokens' => $response?->json('usage.prompt_tokens_details.cached_tokens'),
            'completion_tokens' => $completionTokens,
            'total_tokens' => ($promptTokens ?? 0) + ($completionTokens ?? 0),
            'latency_ms' => (int) round((microtime(true) - $start) * 1000),
            'success' => $success,
            'error_code' => $errorCode,
        ]);
    }

    /** @return array<string, true> */
    private function wordSet(string $text): array
    {
        preg_match_all('/\p{L}[\p{L}\p{N}\'-]*/u', mb_strtolower($text), $matches);

        return array_fill_keys($matches[0], true);
    }

    private function sourceKey(string $pdfHash): string
    {
        return "ai_abstract_src:{$pdfHash}";
    }

    private function resultKey(string $pdfHash): string
    {
        return 'ai_abstract:'.$pdfHash.':'.self::PROMPT_VERSION;
    }
}

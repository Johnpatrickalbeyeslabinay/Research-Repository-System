<?php

namespace App\Services\Ai;

use App\Exceptions\AiQuotaExceededException;
use App\Exceptions\MetadataAnalysisException;
use App\Models\AiUsageLog;
use App\Support\SubmissionAiQuota;
use App\Support\TokenEstimator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Generates keyword + SDG suggestions from a research title and abstract
 * only — never the full document. One structured OpenAI call produces both
 * fields together. Results are cached by a fingerprint of the (normalized)
 * inputs + prompt version, so re-analyzing unchanged content never re-hits
 * the API or counts against the student's daily SubmissionAiQuota.
 */
class MetadataAnalysisService
{
    // Bump this when the prompt or schema changes so old cached/stored
    // results are treated as stale rather than silently reused.
    // v2: sanitizeSdgs() now returns 'number' instead of 'id' — see below.
    // v3: prompt also grounded in a paper excerpt + the author's own keywords.
    private const PROMPT_VERSION = 'v3';

    // These are SDG *numbers* (1-17, the fixed UN numbering used in the
    // prompt below), not sdgs.id database row ids — the two are easy to
    // conflate because they happen to match on a freshly-seeded table.
    // Resolving a number to an actual row id is ResearchAnalysisController's
    // job, not this service's; this class never touches the database.
    private const VALID_SDG_NUMBERS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17];

    private const MIN_KEYWORDS = 5;

    private const MAX_KEYWORDS = 8;

    private const MAX_SDGS = 3;

    private const MIN_SDG_CONFIDENCE = 0.5;

    public function __construct(private TokenEstimator $tokens) {}

    public function fingerprint(string $title, string $abstract): string
    {
        $normalized = $this->normalize($title).'|'.$this->normalize($abstract);

        return hash('sha256', $normalized.'|'.self::PROMPT_VERSION);
    }

    /**
     * @param  ?string  $fallbackExcerpt  small intro excerpt from the PDF, so
     *                                    suggestions reflect the paper itself
     *                                    and not only its abstract
     * @param  string[]  $existingKeywords  keywords the author already listed
     *                                      in the PDF — the model is asked to
     *                                      complement rather than repeat them
     * @return array{keywords: string[], sdgs: array<int, array{number: int, confidence: float}>, model: string, fingerprint: string, cached: bool}
     *
     * @throws MetadataAnalysisException
     * @throws AiQuotaExceededException
     */
    public function analyze(string $title, string $abstract, ?string $fallbackExcerpt = null, array $existingKeywords = []): array
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            throw MetadataAnalysisException::notConfigured();
        }

        $existingKeywords = $this->sanitizeKeywords($existingKeywords);
        $fingerprint = $this->fingerprint($title, $abstract);

        // The cache key covers every input that reaches the prompt — the
        // excerpt and author keywords too — while fingerprint() stays
        // title+abstract only, because StudentController::applyAiAuditFields()
        // recomputes it from the submitted title/desc to verify the audit
        // fields.
        $cacheKey = 'ai_metadata:'.$fingerprint.':'.sha1(
            $this->normalize((string) $fallbackExcerpt).'|'.implode('|', array_map(fn ($k) => mb_strtolower($k), $existingKeywords))
        );

        // Explicit get/put rather than Cache::remember(): the daily quota
        // must be charged only when the API is actually about to be called,
        // so a hit (page reload, re-click on unchanged text) stays free.
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return ['cached' => true] + $cached;
        }

        SubmissionAiQuota::consume(Auth::id());

        $result = $this->callOpenAi($apiKey, $title, $abstract, $fallbackExcerpt, $existingKeywords, $fingerprint);

        Cache::put($cacheKey, $result, now()->addDays(7));

        return ['cached' => false] + $result;
    }

    /**
     * Writes the 'metadata' row to ai_usage_logs. Deliberately HERE and not
     * in analyze(): analyze() wraps this in a 7-day cache, so logging there
     * would report every cache hit as fresh API spend. This method only runs
     * on a miss, which makes it the only honest place to record the cost.
     *
     * (Until this existed, ai_usage_logs had no 'metadata' rows at all,
     * despite that operation being named in the table's own migration
     * comment — so keyword/SDG suggestion spend was invisible.)
     *
     * @return array{keywords: string[], sdgs: array<int, array{number: int, confidence: float}>, model: string, fingerprint: string}
     *
     * @throws MetadataAnalysisException
     */
    private function callOpenAi(string $apiKey, string $title, string $abstract, ?string $fallbackExcerpt, array $existingKeywords, string $fingerprint): array
    {
        $requestId = (string) Str::uuid();
        $start = microtime(true);
        $model = config('services.openai.metadata_model');
        $maxInputTokens = (int) config('services.openai.metadata_max_input_tokens', 2000);
        $maxOutputTokens = (int) config('services.openai.metadata_max_output_tokens', 300);

        $userContent = "Title: {$title}\n\nAbstract: {$abstract}";

        if ($existingKeywords !== []) {
            $userContent .= "\n\nKeywords the author already listed: ".implode(', ', $existingKeywords);
        }

        if ($fallbackExcerpt !== null && trim($fallbackExcerpt) !== '') {
            $userContent .= "\n\nExcerpt from the paper's introduction: {$fallbackExcerpt}";
        }

        // Applied unconditionally: previously this only ran when a
        // fallback excerpt was supplied, which the form never sends, so
        // title+abstract alone (the normal path, every single call) went
        // out completely uncapped regardless of METADATA_MAX_INPUT_TOKENS.
        //
        // Trimmed against App\Support\TokenEstimator rather than a flat
        // chars-per-token multiplier, so an abstract dense with numbers and
        // symbols is measured at what it really costs instead of a quarter
        // of it. Trimming by characters and re-measuring converges quickly
        // because the estimate is monotonic in length.
        while ($userContent !== '' && $this->tokens->estimate($userContent) > $maxInputTokens) {
            $overBy = $this->tokens->estimate($userContent) - $maxInputTokens;

            // Cut at least one character so this always terminates, and
            // scale the cut by how far over budget we are so a long abstract
            // does not take thousands of iterations.
            $userContent = mb_substr($userContent, 0, max(1, mb_strlen($userContent) - max(1, $overBy * 2)));
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'max_tokens' => $maxOutputTokens,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You classify academic research metadata. Given a title, abstract and '
                                .'possibly an excerpt from the paper itself, suggest 5-8 specific, domain-relevant '
                                .'keywords grounded in what the paper actually studies (avoid generic terms like '
                                .'"system", "study", "research", "technology" unless truly central; do not repeat '
                                .'keywords the author already listed) and 1-3 '
                                .'UN Sustainable Development Goals (1-17) that genuinely relate to the research, '
                                .'with a confidence from 0 to 1 for each. Only suggest SDGs you are reasonably '
                                .'confident about — do not pad the list to appear broadly applicable.',
                        ],
                        ['role' => 'user', 'content' => $userContent],
                    ],
                    'response_format' => [
                        'type' => 'json_schema',
                        'json_schema' => [
                            'name' => 'research_metadata',
                            'strict' => true,
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'keywords' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'string'],
                                    ],
                                    'sdgs' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'number' => ['type' => 'integer'],
                                                'confidence' => ['type' => 'number'],
                                            ],
                                            'required' => ['number', 'confidence'],
                                            'additionalProperties' => false,
                                        ],
                                    ],
                                ],
                                'required' => ['keywords', 'sdgs'],
                                'additionalProperties' => false,
                            ],
                        ],
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Metadata AI call failed', ['error' => $e->getMessage()]);

            $this->recordUsage($requestId, $model, null, $start, false, 'transport');

            throw MetadataAnalysisException::unavailable();
        }

        if ($response->failed()) {
            Log::warning('Metadata AI call returned an error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $this->recordUsage($requestId, $model, null, $start, false, 'http_'.$response->status());

            throw MetadataAnalysisException::unavailable();
        }

        $content = $response->json('choices.0.message.content');
        $parsed = is_string($content) ? json_decode($content, true) : null;

        if (! is_array($parsed) || ! isset($parsed['keywords']) || ! isset($parsed['sdgs'])) {
            // Tokens were still spent producing the unusable response, so
            // this is logged as a failure WITH its cost rather than skipped.
            $this->recordUsage($requestId, $model, $response, $start, false, 'invalid_response');

            throw MetadataAnalysisException::invalidResponse();
        }

        $this->recordUsage($requestId, $model, $response, $start, true, null);

        $alreadyListed = array_flip(array_map(fn ($k) => mb_strtolower($k), $existingKeywords));

        return [
            'keywords' => array_values(array_filter(
                $this->sanitizeKeywords($parsed['keywords']),
                fn ($k) => ! isset($alreadyListed[mb_strtolower($k)])
            )),
            'sdgs' => $this->sanitizeSdgs($parsed['sdgs']),
            'model' => $model,
            'fingerprint' => $fingerprint,
        ];
    }

    /**
     * Fire-and-forget cost telemetry (AiUsageLog::record swallows its own
     * failures, so this can never break a submission).
     *
     * Auth::id() is read here rather than threaded in from
     * ResearchAnalysisController: this is the only place that knows whether
     * an API call actually happened, and a null user_id is a valid row, so
     * the coupling buys correctness cheaply. It is nullable precisely
     * because this can also run from a queue or a console command.
     *
     * @param  \Illuminate\Http\Client\Response|null  $response
     */
    private function recordUsage(
        string $requestId,
        string $model,
        $response,
        float $start,
        bool $success,
        ?string $errorCode,
    ): void {
        $promptTokens = $response?->json('usage.prompt_tokens');
        $completionTokens = $response?->json('usage.completion_tokens');

        AiUsageLog::record([
            'user_id' => Auth::id(),
            'request_id' => $requestId,
            'operation' => 'metadata',
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

    /**
     * @return string[]
     */
    private function sanitizeKeywords(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $keywords = [];
        $seen = [];

        foreach ($raw as $keyword) {
            if (! is_string($keyword)) {
                continue;
            }

            $keyword = trim(preg_replace('/\s+/', ' ', $keyword));

            if ($keyword === '' || mb_strlen($keyword) > 100) {
                continue;
            }

            $normalized = mb_strtolower($keyword);

            if (isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $keywords[] = $keyword;

            if (count($keywords) >= self::MAX_KEYWORDS) {
                break;
            }
        }

        // Fewer than MIN_KEYWORDS is still returned as-is — better to show
        // 3 good suggestions than none; the UI treats this as advisory.
        return $keywords;
    }

    /**
     * @return array<int, array{number: int, confidence: float}>
     */
    private function sanitizeSdgs(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $sdgs = [];
        $seenNumbers = [];

        // Highest confidence first, so truncating to MAX_SDGS keeps the
        // strongest suggestions if the model returned more than expected.
        usort($raw, fn ($a, $b) => ($b['confidence'] ?? 0) <=> ($a['confidence'] ?? 0));

        foreach ($raw as $entry) {
            if (! is_array($entry) || ! isset($entry['number'], $entry['confidence'])) {
                continue;
            }

            $number = (int) $entry['number'];
            $confidence = (float) $entry['confidence'];

            if (! in_array($number, self::VALID_SDG_NUMBERS, true)) {
                continue;
            }

            if ($confidence < self::MIN_SDG_CONFIDENCE) {
                continue;
            }

            if (isset($seenNumbers[$number])) {
                continue;
            }

            $seenNumbers[$number] = true;
            $sdgs[] = ['number' => $number, 'confidence' => round(min(1.0, max(0.0, $confidence)), 2)];

            if (count($sdgs) >= self::MAX_SDGS) {
                break;
            }
        }

        return $sdgs;
    }

    private function normalize(string $text): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $text)));
    }
}

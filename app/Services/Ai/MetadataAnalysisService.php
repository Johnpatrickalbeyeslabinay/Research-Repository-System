<?php

namespace App\Services\Ai;

use App\Exceptions\MetadataAnalysisException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generates keyword + SDG suggestions from a research title and abstract
 * only — never the full document. One structured OpenAI call produces both
 * fields together. Results are cached by a fingerprint of the (normalized)
 * title + abstract + prompt version, so re-analyzing unchanged content never
 * re-hits the API.
 */
class MetadataAnalysisService
{
    // Bump this when the prompt or schema changes so old cached/stored
    // results are treated as stale rather than silently reused.
    // v2: sanitizeSdgs() now returns 'number' instead of 'id' — see below.
    private const PROMPT_VERSION = 'v2';

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

    public function fingerprint(string $title, string $abstract): string
    {
        $normalized = $this->normalize($title).'|'.$this->normalize($abstract);

        return hash('sha256', $normalized.'|'.self::PROMPT_VERSION);
    }

    /**
     * @param  ?string  $fallbackExcerpt  small intro/conclusion excerpt, only
     *                                    used when the abstract is missing/too short
     * @return array{keywords: string[], sdgs: array<int, array{number: int, confidence: float}>, model: string, fingerprint: string}
     *
     * @throws MetadataAnalysisException
     */
    public function analyze(string $title, string $abstract, ?string $fallbackExcerpt = null): array
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            throw MetadataAnalysisException::notConfigured();
        }

        $fingerprint = $this->fingerprint($title, $abstract);

        return Cache::remember(
            "ai_metadata:{$fingerprint}",
            now()->addDays(7),
            fn () => $this->callOpenAi($apiKey, $title, $abstract, $fallbackExcerpt, $fingerprint)
        );
    }

    /**
     * @return array{keywords: string[], sdgs: array<int, array{number: int, confidence: float}>, model: string, fingerprint: string}
     *
     * @throws MetadataAnalysisException
     */
    private function callOpenAi(string $apiKey, string $title, string $abstract, ?string $fallbackExcerpt, string $fingerprint): array
    {
        $model = config('services.openai.metadata_model');
        $maxInputTokens = (int) config('services.openai.metadata_max_input_tokens', 2000);
        $maxOutputTokens = (int) config('services.openai.metadata_max_output_tokens', 300);

        $userContent = "Title: {$title}\n\nAbstract: {$abstract}";

        if ($fallbackExcerpt !== null) {
            $userContent .= "\n\nAdditional excerpt: {$fallbackExcerpt}";
        }

        // Rough char budget so we never blow past the configured input
        // token ceiling — ~4 chars/token is a standard rough estimate.
        // Applied unconditionally: previously this only ran when a
        // fallback excerpt was supplied, which the form never sends, so
        // title+abstract alone (the normal path, every single call) went
        // out completely uncapped regardless of METADATA_MAX_INPUT_TOKENS.
        $charBudget = $maxInputTokens * 4;
        $userContent = mb_substr($userContent, 0, $charBudget);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'max_tokens' => $maxOutputTokens,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You classify academic research metadata. Given a title and abstract, '
                                .'suggest 5-8 specific, domain-relevant keywords (avoid generic terms like '
                                .'"system", "study", "research", "technology" unless truly central) and 1-3 '
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

            throw MetadataAnalysisException::unavailable();
        }

        if ($response->failed()) {
            Log::warning('Metadata AI call returned an error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw MetadataAnalysisException::unavailable();
        }

        $content = $response->json('choices.0.message.content');
        $parsed = is_string($content) ? json_decode($content, true) : null;

        if (! is_array($parsed) || ! isset($parsed['keywords']) || ! isset($parsed['sdgs'])) {
            throw MetadataAnalysisException::invalidResponse();
        }

        return [
            'keywords' => $this->sanitizeKeywords($parsed['keywords']),
            'sdgs' => $this->sanitizeSdgs($parsed['sdgs']),
            'model' => $model,
            'fingerprint' => $fingerprint,
        ];
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

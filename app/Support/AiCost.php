<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Turns the token counts in ai_usage_logs into money.
 *
 * Rates live in config/ai_pricing.php and cost is computed on read, never
 * stored — see that file's header for why a cost column would be a bug
 * rather than an optimisation.
 *
 * Used by App\Console\Commands\AiCostReportCommand and by the token report
 * in App\Console\Commands\EvaluateChatbotCommand, which is what makes
 * "we reduced token usage" a number in dollars instead of an assertion.
 */
final class AiCost
{
    /** Model names that legitimately carry no price. */
    private const PRICELESS_MODELS = ['none', '', 'openalex'];

    /**
     * USD for one call.
     *
     * $cachedPromptTokens is the subset of $promptTokens the provider served
     * from its own prefix cache and billed at the discounted rate; it is
     * subtracted from the full-rate portion rather than added on top.
     */
    public static function usd(
        ?string $model,
        ?int $promptTokens,
        ?int $completionTokens,
        ?int $cachedPromptTokens = null,
    ): float {
        $rates = self::rates($model);

        if ($rates === null) {
            return 0.0;
        }

        $prompt = max(0, (int) $promptTokens);
        $completion = max(0, (int) $completionTokens);

        // Clamp rather than trust: a provider response that reports more
        // cached tokens than prompt tokens would otherwise produce a
        // negative cost, which is worse than a slightly wrong one.
        $cached = min(max(0, (int) $cachedPromptTokens), $prompt);
        $uncached = $prompt - $cached;

        return ($uncached / 1_000_000) * $rates['input']
            + ($cached / 1_000_000) * $rates['cached_input']
            + ($completion / 1_000_000) * $rates['output'];
    }

    /** The same figure in pesos, for local budgeting. */
    public static function php(
        ?string $model,
        ?int $promptTokens,
        ?int $completionTokens,
        ?int $cachedPromptTokens = null,
    ): float {
        return self::usd($model, $promptTokens, $completionTokens, $cachedPromptTokens)
            * (float) config('ai_pricing.php_per_usd', 58.0);
    }

    /** Whether a price is on file — lets a report flag gaps explicitly. */
    public static function isPriced(?string $model): bool
    {
        return self::rates($model) !== null;
    }

    /**
     * @return array{input: float, cached_input: float, output: float}|null
     */
    private static function rates(?string $model): ?array
    {
        $model = (string) $model;

        if (in_array($model, self::PRICELESS_MODELS, true)) {
            return null;
        }

        $rates = config('ai_pricing.models.'.$model);

        if (! is_array($rates)) {
            if (config('ai_pricing.warn_on_unknown_model', true)) {
                // Warn once per model per process, not once per row — a
                // 30-day report over an unpriced model would otherwise
                // write thousands of identical lines.
                static $warned = [];

                if (! isset($warned[$model])) {
                    $warned[$model] = true;
                    Log::warning('No AI price on file for model; cost reported as 0', [
                        'model' => $model,
                        'fix' => 'add it to config/ai_pricing.php models',
                    ]);
                }
            }

            return null;
        }

        return [
            'input' => (float) ($rates['input'] ?? 0.0),
            'cached_input' => (float) ($rates['cached_input'] ?? $rates['input'] ?? 0.0),
            'output' => (float) ($rates['output'] ?? 0.0),
        ];
    }
}

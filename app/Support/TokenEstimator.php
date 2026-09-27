<?php

namespace App\Support;

/**
 * The one place that estimates how many tokens a string will cost.
 *
 * Before this existed, `(int) round(str_word_count($text) * 4 / 3)` was
 * copy-pasted into ChunkingService (twice) and ChunkReranker, and
 * MetadataAnalysisService approximated the same thing as `$maxTokens * 4`
 * characters. Three formulas, no single place to fix, and the formula was
 * not merely rough — it was biased in the direction that breaks a budget.
 *
 * str_word_count() counts runs of [A-Za-z'-] only, so digits and symbols
 * score ZERO. A results-section chunk like:
 *
 *     Mean length was 12.4 ± 0.8 cm (n = 120; p < 0.05)
 *
 * scores ~5 words => ~7 tokens, against a real cost near 28. That is
 * precisely the numeric-dense text the full-text retrieval tier exists to
 * fetch, so config('chat.context.max_evidence_tokens') was admitting far
 * more than the number it names.
 *
 * The fix is max() of a character-based and a word-based estimate. max(),
 * not an average: a budget must never UNDER-estimate, because the
 * consequence of under-estimating is a 400 from the API or a silently
 * truncated prompt, while the consequence of over-estimating is one chunk
 * of evidence fewer. Those are not symmetric.
 *
 * This is still an estimate — there is no BPE tokenizer in this project's
 * composer.json and adding one for budgeting would be a large dependency
 * for a number we only need to be conservative about. Calibrate it against
 * reality with `php artisan research:calibrate-token-estimator`, which
 * compares these estimates to the prompt_tokens OpenAI actually charged.
 */
final class TokenEstimator
{
    /**
     * Per-message framing overhead in the Chat Completions format (role,
     * separators). OpenAI's own token-counting guidance uses 4.
     */
    private const PER_MESSAGE_OVERHEAD = 4;

    /** Per-request framing overhead on top of the per-message cost. */
    private const REQUEST_OVERHEAD = 3;

    public function estimate(string $text): int
    {
        if ($text === '') {
            return 0;
        }

        $charsPerToken = (float) config('chat.estimator.chars_per_token', 4.0);
        $tokensPerWord = (float) config('chat.estimator.tokens_per_word', 4 / 3);

        // Guard against a misconfigured 0 turning this into a division by
        // zero — a broken config should not take the chatbot down.
        $charBased = $charsPerToken > 0
            ? (int) ceil(mb_strlen($text) / $charsPerToken)
            : 0;

        $wordBased = (int) ceil(str_word_count($text) * $tokensPerWord);

        return max($charBased, $wordBased);
    }

    /**
     * The cost of a fully assembled Chat Completions payload, framing
     * included. This — not the sum of the evidence chunks — is the number
     * that has to fit inside the model's context window.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function estimateMessages(array $messages): int
    {
        $total = self::REQUEST_OVERHEAD;

        foreach ($messages as $message) {
            $total += self::PER_MESSAGE_OVERHEAD
                + $this->estimate((string) ($message['content'] ?? ''));
        }

        return $total;
    }
}

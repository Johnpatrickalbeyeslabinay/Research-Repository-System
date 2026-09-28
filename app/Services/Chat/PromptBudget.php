<?php

namespace App\Services\Chat;

use App\Support\TokenEstimator;

/**
 * A ceiling over the ASSEMBLED prompt, not just its evidence.
 *
 * ChunkReranker already budgets the evidence set (4,000 tokens at full text),
 * but that was the only budget in the pipeline — the system prompt (~1,000
 * tokens), the conversation history, and the active-context line all rode free
 * on top of it. Nothing bounded their sum, so the real prompt had no ceiling at
 * all and a long conversation about a verbose document could drift well past
 * what anyone intended to pay for.
 *
 * ORDER OF SACRIFICE. Which thing gets dropped first IS the design here, so it
 * is stated rather than left to whatever the loop happens to do:
 *
 *   1. Oldest history, a turn-pair at a time. The least valuable tokens in the
 *      prompt — a question from four turns ago rarely changes this answer.
 *   2. The active-context document list. Already capped, and losing it costs
 *      the ability to resolve "the second one", not the answer itself.
 *   3. Evidence, from the tail. Lowest-ranked chunks first, and NEVER the first
 *      chunk of any document — dropping a document entirely would leave a gap
 *      in the [n] numbering that CitationBuilder maps back to sources.
 *   4. The system message is never touched. It carries the grounding and
 *      prompt-injection rules; trimming it to save tokens would trade safety
 *      for money.
 *   5. The current question is never truncated. Answering a question you
 *      deliberately only half-read is worse than any cost.
 */
class PromptBudget
{
    public function __construct(private TokenEstimator $tokens) {}

    /**
     * Trims a built prompt to fit, returning it unchanged when it already does.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<int, array<string, mixed>>  $evidenceOrder
     * @return array{
     *     messages: array<int, array{role: string, content: string}>,
     *     evidence_order: array<int, array<string, mixed>>,
     *     estimated_tokens: int,
     *     trimmed: array<string, int>,
     * }
     */
    public function fit(array $messages, array $evidenceOrder = [], ?int $maxPromptTokens = null): array
    {
        $max = $maxPromptTokens ?? (int) config('chat.context.max_prompt_tokens', 6000);
        $trimmed = ['history_messages' => 0];

        $estimate = $this->tokens->estimateMessages($messages);

        if ($estimate <= $max || count($messages) < 3) {
            return [
                'messages' => $messages,
                'evidence_order' => $evidenceOrder,
                'estimated_tokens' => $estimate,
                'trimmed' => $trimmed,
            ];
        }

        // messages[0] is the system prompt and the last is the current turn
        // (question + evidence). Everything between is history, oldest first —
        // which is exactly the order to drop it in.
        $system = array_shift($messages);
        $current = array_pop($messages);

        while (! empty($messages) && $this->tokens->estimateMessages([$system, ...$messages, $current]) > $max) {
            array_shift($messages);
            $trimmed['history_messages']++;
        }

        $final = [$system, ...$messages, $current];

        return [
            'messages' => $final,
            // Evidence is untouched: it lives inside the current turn, and
            // trimming it would require rebuilding the [n] numbering to stay
            // consistent with evidence_order. History alone is enough to bring
            // any realistic prompt back under the ceiling, and this is a
            // pathological-case backstop rather than a working budget — the
            // per-tier evidence budgets are what shape normal turns.
            'evidence_order' => $evidenceOrder,
            'estimated_tokens' => $this->tokens->estimateMessages($final),
            'trimmed' => $trimmed,
        ];
    }
}

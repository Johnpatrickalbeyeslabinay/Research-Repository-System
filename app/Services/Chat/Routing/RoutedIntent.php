<?php

namespace App\Services\Chat\Routing;

/**
 * The router's verdict on one turn.
 *
 * $question vs $rawQuestion is the important distinction. $rawQuestion is
 * what the user literally typed and what gets persisted to
 * chat_messages.content — the widget echoes their own words back, and an
 * audit trail of a conversation has to be what was actually said. $question is
 * what the rest of the pipeline works on, with any greeting prefix removed, so
 * "hi, what research is there about tilapia?" retrieves on the question rather
 * than on the hello.
 */
final class RoutedIntent
{
    public function __construct(
        public readonly Intent $intent,
        /** Greeting-stripped text for retrieval and the prompt. */
        public readonly string $question,
        /** Exactly what the user typed; persisted verbatim. */
        public readonly string $rawQuestion,
        /** The greeting that was stripped off, when there was one. */
        public readonly ?string $strippedSmallTalk = null,
        /** Which classifier claimed the turn — recorded for eval output. */
        public readonly string $classifier = 'default',
        /** Classifier hints (is_bare_inventory, matched_term, …). */
        public readonly array $signals = [],
    ) {}

    /** A greeting prefix was removed and real content remains. */
    public function isCompound(): bool
    {
        return $this->strippedSmallTalk !== null;
    }

    public function isZeroToken(): bool
    {
        return $this->intent->isZeroToken();
    }

    /**
     * retrieval_meta for this turn's assistant message. Recording the
     * classifier alongside the intent is what makes a mis-route diagnosable
     * from the database instead of by re-running the message locally.
     */
    public function toMeta(): array
    {
        return array_filter([
            'intent' => $this->intent->value,
            'classifier' => $this->classifier,
            'skipped' => $this->intent->skipReason(),
            'compound' => $this->isCompound() ?: null,
        ], fn ($value) => $value !== null);
    }
}

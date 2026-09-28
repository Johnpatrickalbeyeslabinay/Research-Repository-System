<?php

namespace App\Services\Chat\Routing;

/**
 * Everything a classifier is allowed to see.
 *
 * Deliberately a value object and not the ChatConversation model: a classifier
 * handed the model could lazy-load its messages relation, and a "free"
 * classification pass would start issuing queries. Passing only the two facts
 * any classifier legitimately needs — whether the conversation is pinned to one
 * document, and the prior turn's context — makes the purity rule in
 * TurnClassifier enforceable rather than aspirational.
 */
final class ClassificationInput
{
    public function __construct(
        /** Greeting-stripped text: what classification decisions are made on. */
        public readonly string $question,
        /** Exactly what the user typed. */
        public readonly string $rawQuestion,
        /** The greeting removed from the front, if any. */
        public readonly ?string $strippedSmallTalk = null,
        /**
         * True for a single-research chat. Catalog and (later) external
         * lookups must never claim these turns: a pinned conversation is
         * already scoped to one document, so there is nothing to browse.
         */
        public readonly bool $isPinnedConversation = false,
        /** ChatConversation::active_context from the previous turn. */
        public readonly ?array $activeContext = null,
    ) {}
}

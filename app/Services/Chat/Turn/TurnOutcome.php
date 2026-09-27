<?php

namespace App\Services\Chat\Turn;

use App\Services\Retrieval\CatalogResult;
use App\Services\Retrieval\RetrievalResult;

/**
 * What a handler WANTS to happen — not what it did.
 *
 * Handlers persist nothing and call no LLM. They describe the turn and hand it
 * back; ChatTurnService performs every side effect once, in one place.
 *
 * That split is the point of the whole refactor. Moving each branch's logic
 * into its own class while leaving it to do its own persisting, usage-logging
 * and error handling would have relocated the duplication rather than removing
 * it — and it is the duplication, not the branching, that made the original
 * 542-line method hard to change safely.
 */
final class TurnOutcome
{
    private function __construct(
        /** Set for a zero-token turn; null when $prompt must be answered instead. */
        public readonly ?string $content,
        /** Prompt to send, or null for a zero-token turn. */
        public readonly ?array $prompt,
        public readonly array $retrievalMeta,
        public readonly array $suggestedResearch,
        public readonly array $externalSources,
        public readonly ?RetrievalResult $retrieval,
        public readonly ?CatalogResult $catalog,
        public readonly string $usageOperation,
        public readonly int $chunksRetrieved,
        public readonly ?int $maxOutputTokens,
        /** Keep this turn's rows out of the prompt history window. */
        public readonly bool $excludeFromHistory,
        /** Also exclude the user's own message ("hi" teaches a later turn nothing). */
        public readonly bool $excludeUserMessage,
    ) {}

    /**
     * A turn answered from a constant. Structurally incapable of spending a
     * token: there is no prompt for the orchestrator to send.
     */
    public static function canned(
        string $content,
        array $retrievalMeta = [],
        array $suggestedResearch = [],
        array $externalSources = [],
        bool $excludeFromHistory = true,
        bool $excludeUserMessage = false,
        ?CatalogResult $catalog = null,
        /**
         * Set when a zero-token turn still did work worth recording — the
         * empty-catalog path consulted the database and should appear in cost
         * reports as a 'catalog' operation costing nothing, rather than
         * vanishing from them entirely.
         */
        string $usageOperation = 'none',
    ): self {
        return new self(
            content: $content,
            prompt: null,
            retrievalMeta: $retrievalMeta,
            suggestedResearch: $suggestedResearch,
            externalSources: $externalSources,
            retrieval: null,
            catalog: $catalog,
            usageOperation: $usageOperation,
            chunksRetrieved: 0,
            maxOutputTokens: null,
            excludeFromHistory: $excludeFromHistory,
            excludeUserMessage: $excludeUserMessage,
        );
    }

    /** A turn the orchestrator must send to the model. */
    public static function answered(
        array $prompt,
        string $usageOperation = 'chat_answer',
        array $retrievalMeta = [],
        ?RetrievalResult $retrieval = null,
        ?CatalogResult $catalog = null,
        int $chunksRetrieved = 0,
        ?int $maxOutputTokens = null,
    ): self {
        return new self(
            content: null,
            prompt: $prompt,
            retrievalMeta: $retrievalMeta,
            suggestedResearch: [],
            externalSources: [],
            retrieval: $retrieval,
            catalog: $catalog,
            usageOperation: $usageOperation,
            chunksRetrieved: $chunksRetrieved,
            maxOutputTokens: $maxOutputTokens,
            excludeFromHistory: false,
            excludeUserMessage: false,
        );
    }

    public function needsLlm(): bool
    {
        return $this->prompt !== null;
    }
}

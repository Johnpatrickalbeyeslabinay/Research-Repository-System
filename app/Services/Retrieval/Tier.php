<?php

namespace App\Services\Retrieval;

/**
 * How much of a document this turn needs to read.
 *
 * The chat path always retrieved body chunks — 6 to 8 of them, up to 1600
 * characters each, roughly 4,000 evidence tokens — even for "what is this
 * study about", which the abstract answers completely. CatalogService already
 * proved the cheap path works at corpus level; these tiers extend the same
 * idea to a single question about a single study.
 *
 * Escalation between tiers is nearly free, which is what makes defaulting to
 * Abstract safe: RetrievalService caches the query vector for 24 hours, so a
 * second pass at FullText within the same turn costs one more Postgres round
 * trip and ZERO API tokens. Guessing the tier wrong costs latency, not money.
 */
enum Tier: string
{
    /** stud_file metadata only — CatalogService's territory. */
    case Metadata = 'metadata';

    /** The synthetic metadata chunk plus abstract-section chunks. */
    case Abstract = 'abstract';

    /** Every section. What the chat path used to do unconditionally. */
    case FullText = 'full_text';

    /**
     * Which chunk sections this tier may retrieve. An empty array means no
     * restriction at all.
     *
     * The Abstract tier includes 'metadata' deliberately: ChunkingService
     * always writes exactly one synthetic metadata chunk per document
     * (title/authors/year/keywords/SDGs/abstract), so including it guarantees
     * this tier can never come back empty where Metadata would have found
     * something. That guarantee is why Abstract is safe as the default.
     *
     * @return string[]
     */
    public function sections(): array
    {
        return match ($this) {
            self::Metadata => ['metadata'],
            self::Abstract => ['metadata', 'abstract'],
            self::FullText => [],
        };
    }

    /**
     * Evidence token budget. Config-backed so these can be tuned from the
     * environment without a deploy.
     */
    public function evidenceTokenBudget(): int
    {
        return match ($this) {
            self::Metadata => (int) config('chat.tiers.metadata.max_evidence_tokens', 800),
            self::Abstract => (int) config('chat.tiers.abstract.max_evidence_tokens', 1200),
            self::FullText => (int) config('chat.context.max_evidence_tokens', 4000),
        };
    }

    /**
     * Answer length ceiling.
     *
     * This matters more to the bill than the prompt budget does: gpt-4o-mini
     * charges $0.15 per million input tokens and $0.60 per million output, so
     * output costs FOUR TIMES as much. A 700-token answer costs the same as a
     * 2,800-token prompt. An abstract-tier answer has less to say and should
     * not be given room to ramble past it.
     */
    public function maxOutputTokens(): int
    {
        return match ($this) {
            self::Metadata => (int) config('chat.catalog.max_output_tokens', 450),
            self::Abstract => (int) config('chat.tiers.abstract.max_output_tokens', 350),
            self::FullText => (int) config('chat.models.max_output_tokens', 700),
        };
    }

    /** Max chunks that reach the prompt at this tier. */
    public function maxChunks(): int
    {
        return match ($this) {
            self::Metadata => (int) config('chat.catalog.max_documents', 10),
            self::Abstract => (int) config('chat.tiers.abstract.max_chunks', 6),
            self::FullText => (int) config('chat.retrieval.final_chunks', 8),
        };
    }

    /** Max chunks from any one document at this tier. */
    public function maxPerDocument(): int
    {
        return match ($this) {
            self::Metadata => 1,
            self::Abstract => (int) config('chat.tiers.abstract.max_per_document', 2),
            self::FullText => (int) config('chat.retrieval.max_per_document', 3),
        };
    }

    /** Max distinct documents at this tier. */
    public function maxDocuments(): int
    {
        return match ($this) {
            self::Metadata => (int) config('chat.catalog.max_documents', 10),
            self::Abstract => (int) config('chat.tiers.abstract.max_documents', 3),
            self::FullText => (int) config('chat.retrieval.max_documents', 5),
        };
    }
}

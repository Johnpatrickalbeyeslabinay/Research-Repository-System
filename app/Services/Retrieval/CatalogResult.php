<?php

namespace App\Services\Retrieval;

use App\Models\UserFile;
use Illuminate\Support\Collection;

/**
 * Value object returned by CatalogService::browse() — the document-level
 * counterpart to RetrievalResult. Where RetrievalResult carries CHUNKS of
 * text to answer "what does this study say?", this carries whole DOCUMENTS
 * to answer "what studies exist?".
 *
 * $total is deliberately the count BEFORE the display cap, so an answer can
 * honestly say "showing 10 of 34" rather than implying the repository holds
 * only what fits in one reply.
 */
final class CatalogResult
{
    /**
     * @param  Collection<int, UserFile>  $documents  in display order (most relevant
     *         or most recent first), eager-loaded with authors/keywords/sdgs
     * @param  int  $total  matching documents the user may access, before the cap
     * @param  array  $filters  QueryPlanner::plan()'s output, for explaining an empty result
     * @param  int  $unindexed  set only on an empty result: matching documents the
     *         user may access whose search index is not ready (pending, failed)
     */
    public function __construct(
        public readonly Collection $documents,
        public readonly int $total,
        public readonly array $filters,
        public readonly bool $semantic = false,
        public readonly int $unindexed = 0,
    ) {}

    public function isEmpty(): bool
    {
        return $this->documents->isEmpty();
    }

    /** True when more matched than are being shown. */
    public function isTruncated(): bool
    {
        return $this->total > $this->documents->count();
    }

    /**
     * Keyed by stud_file.id — the shape App\Services\Chat\CitationBuilder
     * expects for its $files argument, so a catalog turn can reuse it
     * unchanged.
     *
     * @return Collection<int, UserFile>
     */
    public function files(): Collection
    {
        return $this->documents->keyBy('id');
    }

    /** @return int[] */
    public function documentIds(): array
    {
        return $this->documents->pluck('id')->all();
    }
}

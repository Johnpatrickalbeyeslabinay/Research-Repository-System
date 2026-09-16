<?php

namespace App\Services\Retrieval;

use App\Models\UserFile;
use Illuminate\Support\Collection;

/**
 * Value object returned by RetrievalService::retrieve() — bundles the final
 * reranked chunks with everything downstream (App\Services\Chat\PromptBuilder,
 * CitationBuilder, ConversationContext) needs, so each of those doesn't have
 * to re-derive it independently.
 */
final class RetrievalResult
{
    /**
     * @param  array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, similarity:float, weighted_score:float}>  $chunks
     * @param  Collection<int, UserFile>  $files  keyed by stud_file.id — only the documents actually represented in $chunks, eager-loaded with authors/keywords/sdgs
     * @param  array{topic:string, year_from:?int, year_to:?int, sdg_numbers:int[], author:?string, research_type:?string, stud_file_ids:int[], is_comparison:bool, is_broad:bool}  $filters  QueryPlanner::plan()'s full output
     */
    /**
     * @param  int[]  $nearestDocumentIds  up to 3 document ids that scored
     *         highest even though nothing cleared min_similarity — used
     *         only to suggest "you might mean..." research cards alongside
     *         the no-evidence canned response, never sent to the LLM.
     */
    public function __construct(
        public readonly array $chunks,
        public readonly Collection $files,
        public readonly array $filters,
        public readonly bool $isComparison,
        public readonly bool $relaxedFilters,
        public readonly array $nearestDocumentIds = [],
    ) {}

    /**
     * True when nothing cleared config('chat.retrieval.min_similarity') —
     * the zero-token path: callers must skip the LLM call entirely and
     * return the canned "not enough evidence" response instead (see the
     * project's RAG plan, Deliverable 10 / "no-evidence path").
     */
    public function isEmpty(): bool
    {
        return empty($this->chunks);
    }

    /**
     * Distinct stud_file ids represented in $chunks, in the order they
     * first appear (i.e. best-scoring document first).
     *
     * @return int[]
     */
    public function documentIds(): array
    {
        return array_values(array_unique(array_column($this->chunks, 'stud_file_id')));
    }
}

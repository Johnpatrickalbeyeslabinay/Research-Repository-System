<?php

namespace App\Services\Retrieval;

use App\Models\User;

/**
 * Combines VectorSearchService (semantic) and LexicalSearchService (exact
 * identifiers/keywords) via Reciprocal Rank Fusion — each chunk's final
 * score is the sum of 1/(k + rank) across whichever of the two lists it
 * appeared in, so a chunk ranked highly by either arm surfaces near the
 * top without either score's raw scale (cosine similarity vs. ts_rank_cd,
 * which are not comparable numbers) having to be reconciled directly.
 *
 * This is the retrieval entry point RetrievalService calls — callers never
 * talk to VectorSearchService or LexicalSearchService directly.
 */
class HybridSearchService
{
    // Standard RRF constant (Cormack et al.) — large enough that a
    // document's exact rank within a list matters less than which list(s)
    // it appears in at all; not exposed as config since there's no
    // evidence yet (see App\Console\Commands\EvaluateChatbotCommand) that
    // tuning it would change anything.
    private const RRF_K = 60;

    public function __construct(
        private VectorSearchService $vectorSearch,
        private LexicalSearchService $lexicalSearch,
    ) {}

    /**
     * @param  float[]  $queryVector
     * @param  array{year_from: ?int, year_to: ?int, sdg_numbers: int[], author: ?string,
     *                research_type: ?string, stud_file_ids: int[]}  $filters
     * @return array{
     *     chunks: array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, similarity:float}>,
     *     relaxed_filters: bool,
     *     nearest: array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, similarity:float}>,
     * } 'similarity' on the returned chunks is the fused RRF score, not a
     *   raw cosine similarity — App\Services\Retrieval\ChunkReranker only
     *   needs a comparable ordering value, not a specific scale.
     */
    public function search(array $queryVector, string $queryText, ?User $user, array $filters, int $limit): array
    {
        $vectorResult = $this->vectorSearch->search($queryVector, $user, $filters, $limit);
        $lexicalChunks = $this->lexicalSearch->search($queryText, $user, $filters, $limit);

        // VectorSearchService relaxes its own narrow filters internally
        // when starved (a specific year/SDG/author/type returning almost
        // nothing) — LexicalSearchService has no such fallback of its own,
        // so mirror the same retry here. This matters more here than it
        // might seem: a name that appears in a document's raw text but not
        // in its structured author fields (e.g. a name QueryPlanner
        // over-eagerly read as an "author" filter) would otherwise be
        // permanently excluded from the one search arm that could actually
        // find it.
        $lexicalRelaxed = false;

        if (
            empty($lexicalChunks)
            && RetrievalFilterSql::hasNarrowFilter($filters)
        ) {
            $lexicalChunks = $this->lexicalSearch->search(
                $queryText,
                $user,
                RetrievalFilterSql::clearNarrowFilters($filters),
                $limit
            );
            $lexicalRelaxed = ! empty($lexicalChunks);
        }

        return [
            'chunks' => $this->fuse($vectorResult['chunks'], $lexicalChunks, $limit),
            'relaxed_filters' => $vectorResult['relaxed_filters'] || $lexicalRelaxed,
            'nearest' => $vectorResult['nearest'],
        ];
    }

    /**
     * @param  array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, similarity:float}>  $vectorChunks
     * @param  array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, rank:float}>  $lexicalChunks
     * @return array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, similarity:float}>
     */
    private function fuse(array $vectorChunks, array $lexicalChunks, int $limit): array
    {
        $scores = [];
        $byId = [];

        foreach ($vectorChunks as $position => $chunk) {
            $scores[$chunk['id']] = ($scores[$chunk['id']] ?? 0.0) + 1 / (self::RRF_K + $position + 1);
            $byId[$chunk['id']] ??= $chunk;
        }

        foreach ($lexicalChunks as $position => $chunk) {
            $scores[$chunk['id']] = ($scores[$chunk['id']] ?? 0.0) + 1 / (self::RRF_K + $position + 1);
            // A lexical-only hit (never reached min_similarity semantically,
            // or wasn't in the vector candidate set at all) has no cosine
            // similarity of its own — 0.0 here only ever feeds into the
            // fused score below, never compared against min_similarity
            // again (that threshold was already applied inside
            // VectorSearchService, before fusion).
            $byId[$chunk['id']] ??= [
                'id' => $chunk['id'],
                'stud_file_id' => $chunk['stud_file_id'],
                'section' => $chunk['section'],
                'page_number' => $chunk['page_number'],
                'content' => $chunk['content'],
                'similarity' => 0.0,
            ];
        }

        $fused = [];
        foreach ($byId as $id => $chunk) {
            $chunk['similarity'] = $scores[$id];
            $fused[] = $chunk;
        }

        usort($fused, fn ($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_slice($fused, 0, $limit);
    }
}

<?php

namespace App\Services\Retrieval;

use App\Models\User;
use App\Support\ResearchAccess;
use Illuminate\Support\Facades\DB;

/**
 * The ONLY place raw pgvector `<=>` SQL lives. Applies
 * App\Support\ResearchAccess's predicate directly inside the query that
 * fetches chunks, so a chunk from research the asking user isn't allowed to
 * read never leaves Postgres in the first place — it is not filtered out
 * afterward, and it is not left to the caller to remember.
 */
class VectorSearchService
{
    /**
     * @param  float[]  $queryVector  1536-dim, from the same embedding
     *                                model used to index the corpus (see
     *                                App\Services\Research\EmbeddingService)
     * @param  array{year_from: ?int, year_to: ?int, sdg_numbers: int[], author: ?string,
     *                research_type: ?string, stud_file_ids: int[]}  $filters  QueryPlanner::plan()'s output
     * @param  int  $limit  final row count after any relaxation — the
     *                      caller's ChunkReranker narrows further
     * @param  string[]  $sections  restrict to these chunk sections; empty
     *         means no restriction. Comes from
     *         App\Services\Retrieval\Tier::sections().
     *
     *         ['metadata'] turns this into a DOCUMENT-level search:
     *         ChunkingService writes exactly one synthetic metadata chunk
     *         per document (chunk_index 0 — title/authors/year/type/
     *         keywords/SDGs/abstract, see ProcessResearchDocument::
     *         documentSummary()), so restricting to it returns at most one
     *         row per study instead of many rows from the same one. That is
     *         precisely what App\Services\Retrieval\CatalogService needs for
     *         "recommend papers about X", and it needs no separate index.
     *
     *         ['metadata', 'abstract'] is the Abstract retrieval tier. It was
     *         a single ?string until tiering needed two sections in one query;
     *         widening it to a list is what let an abstract-level answer read
     *         the abstract AND the document's metadata in one pass.
     * @param  ?float  $minSimilarity  overrides
     *         config('chat.retrieval.min_similarity') for this call — a
     *         broad discovery question is legitimately looser than a
     *         precise factual one. The one real caller that overrides it is
     *         CatalogService, via config('chat.catalog.min_similarity').
     *         (This used to cite a 'min_similarity_by_intent' config key,
     *         which never existed: config/chat.php documents the OPPOSITE
     *         decision — deliberately one threshold, not one per intent,
     *         because a looser bar for broad questions measured worse.)
     * @return array{
     *     chunks: array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, similarity:float}>,
     *     relaxed_filters: bool,
     *     nearest: array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, similarity:float}>,
     * } 'nearest' is the top 3 rows by raw similarity regardless of
     *   min_similarity — used only for the "couldn't find enough evidence"
     *   UI's nearest-topic research cards (see ChatController::message()),
     *   never sent to the LLM as evidence.
     */
    public function search(
        array $queryVector,
        ?User $user,
        array $filters,
        int $limit,
        array $sections = [],
        ?float $minSimilarity = null
    ): array {
        $minSimilarity ??= (float) config('chat.retrieval.min_similarity', 0.25);

        $rows = $this->runQuery($queryVector, $user, $filters, $this->overfetchLimit($filters, $limit), $sections);
        $survivors = $this->aboveThreshold($rows, $minSimilarity);
        $nearest = array_slice($rows, 0, 3);

        // A narrow filter (a specific year, a specific SDG) can starve
        // HNSW's post-filtering down to almost nothing even when the
        // corpus has good matches for the underlying *topic* — retry once,
        // entirely unfiltered (scope from a pinned/follow-up
        // stud_file_ids is deliberately kept — see
        // RetrievalFilterSql::clearNarrowFilters()), rather than return a
        // handful of weak results or none at all.
        if (RetrievalFilterSql::hasNarrowFilter($filters) && count($survivors) < (int) config('chat.retrieval.filtered_min_results', 3)) {
            $unfiltered = $this->runQuery($queryVector, $user, RetrievalFilterSql::clearNarrowFilters($filters), $limit, $sections);

            return [
                'chunks' => array_slice($this->aboveThreshold($unfiltered, $minSimilarity), 0, $limit),
                'relaxed_filters' => true,
                'nearest' => array_slice($unfiltered, 0, 3),
            ];
        }

        return [
            'chunks' => array_slice($survivors, 0, $limit),
            'relaxed_filters' => false,
            'nearest' => $nearest,
        ];
    }

    /**
     * @return array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, similarity:float}>
     */
    private function runQuery(array $queryVector, ?User $user, array $filters, int $limit, array $sections = []): array
    {
        // Bound once via a CTE and referenced twice (SELECT + ORDER BY)
        // below, rather than binding the same vector literal at two
        // separate `?` positions — one value, one binding, no risk of the
        // two drifting apart.
        $vectorLiteral = '['.implode(',', $queryVector).']';
        $bindings = [$vectorLiteral];

        $sql = "WITH q AS (SELECT ?::vector AS v)
            SELECT rc.id, rc.stud_file_id, rc.section, rc.page_number, rc.content,
                   1 - (rc.embedding <=> q.v) AS similarity
            FROM research_chunks rc
            JOIN stud_file sf ON sf.id = rc.stud_file_id
            CROSS JOIN q
            WHERE sf.search_index_status = 'ready'
              AND rc.embedding IS NOT NULL";

        // Appended (and bound) HERE rather than further down, because
        // bindings must be pushed in the same order their `?` appear in the
        // SQL text — this clause precedes the access predicate and the
        // filter fragment below, so its binding must too.
        [$sectionSql, $sectionBindings] = RetrievalFilterSql::sectionClause($sections);
        $sql .= $sectionSql;
        array_push($bindings, ...$sectionBindings);

        // ResearchAccess::sqlPredicate() and RetrievalFilterSql::build() both
        // use positional `?` placeholders (never `:named` ones) — PDO does
        // not allow mixing the two styles in one prepared statement, and
        // this query already uses `?` for the vector literal above.
        [$accessSql, $accessBindings] = ResearchAccess::sqlPredicate($user, 'sf');
        $sql .= " AND {$accessSql}";
        array_push($bindings, ...$accessBindings);

        [$filterSql, $filterBindings] = RetrievalFilterSql::build($filters);
        $sql .= $filterSql;
        array_push($bindings, ...$filterBindings);

        $sql .= ' ORDER BY rc.embedding <=> q.v LIMIT ?';
        $bindings[] = $limit;

        return DB::transaction(function () use ($sql, $bindings) {
            // hnsw.ef_search MUST only ever be set with SET LOCAL inside a
            // transaction, never a bare SET — the app connects through
            // PgBouncer in transaction pooling mode (DB_PORT=6543), where a
            // bare SET on a pooled connection is unsafe (it can leak to, or
            // be silently lost between, unrelated requests sharing the same
            // pooled backend).
            $ef = (int) config('chat.retrieval.hnsw_ef_search', 60);
            DB::statement("SET LOCAL hnsw.ef_search = {$ef}");

            $rows = DB::select($sql, $bindings);

            return array_map(fn ($row) => [
                'id' => (int) $row->id,
                'stud_file_id' => (int) $row->stud_file_id,
                'section' => $row->section,
                'page_number' => $row->page_number !== null ? (int) $row->page_number : null,
                'content' => $row->content,
                'similarity' => (float) $row->similarity,
            ], $rows);
        });
    }

    /**
     * @param  array<int, array{similarity: float}>  $rows
     * @return array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, similarity:float}>
     */
    private function aboveThreshold(array $rows, float $minSimilarity): array
    {
        // Applied here, in PHP, after the query — NOT as a SQL WHERE on
        // the distance expression, which would defeat the HNSW index by
        // forcing a filter on a computed value the index can't use.
        return array_values(array_filter($rows, fn ($r) => $r['similarity'] >= $minSimilarity));
    }

    private function overfetchLimit(array $filters, int $limit): int
    {
        if (! RetrievalFilterSql::hasNarrowFilter($filters)) {
            return $limit;
        }

        return $limit * (int) config('chat.retrieval.filtered_overfetch_multiplier', 3);
    }
}

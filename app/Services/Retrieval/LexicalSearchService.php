<?php

namespace App\Services\Retrieval;

use App\Models\User;
use App\Support\ResearchAccess;
use Illuminate\Support\Facades\DB;

/**
 * The lexical arm of hybrid search — full-text search over
 * research_chunks.content_tsv (Postgres websearch_to_tsquery). Vector
 * search alone handles topical questions well but exact identifiers poorly
 * — a study's own acronym, an author's exact name spelling, a title
 * fragment. Fused with VectorSearchService's results via Reciprocal Rank
 * Fusion — see HybridSearchService.
 *
 * Applies the exact same App\Support\ResearchAccess predicate and
 * RetrievalFilterSql filters as VectorSearchService — this is a second
 * *ranking* signal over the same authorized rows, never a second
 * authorization path.
 */
class LexicalSearchService
{
    /**
     * @param  array{year_from: ?int, year_to: ?int, sdg_numbers: int[], author: ?string,
     *                research_type: ?string, stud_file_ids: int[]}  $filters
     * @return array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, rank:float}>
     */
    public function search(string $query, ?User $user, array $filters, int $limit): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        // websearch_to_tsquery is computed once via a CTE and referenced
        // twice (WHERE + rank in SELECT), same pattern
        // VectorSearchService uses for its query vector — one bound value,
        // no risk of the two drifting apart.
        $bindings = [$query];
        $sql = "WITH q AS (SELECT websearch_to_tsquery('english', ?) AS tsq)
            SELECT rc.id, rc.stud_file_id, rc.section, rc.page_number, rc.content,
                   ts_rank_cd(rc.content_tsv, q.tsq) AS rank
            FROM research_chunks rc
            JOIN stud_file sf ON sf.id = rc.stud_file_id
            CROSS JOIN q
            WHERE sf.search_index_status = 'ready'
              AND rc.content_tsv @@ q.tsq";

        [$accessSql, $accessBindings] = ResearchAccess::sqlPredicate($user, 'sf');
        $sql .= " AND {$accessSql}";
        array_push($bindings, ...$accessBindings);

        [$filterSql, $filterBindings] = RetrievalFilterSql::build($filters);
        $sql .= $filterSql;
        array_push($bindings, ...$filterBindings);

        $sql .= ' ORDER BY rank DESC LIMIT ?';
        $bindings[] = $limit;

        $rows = DB::select($sql, $bindings);

        return array_map(fn ($row) => [
            'id' => (int) $row->id,
            'stud_file_id' => (int) $row->stud_file_id,
            'section' => $row->section,
            'page_number' => $row->page_number !== null ? (int) $row->page_number : null,
            'content' => $row->content,
            'rank' => (float) $row->rank,
        ], $rows);
    }
}

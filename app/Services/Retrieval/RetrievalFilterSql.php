<?php

namespace App\Services\Retrieval;

/**
 * QueryPlanner's structured filters (year/SDG/author/type/scope), as a
 * reusable SQL fragment — shared by VectorSearchService and
 * LexicalSearchService so the two arms of hybrid search can never drift
 * apart on what a filter means. Positional `?` placeholders throughout
 * (never `:named` ones), matching both callers' queries.
 */
final class RetrievalFilterSql
{
    /**
     * @param  array{year_from: ?int, year_to: ?int, sdg_numbers: int[], author: ?string,
     *                research_type: ?string, stud_file_ids: int[]}  $filters
     * @param  string  $studFileAlias  the calling query's stud_file table alias
     * @param  string  $chunkAlias  the calling query's research_chunks table alias
     * @return array{0: string, 1: array<int, mixed>}
     */
    public static function build(array $filters, string $studFileAlias = 'sf', string $chunkAlias = 'rc'): array
    {
        $sql = '';
        $bindings = [];

        if (! empty($filters['year_from'])) {
            $sql .= " AND {$studFileAlias}.publication_year >= ?";
            $bindings[] = $filters['year_from'];
        }

        if (! empty($filters['year_to'])) {
            $sql .= " AND {$studFileAlias}.publication_year <= ?";
            $bindings[] = $filters['year_to'];
        }

        if (! empty($filters['research_type'])) {
            $sql .= " AND {$studFileAlias}.research_type = ?";
            $bindings[] = $filters['research_type'];
        }

        // Single-research / follow-up scope — always applied when present,
        // never relaxed by a caller's "narrow filter" retry (see
        // VectorSearchService::clearNarrowFilters()).
        if (! empty($filters['stud_file_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['stud_file_ids']), '?'));
            $sql .= " AND {$chunkAlias}.stud_file_id IN ({$placeholders})";
            array_push($bindings, ...array_values($filters['stud_file_ids']));
        }

        if (! empty($filters['sdg_numbers'])) {
            $placeholders = implode(',', array_fill(0, count($filters['sdg_numbers']), '?'));
            $sql .= " AND EXISTS (
                SELECT 1 FROM stud_file_sdg fsdg
                JOIN sdgs s ON s.id = fsdg.sdg_id
                WHERE fsdg.stud_file_id = {$studFileAlias}.id AND s.number IN ({$placeholders})
            )";
            array_push($bindings, ...array_values($filters['sdg_numbers']));
        }

        if (! empty($filters['author'])) {
            $needle = '%'.$filters['author'].'%';
            $sql .= " AND ({$studFileAlias}.name ILIKE ? OR EXISTS (
                SELECT 1 FROM stud_file_author sfa
                WHERE sfa.stud_file_id = {$studFileAlias}.id AND sfa.author_name ILIKE ?
            ))";
            $bindings[] = $needle;
            $bindings[] = $needle;
        }

        return [$sql, $bindings];
    }

    /**
     * year/SDG/author/type — narrow enough to starve a search down to
     * (near) nothing even when the corpus has good matches for the
     * underlying topic. Deliberately excludes stud_file_ids: a pinned
     * single-research chat or a conversational follow-up scope is a
     * deliberate choice, not a starvable filter, and must never be
     * silently widened to the whole corpus by a relax-on-starvation retry.
     */
    public static function hasNarrowFilter(array $filters): bool
    {
        return ! empty($filters['year_from']) || ! empty($filters['year_to'])
            || ! empty($filters['sdg_numbers']) || ! empty($filters['author'])
            || ! empty($filters['research_type']);
    }

    public static function clearNarrowFilters(array $filters): array
    {
        return [
            'year_from' => null,
            'year_to' => null,
            'sdg_numbers' => [],
            'author' => null,
            'research_type' => null,
            'stud_file_ids' => $filters['stud_file_ids'] ?? [],
        ];
    }
}

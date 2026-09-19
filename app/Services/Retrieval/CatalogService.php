<?php

namespace App\Services\Retrieval;

use App\Models\User;
use App\Models\UserFile;
use App\Support\ResearchAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Document-level retrieval for CATALOG questions — "what research is
 * available?", "recommend papers about fish", "how many studies are there?"
 *
 * Why this is a separate path from RetrievalService: that one searches
 * research_chunks, which answers "what does this study SAY". A catalog
 * question asks "what studies EXIST", and the answer to that lives in
 * stud_file's metadata, not in any chunk's prose. Running a corpus-level
 * question through chunk search returns nothing above min_similarity (no
 * chunk contains a list of the repository's contents) and falls into the
 * no-evidence path — the exact bug this class fixes.
 *
 * Two complementary arms, both permission-scoped through the same
 * App\Support\ResearchAccess boundary every other surface uses:
 *
 *   A. Structured — an Eloquent query over stud_file with QueryPlanner's
 *      year/SDG/author/type filters. Answers "list everything", "studies
 *      from 2024 to 2026", "which support SDG 4", and gives the true total.
 *
 *   B. Semantic — VectorSearchService restricted to section='metadata'.
 *      ChunkingService writes exactly ONE metadata chunk per document
 *      (title/authors/year/keywords/SDGs/abstract), so a vector search over
 *      just those rows is a document-level recommender that reuses the
 *      existing HNSW index with no new table, column, or embedding. This is
 *      what makes "recommend papers related to fish" work.
 *
 * Arm B orders, arm A completes: a topical question ranks by semantic
 * relevance and then back-fills with recent documents so a thin corpus
 * still returns something useful.
 */
class CatalogService
{
    public function __construct(private VectorSearchService $vectorSearch) {}

    /**
     * @param  array  $plan  QueryPlanner::plan() output
     * @param  ?float[]  $queryVector  null for a bare inventory question
     *         ("list everything"), where embedding the question produces a
     *         meaningless vector and arm B is skipped entirely
     */
    public function browse(?User $user, array $plan, ?array $queryVector = null): CatalogResult
    {
        $limit = (int) config('chat.catalog.max_documents', 10);

        $total = $this->accessibleQuery($user, $plan)->count();

        if ($total === 0) {
            return new CatalogResult(collect(), 0, $plan);
        }

        $semanticIds = $queryVector === null
            ? []
            : $this->semanticallyRankedIds($queryVector, $user, $plan, $limit);

        $documents = $this->hydrate($semanticIds, $user, $plan, $limit);

        return new CatalogResult($documents, $total, $plan, semantic: ! empty($semanticIds));
    }

    /**
     * The base query: everything this user may read that is actually
     * searchable, with the planner's structured filters applied.
     *
     * ResearchAccess::scope() is the security boundary — reused, never
     * re-derived. search_index_status='ready' matches what
     * VectorSearchService and LexicalSearchService require, so the catalog
     * can never advertise a document the other two arms can't answer from.
     */
    private function accessibleQuery(?User $user, array $plan): Builder
    {
        $query = ResearchAccess::scope(UserFile::query(), $user)
            ->where('stud_file.search_index_status', 'ready');

        if (! empty($plan['year_from'])) {
            $query->where('publication_year', '>=', $plan['year_from']);
        }

        if (! empty($plan['year_to'])) {
            $query->where('publication_year', '<=', $plan['year_to']);
        }

        if (! empty($plan['research_type'])) {
            $query->where('research_type', $plan['research_type']);
        }

        if (! empty($plan['stud_file_ids'])) {
            $query->whereIn('stud_file.id', $plan['stud_file_ids']);
        }

        if (! empty($plan['sdg_numbers'])) {
            $query->whereHas('sdgs', fn ($q) => $q->whereIn('number', $plan['sdg_numbers']));
        }

        if (! empty($plan['author'])) {
            $needle = '%'.$plan['author'].'%';
            // Postgres needs ILIKE for case-insensitivity; sqlite (the test
            // database) has no ILIKE and its LIKE is already
            // case-insensitive for ASCII. RetrievalFilterSql hardcodes
            // ILIKE because it only ever builds raw Postgres SQL — this
            // arm is Eloquent and does run on both.
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

            $query->where(function ($q) use ($needle, $like) {
                $q->where('stud_file.name', $like, $needle)
                    ->orWhereHas('authors', fn ($a) => $a->where('author_name', $like, $needle));
            });
        }

        return $query;
    }

    /**
     * Arm B — document ids in semantic relevance order.
     *
     * Uses a deliberately looser threshold than research Q&A: a
     * recommendation is offered as "you might find these relevant", not
     * asserted as fact, so a weaker match is still a useful suggestion
     * where it would be a bad citation.
     *
     * @param  float[]  $queryVector
     * @return int[]
     */
    private function semanticallyRankedIds(array $queryVector, ?User $user, array $plan, int $limit): array
    {
        $result = $this->vectorSearch->search(
            queryVector: $queryVector,
            user: $user,
            filters: $plan,
            // One metadata chunk per document means rows map 1:1 to
            // documents — no need to over-fetch for de-duplication.
            limit: $limit,
            section: 'metadata',
            minSimilarity: (float) config('chat.catalog.min_similarity', 0.12),
        );

        return array_values(array_unique(array_column($result['chunks'], 'stud_file_id')));
    }

    /**
     * Loads the documents, ordered semantically where arm B had an opinion
     * and by recency for the rest, then collapses duplicate PDFs.
     *
     * @param  int[]  $semanticIds
     * @return Collection<int, UserFile>
     */
    private function hydrate(array $semanticIds, ?User $user, array $plan, int $limit): Collection
    {
        // Over-fetch the recency fill so that de-duplication below can't
        // leave the list short of the cap.
        $recent = $this->accessibleQuery($user, $plan)
            ->orderByDesc('publication_year')
            ->orderByDesc('stud_file.id')
            ->limit($limit * 2)
            ->pluck('stud_file.id')
            ->all();

        $orderedIds = array_values(array_unique(array_merge($semanticIds, $recent)));

        if (empty($orderedIds)) {
            return collect();
        }

        $files = UserFile::with(['authors', 'keywords', 'sdgs'])
            ->whereIn('id', $orderedIds)
            ->get()
            ->keyBy('id');

        return $this->dedupeByPdf($orderedIds, $files, $limit);
    }

    /**
     * Keeps the first document per distinct PDF, preserving $orderedIds'
     * ordering. The corpus can hold the identical file submitted more than
     * once (duplicate test submissions produce several stud_file rows
     * sharing a pdf_hash); listing that content three times is noise, not
     * three recommendations. A row with no pdf_hash — a legacy record
     * predating the column — is never deduped against anything, since a
     * missing hash carries no dedupe information.
     *
     * Mirrors ChatController::suggestedResearchCards()'s rule deliberately:
     * the two operate on different shapes (cards vs. models), but a user
     * must never see the same study collapsed in one list and duplicated in
     * the other.
     *
     * @param  int[]  $orderedIds
     * @param  Collection<int, UserFile>  $files
     * @return Collection<int, UserFile>
     */
    private function dedupeByPdf(array $orderedIds, Collection $files, int $limit): Collection
    {
        $seenHashes = [];
        $kept = collect();

        foreach ($orderedIds as $id) {
            if ($kept->count() >= $limit) {
                break;
            }

            $file = $files->get($id);

            if ($file === null) {
                continue;
            }

            $hashKey = $file->pdf_hash ?? "id:{$file->id}";

            if (isset($seenHashes[$hashKey])) {
                continue;
            }

            $seenHashes[$hashKey] = true;
            $kept->push($file);
        }

        return $kept;
    }

    /**
     * The zero-token answer for an empty catalog — deliberately names the
     * filters that were applied, so "nothing found" is explainable rather
     * than a dead end the user can't act on.
     */
    public function emptyMessage(array $plan): string
    {
        $described = $this->describeFilters($plan);

        if ($described === null) {
            return "The repository doesn't have any research you can access yet. "
                .'Once submissions are approved and published, I can help you explore them.';
        }

        return "I couldn't find any research you can access matching {$described}. "
            .'Try widening the year range, removing a filter, or asking about a broader topic.';
    }

    /** Human-readable summary of the active filters, or null if there are none. */
    private function describeFilters(array $plan): ?string
    {
        $bits = [];

        if (! empty($plan['year_from']) && ! empty($plan['year_to'])) {
            $bits[] = $plan['year_from'] === $plan['year_to']
                ? "year {$plan['year_from']}"
                : "years {$plan['year_from']}-{$plan['year_to']}";
        } elseif (! empty($plan['year_from'])) {
            $bits[] = "{$plan['year_from']} onwards";
        } elseif (! empty($plan['year_to'])) {
            $bits[] = "up to {$plan['year_to']}";
        }

        if (! empty($plan['sdg_numbers'])) {
            $bits[] = 'SDG '.implode(', ', $plan['sdg_numbers']);
        }

        if (! empty($plan['research_type'])) {
            $bits[] = "type \"{$plan['research_type']}\"";
        }

        if (! empty($plan['author'])) {
            $bits[] = "author \"{$plan['author']}\"";
        }

        return empty($bits) ? null : implode(' · ', $bits);
    }
}

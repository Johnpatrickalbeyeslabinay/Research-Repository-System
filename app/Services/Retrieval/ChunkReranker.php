<?php

namespace App\Services\Retrieval;

use App\Support\TokenEstimator;

/**
 * Narrows pgvector's candidate chunks (config('chat.retrieval.candidates'),
 * default 24) down to the small, diverse, budget-bounded evidence set
 * actually sent to the LLM (config('chat.retrieval.final_chunks'), default
 * 8). Deliberately NOT a cross-encoder/LLM rerank — that would be a new
 * vendor, new latency, and new cost for a corpus this size. Everything
 * here is cheap, deterministic post-processing:
 *
 *   1. Section weighting   — favor abstract/results/conclusion/metadata
 *   2. Per-document cap    — stop one verbose document monopolizing the
 *                            answer (also what makes comparison answers
 *                            possible: no single-document cap means no
 *                            guarantee a second study's evidence survives)
 *   3. MMR-style diversity — drop a chunk that's a near-duplicate of one
 *                            already selected (adjacent overlapping chunks
 *                            commonly are, given ChunkingService's word
 *                            overlap between consecutive chunks)
 *   4. Token budget         — stop once final_chunks binds, or skip a chunk
 *                            that would not fit in max_evidence_tokens.
 *                            Budgeted against the TRUNCATED text that will
 *                            actually reach the prompt (see select()), and
 *                            measured by App\Support\TokenEstimator rather
 *                            than a local word count.
 */
class ChunkReranker
{
    public function __construct(private TokenEstimator $tokens) {}

    /**
     * @param  array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, similarity:float}>  $chunks
     * @param  bool  $isComparison  when true, each distinct document's best
     *                              chunk is pulled to the front before the
     *                              cap/diversity pass runs — guaranteeing
     *                              evidence from more than one study is
     *                              available to compare, rather than
     *                              risking the whole budget filling from
     *                              whichever single document scored highest.
     * @param  bool  $isBroad  same document-diversifying treatment, for a
     *                         different reason: a discovery question like
     *                         "what research is available?" (see
     *                         QueryPlanner::plan()'s is_broad) is better
     *                         served by a representative spread across
     *                         documents than by whichever single document's
     *                         chunks happened to score highest.
     * @return array<int, array{id:int, stud_file_id:int, section:?string, page_number:?int, content:string, similarity:float, weighted_score:float}>
     */
    public function rerank(
        array $chunks,
        bool $isComparison = false,
        bool $isBroad = false,
        ?Tier $tier = null,
    ): array {
        $weighted = $this->applySectionWeights($chunks);

        usort($weighted, fn ($a, $b) => $b['weighted_score'] <=> $a['weighted_score']);

        $ordered = ($isComparison || $isBroad)
            ? $this->pullOneRepresentativePerDocumentToFront($weighted)
            : $weighted;

        return $this->select($ordered, $this->chunkBudget($isComparison, $isBroad, $tier), $tier);
    }

    /**
     * How many chunks this question actually needs.
     *
     * A comparison or broad-discovery question spends its budget across
     * several documents, so it keeps the full allowance. An ordinary
     * factual question is answered from one or two passages of a single
     * study, where the 7th and 8th chunks are near-duplicates that cost
     * tokens without adding evidence — config('chat.retrieval.final_chunks')
     * stays the ceiling, and 'final_chunks_focused' is the cheaper default.
     */
    private function chunkBudget(bool $isComparison, bool $isBroad, ?Tier $tier = null): int
    {
        // A tier's own ceiling wins. The Abstract tier reads at most 2 chunks
        // from each of 3 documents, so borrowing the full-text allowance of 8
        // would let it quietly spend a full-text budget on abstracts and
        // deliver none of the saving it exists for.
        if ($tier !== null && $tier !== Tier::FullText) {
            return $tier->maxChunks();
        }

        $max = (int) config('chat.retrieval.final_chunks', 8);

        if ($isComparison || $isBroad) {
            return $max;
        }

        return min($max, (int) config('chat.retrieval.final_chunks_focused', 6));
    }

    private function applySectionWeights(array $chunks): array
    {
        $weights = config('chat.retrieval.section_weights', []);

        return array_map(function (array $chunk) use ($weights) {
            $weight = $weights[$chunk['section']] ?? 1.0;
            $chunk['weighted_score'] = $chunk['similarity'] * $weight;

            return $chunk;
        }, $chunks);
    }

    /**
     * Re-orders an already score-sorted list so each distinct document's
     * single best chunk comes first (still relative to each other by
     * score), before every other chunk. select() below still enforces the
     * per-document cap and MMR on top of this — this step only guarantees
     * document *diversity* survives that far, not which chunk wins overall.
     */
    private function pullOneRepresentativePerDocumentToFront(array $sorted): array
    {
        $seenDocs = [];
        $representatives = [];
        $rest = [];

        foreach ($sorted as $chunk) {
            if (! isset($seenDocs[$chunk['stud_file_id']])) {
                $seenDocs[$chunk['stud_file_id']] = true;
                $representatives[] = $chunk;
            } else {
                $rest[] = $chunk;
            }
        }

        return array_merge($representatives, $rest);
    }

    private function select(array $ordered, int $maxChunks, ?Tier $tier = null): array
    {
        // Per-tier caps where a tier was given, falling back to the
        // full-text config otherwise so existing callers behave identically.
        $maxPerDoc = $tier?->maxPerDocument() ?? (int) config('chat.retrieval.max_per_document', 3);
        $maxDocs = $tier?->maxDocuments() ?? (int) config('chat.retrieval.max_documents', 5);
        $maxTokens = $tier?->evidenceTokenBudget() ?? (int) config('chat.context.max_evidence_tokens', 4000);
        $overlapThreshold = (float) config('chat.retrieval.mmr_overlap_threshold', 0.80);

        $selected = [];
        $perDocCount = [];
        $usedTokens = 0;
        $selectedWordSets = [];

        foreach ($ordered as $chunk) {
            if (count($selected) >= $maxChunks) {
                break;
            }

            $docId = $chunk['stud_file_id'];
            $docCount = $perDocCount[$docId] ?? 0;

            if ($docCount >= $maxPerDoc) {
                continue;
            }

            if ($docCount === 0 && count($perDocCount) >= $maxDocs) {
                continue; // would introduce a document beyond the cap
            }

            if ($this->tooSimilarToAlreadySelected($chunk['content'], $selectedWordSets, $overlapThreshold)) {
                continue;
            }

            // Budget the text that will ACTUALLY be sent, not the raw chunk.
            // PromptBuilder::renderEvidence() cuts every chunk to
            // config('chat.context.max_chunk_chars') on the way into the
            // prompt, so budgeting the full content spent the allowance on
            // characters that never left the app — and could skip a chunk
            // over tokens nobody was going to pay for.
            $budgetedContent = mb_substr(
                $chunk['content'],
                0,
                (int) config('chat.context.max_chunk_chars', 1600)
            );

            $tokenCount = $this->tokens->estimate($budgetedContent);

            if ($usedTokens + $tokenCount > $maxTokens) {
                // continue, not break: one oversized chunk must not discard
                // every smaller chunk ranked behind it. With a 4000-token
                // budget that rarely mattered, but the abstract retrieval
                // tier runs a far tighter budget where a single long chunk
                // arriving early would otherwise empty the evidence set.
                continue;
            }

            $selected[] = $chunk;
            $perDocCount[$docId] = $docCount + 1;
            $usedTokens += $tokenCount;
            $selectedWordSets[] = $this->wordSet($chunk['content']);
        }

        return $selected;
    }

    /** @param  array<int, array<string, true>>  $selectedWordSets */
    private function tooSimilarToAlreadySelected(string $content, array $selectedWordSets, float $threshold): bool
    {
        $candidate = $this->wordSet($content);

        if (empty($candidate)) {
            return false;
        }

        foreach ($selectedWordSets as $existing) {
            if ($this->jaccard($candidate, $existing) >= $threshold) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, true> */
    private function wordSet(string $text): array
    {
        $words = preg_split('/\s+/', mb_strtolower(trim($text)), -1, PREG_SPLIT_NO_EMPTY);

        return array_fill_keys($words, true);
    }

    /** @param  array<string, true>  $a
     *  @param  array<string, true>  $b */
    private function jaccard(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        $intersection = count(array_intersect_key($a, $b));
        $union = count($a) + count($b) - $intersection;

        return $union === 0 ? 0.0 : $intersection / $union;
    }
}

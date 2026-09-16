<?php

namespace App\Services\Retrieval;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Deterministic, zero-token extraction of structured filters from a
 * chatbot question — year/range, SDG, author, research type, comparison
 * intent, and conversational follow-up scope ("which one used CNN?").
 *
 * Deliberately no AI call here. A regex/lookup pass handles the
 * overwhelming majority of this chatbot's example questions ("what AI
 * research was published from 2024 to 2026?", "which studies support SDG
 * 4?") for zero tokens; an LLM fallback for whatever this misses is
 * intentionally deferred until evaluation shows it's actually needed — see
 * the project's RAG plan, Deliverable 8.
 */
class QueryPlanner
{
    private const COMPARISON_PATTERN = '/\b(compare|comparison|versus|vs\.?|difference between|which one|both studies)\b/i';

    // Only consulted when NO other filter matched at all — see plan().
    // Deliberately includes generic pronouns ("it", "them"); that's safe
    // specifically because it only fires alongside that precondition.
    private const BACK_REFERENCE_PATTERN = '/\b(which one|that study|these studies|those studies|this one|the study|it|them)\b/i';

    private const AUTHOR_PATTERN = '/\b(?:by|authored by|written by)\s+([A-Z][\w.\'-]+(?:\s+[A-Z][\w.\'-]+){0,3})/u';

    private const SDG_NUMBER_PATTERN = '/\bSDGs?\s*#?\s*(\d{1,2})\b/i';

    /**
     * @param  ?array{topic?: string, filters?: array, research_ids?: int[]}  $activeContext
     *         ChatConversation::active_context from the previous turn — see
     *         App\Services\Chat\ConversationContext.
     * @param  ?int  $pinnedStudFileId  set when this is a single-research
     *         chat (research/show's "Ask about this research"); overrides
     *         everything else for scope.
     * @return array{
     *     topic: string,
     *     year_from: ?int,
     *     year_to: ?int,
     *     sdg_numbers: int[],
     *     author: ?string,
     *     research_type: ?string,
     *     stud_file_ids: int[],
     *     is_comparison: bool,
     *     is_broad: bool,
     * }
     */
    public function plan(string $question, ?array $activeContext = null, ?int $pinnedStudFileId = null): array
    {
        $topic = $question;

        [$yearFrom, $yearTo, $topic] = $this->extractYearRange($topic);
        [$sdgNumbers, $topic] = $this->extractSdgNumbers($topic);
        [$author, $topic] = $this->extractAuthor($topic);
        [$researchType, $topic] = $this->extractResearchType($topic);

        $hasAnyFilter = $yearFrom !== null || $yearTo !== null || ! empty($sdgNumbers)
            || $author !== null || $researchType !== null;

        $isComparison = (bool) preg_match(self::COMPARISON_PATTERN, $question);

        $studFileIds = $this->resolveScope($pinnedStudFileId, $hasAnyFilter, $activeContext, $question);

        $topic = trim(preg_replace('/\s+/', ' ', $topic));
        $topic = $topic === '' ? trim($question) : $topic;

        $isBroad = $pinnedStudFileId === null && (
            (! $hasAnyFilter && str_word_count(trim($question)) <= 6)
            || (bool) preg_match('/^(what|which)\s+(research|studies|papers)\b/i', trim($question))
        );

        return [
            'topic' => $topic,
            'year_from' => $yearFrom,
            'year_to' => $yearTo,
            'sdg_numbers' => $sdgNumbers,
            'author' => $author,
            'research_type' => $researchType,
            'stud_file_ids' => $studFileIds,
            'is_comparison' => $isComparison,
            'is_broad' => $isBroad,
        ];
    }

    /**
     * Single-research chat always wins. Otherwise, only reuse the
     * previous turn's research ids as a retrieval scope when THIS question
     * carries no filter of its own and contains a back-reference — e.g.
     * "show me studies about rice disease" (no scope) then "which one used
     * CNN?" (no filters of its own, but "which one" + prior research_ids
     * means: search within those, not the whole corpus).
     *
     * @return int[]
     */
    private function resolveScope(?int $pinnedStudFileId, bool $hasAnyFilter, ?array $activeContext, string $question): array
    {
        if ($pinnedStudFileId !== null) {
            return [$pinnedStudFileId];
        }

        $priorIds = $activeContext['research_ids'] ?? [];

        if ($hasAnyFilter || empty($priorIds) || ! preg_match(self::BACK_REFERENCE_PATTERN, $question)) {
            return [];
        }

        return array_values(array_map('intval', $priorIds));
    }

    /**
     * @return array{0: ?int, 1: ?int, 2: string} [year_from, year_to, $topic with the matched phrase removed]
     */
    private function extractYearRange(string $topic): array
    {
        if (preg_match('/\bfrom\s+((?:19|20)\d{2})\s+to\s+((?:19|20)\d{2})\b/i', $topic, $m)) {
            return [(int) $m[1], (int) $m[2], str_replace($m[0], '', $topic)];
        }

        if (preg_match('/\bbetween\s+((?:19|20)\d{2})\s+and\s+((?:19|20)\d{2})\b/i', $topic, $m)) {
            return [(int) $m[1], (int) $m[2], str_replace($m[0], '', $topic)];
        }

        if (preg_match('/\b((?:19|20)\d{2})\s*[-–]\s*((?:19|20)\d{2})\b/', $topic, $m)) {
            return [(int) $m[1], (int) $m[2], str_replace($m[0], '', $topic)];
        }

        if (preg_match('/\bsince\s+((?:19|20)\d{2})\b/i', $topic, $m)) {
            return [(int) $m[1], null, str_replace($m[0], '', $topic)];
        }

        if (preg_match('/\blast\s+(\d{1,2})\s+years?\b/i', $topic, $m)) {
            return [((int) date('Y')) - (int) $m[1], null, str_replace($m[0], '', $topic)];
        }

        if (preg_match('/\b((?:19|20)\d{2})\b/', $topic, $m)) {
            return [(int) $m[1], (int) $m[1], str_replace($m[0], '', $topic)];
        }

        return [null, null, $topic];
    }

    /** @return array{0: int[], 1: string} */
    private function extractSdgNumbers(string $topic): array
    {
        $numbers = [];

        if (preg_match_all(self::SDG_NUMBER_PATTERN, $topic, $matches)) {
            foreach ($matches[1] as $n) {
                $n = (int) $n;

                if ($n >= 1 && $n <= 17) {
                    $numbers[] = $n;
                }
            }

            $topic = (string) preg_replace(self::SDG_NUMBER_PATTERN, '', $topic);
        }

        // Also match by the goal's title text (e.g. "quality education" ->
        // SDG 4) — config/sdgs.php is the single source of truth for these
        // names elsewhere in the app (see Sdg::getNameAttribute()).
        foreach (config('sdgs', []) as $number => $title) {
            if (mb_stripos($topic, $title) !== false) {
                $numbers[] = (int) $number;
                $topic = str_ireplace($title, '', $topic);
            }
        }

        return [array_values(array_unique($numbers)), $topic];
    }

    /** @return array{0: ?string, 1: string} */
    private function extractAuthor(string $topic): array
    {
        if (preg_match(self::AUTHOR_PATTERN, $topic, $m)) {
            return [trim($m[1]), str_replace($m[0], '', $topic)];
        }

        return [null, $topic];
    }

    /** @return array{0: ?string, 1: string} */
    private function extractResearchType(string $topic): array
    {
        foreach ($this->knownResearchTypes() as $type) {
            if ($type !== '' && mb_stripos($topic, $type) !== false) {
                return [$type, str_ireplace($type, '', $topic)];
            }
        }

        return [null, $topic];
    }

    /**
     * Cached — this is a small, slow-changing lookup table (a handful of
     * distinct values at most), not worth a fresh query per question.
     *
     * @return string[]
     */
    private function knownResearchTypes(): array
    {
        return Cache::remember('chat_planner:research_types', 3600, function () {
            $types = DB::table('stud_file')
                ->whereNotNull('research_type')
                ->distinct()
                ->pluck('research_type')
                ->all();

            // Longest first so a more specific type (e.g. "Applied Thesis")
            // matches before a shorter one that happens to be its
            // substring (e.g. "Thesis") would. Sorted in PHP, not SQL —
            // Postgres rejects an ORDER BY expression that isn't in a
            // SELECT DISTINCT's select list, and this list is small enough
            // (a handful of values) that it doesn't matter.
            usort($types, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

            return $types;
        });
    }
}

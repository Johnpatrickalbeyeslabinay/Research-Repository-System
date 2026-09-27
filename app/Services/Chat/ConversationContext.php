<?php

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Services\Retrieval\CatalogResult;
use App\Services\Retrieval\RetrievalResult;
use Illuminate\Support\Collection;

/**
 * Updates ChatConversation::active_context after every turn — a small,
 * deterministically-derived JSON blob (topic, active filters, the research
 * last discussed) that costs zero extra API calls and is what lets a
 * follow-up like "which one used CNN?" resolve against the right documents
 * on the next turn (see App\Services\Retrieval\QueryPlanner's follow-up
 * scope handling). Deliberately not an LLM-generated summary.
 *
 * MERGES rather than overwrites. The original version replaced the whole
 * blob every turn, which destroyed the very context it existed to keep: a
 * terse follow-up ("which one?") plans a topic of literally "which one",
 * so writing that over "rice disease detection" meant the turn after it
 * had nothing useful left to fall back on. Two rules follow from that:
 *
 *   - a follow-up turn keeps the previous topic instead of overwriting it
 *     with its own pronoun-shaped one;
 *   - documents accumulate in a rolling window, not just the current turn's
 *     hits, so "compare these two studies" still works when the two studies
 *     were surfaced in two different turns.
 */
class ConversationContext
{
    /** How many recently-discussed documents to carry forward. */
    private const RECENT_DOCUMENT_LIMIT = 8;

    /**
     * @param  bool  $isFollowUp  this turn was resolved against prior context
     *         (App\Services\Chat\QueryCondenser reported a rewrite), so its
     *         own topic is a pronoun, not a subject worth remembering
     */
    public function update(ChatConversation $conversation, RetrievalResult $retrieval, bool $isFollowUp = false): void
    {
        if ($retrieval->isEmpty()) {
            // No evidence this turn — leave the prior context alone rather
            // than overwrite it with an empty one. A follow-up right after
            // a no-results answer should still be able to fall back to
            // whatever was being discussed before it.
            return;
        }

        $this->write(
            $conversation,
            $retrieval->filters,
            $retrieval->documentIds(),
            $retrieval->files,
            $isFollowUp
        );

        // Remember how deep this turn read. RetrievalTierPlanner uses it so a
        // bare follow-up ("and the sample size?") after a full-text turn stays
        // at full text instead of silently dropping back to abstracts — it is
        // continuing the same line of enquiry, and the pronoun carries none of
        // the depth markers the planner would otherwise look for.
        //
        // Stored here rather than in retrieval_meta because the planner reads
        // active_context, and the planner only honours it for follow-ups — so a
        // genuinely fresh question re-plans from scratch and one deep turn does
        // not make the rest of the conversation expensive.
        if ($retrieval->tier !== null) {
            $context = $conversation->active_context ?? [];
            $context['tier'] = $retrieval->tier->tier->value;
            $context['tier_reason'] = $retrieval->tier->reason;
            $conversation->active_context = $context;
            $conversation->save();
        }
    }

    /**
     * The catalog counterpart (App\Services\Retrieval\CatalogService).
     * Without this, a catalog turn left no trace at all and "of those,
     * which are from 2025?" had nothing to narrow.
     */
    public function updateFromCatalog(ChatConversation $conversation, CatalogResult $catalog, bool $isFollowUp = false): void
    {
        if ($catalog->isEmpty()) {
            return;
        }

        $this->write(
            $conversation,
            $catalog->filters,
            $catalog->documentIds(),
            $catalog->files(),
            $isFollowUp
        );
    }

    /**
     * @param  int[]  $documentIds  this turn's documents, best first
     * @param  Collection<int, \App\Models\UserFile>  $files  keyed by id
     */
    private function write(
        ChatConversation $conversation,
        array $filters,
        array $documentIds,
        Collection $files,
        bool $isFollowUp
    ): void {
        $previous = $conversation->active_context ?? [];

        $topic = $filters['topic'] ?? '';

        // A follow-up's topic is its pronoun ("which one", "summarize it").
        // Keeping the prior subject is the whole point of this class.
        if ($isFollowUp && ! empty($previous['topic'])) {
            $topic = $previous['topic'];
        }

        $conversation->active_context = [
            'topic' => $topic,
            'filters' => [
                'year_from' => $filters['year_from'] ?? null,
                'year_to' => $filters['year_to'] ?? null,
                'sdg_numbers' => $filters['sdg_numbers'] ?? [],
                'research_type' => $filters['research_type'] ?? null,
                'author' => $filters['author'] ?? null,
            ],
            // This turn's documents — the tightest scope for an immediate
            // follow-up.
            'research_ids' => $documentIds,
            // A rolling window across turns, newest first. QueryPlanner
            // widens to this when research_ids alone can't satisfy a
            // back-reference spanning more than one turn.
            'recent_research_ids' => $this->mergeRecentIds($previous, $documentIds),
            // id => title, so PromptBuilder can NAME what's in scope and
            // the model can resolve "the second one" / "the seaweed study"
            // instead of being handed bare numbers it can't interpret.
            'documents' => $this->mergeDocumentTitles($previous, $documentIds, $files),
        ];

        $conversation->save();
    }

    /**
     * @param  int[]  $documentIds
     * @return int[]
     */
    private function mergeRecentIds(array $previous, array $documentIds): array
    {
        $merged = array_merge(
            $documentIds,
            array_map('intval', $previous['recent_research_ids'] ?? [])
        );

        return array_slice(array_values(array_unique($merged)), 0, self::RECENT_DOCUMENT_LIMIT);
    }

    /**
     * @param  int[]  $documentIds
     * @param  Collection<int, \App\Models\UserFile>  $files
     * @return array<int, string>
     */
    private function mergeDocumentTitles(array $previous, array $documentIds, Collection $files): array
    {
        $titles = [];

        // This turn's documents first, so the freshest titles win on
        // collision and survive the slice below.
        foreach ($documentIds as $id) {
            $file = $files->get($id);

            if ($file !== null) {
                $titles[$id] = $file->title;
            }
        }

        foreach ($previous['documents'] ?? [] as $id => $title) {
            $titles[(int) $id] ??= $title;
        }

        return array_slice($titles, 0, self::RECENT_DOCUMENT_LIMIT, preserve_keys: true);
    }
}

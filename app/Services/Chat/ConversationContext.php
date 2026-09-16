<?php

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Services\Retrieval\RetrievalResult;

/**
 * Updates ChatConversation::active_context after every turn — a small,
 * deterministically-derived JSON blob (topic, active filters, the research
 * ids last discussed) that costs zero extra API calls and is what lets a
 * follow-up like "which one used CNN?" resolve against the right documents
 * on the next turn (see App\Services\Retrieval\QueryPlanner's follow-up
 * scope handling). Deliberately not an LLM-generated summary — see the
 * project's RAG plan, Deliverable 14/15.
 */
class ConversationContext
{
    public function update(ChatConversation $conversation, RetrievalResult $retrieval): void
    {
        if ($retrieval->isEmpty()) {
            // No evidence this turn — leave the prior context alone rather
            // than overwrite it with an empty one. A follow-up right after
            // a no-results answer should still be able to fall back to
            // whatever was being discussed before it.
            return;
        }

        $filters = $retrieval->filters;

        $conversation->active_context = [
            'topic' => $filters['topic'] ?? '',
            'filters' => [
                'year_from' => $filters['year_from'] ?? null,
                'year_to' => $filters['year_to'] ?? null,
                'sdg_numbers' => $filters['sdg_numbers'] ?? [],
                'research_type' => $filters['research_type'] ?? null,
                'author' => $filters['author'] ?? null,
            ],
            'research_ids' => $retrieval->documentIds(),
        ];

        $conversation->save();
    }
}

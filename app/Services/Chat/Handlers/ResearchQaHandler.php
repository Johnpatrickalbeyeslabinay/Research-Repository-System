<?php

namespace App\Services\Chat\Handlers;

use App\Services\Chat\PromptBuilder;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Turn\ChatTurn;
use App\Services\Chat\Turn\TurnHandler;
use App\Services\Chat\Turn\TurnOutcome;
use App\Services\Retrieval\RetrievalService;

/**
 * The full RAG path: retrieve evidence, then answer from it and only it.
 *
 * Also the router's default — anything no classifier claimed is a research
 * question, which is the pipeline's foundational assumption.
 */
final class ResearchQaHandler implements TurnHandler
{
    public function __construct(
        private RetrievalService $retrieval,
        private PromptBuilder $prompts,
    ) {}

    public function handles(): array
    {
        return [Intent::ResearchQa, Intent::ExternalLiterature];
    }

    public function handle(ChatTurn $turn): TurnOutcome
    {
        $result = $this->retrieval->retrieve(
            question: $turn->question(),
            user: $turn->user,
            activeContext: $turn->conversation->active_context,
            pinnedStudFileId: $turn->conversation->stud_file_id,
        );

        if ($result->isEmpty()) {
            // Zero-token: nothing cleared min_similarity, so the model is never
            // called. Kept out of history because this sentence is a worked
            // example of declining, and one failed lookup should not make the
            // next few answers likelier to give up too.
            return TurnOutcome::canned(
                content: "I couldn't find enough information in the research repository to answer that. "
                    .'Try a broader topic, different keywords, or browse the repository directly.',
                retrievalMeta: $turn->routed->toMeta() + [
                    'no_evidence' => true,
                    'nearest_document_ids' => $result->nearestDocumentIds,
                ],
                suggestedResearch: $result->nearestDocumentIds,
            );
        }

        return TurnOutcome::answered(
            prompt: $this->prompts->build($turn->conversation, $result, $turn->question(), $turn->userMessage->id),
            retrievalMeta: $turn->routed->toMeta() + [
                'no_evidence' => false,
                'chunks_considered' => count($result->chunks),
                'documents_used' => count($result->documentIds()),
                'filters' => $result->filters,
                'relaxed_filters' => $result->relaxedFilters,
                'prompt_version' => PromptBuilder::promptVersion(),
            ] + ($result->tier?->toMeta() ?? []),
            retrieval: $result,
            chunksRetrieved: count($result->chunks),
            // Per-tier answer ceiling — the single biggest cost lever, since
            // gpt-4o-mini bills output at 4x input.
            maxOutputTokens: $result->tier?->tier->maxOutputTokens(),
        );
    }
}

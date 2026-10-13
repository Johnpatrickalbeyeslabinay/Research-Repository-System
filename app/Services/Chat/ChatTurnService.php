<?php

namespace App\Services\Chat;

use App\Exceptions\ChatUnavailableException;
use App\Exceptions\EmbeddingFailedException;
use App\Models\AiUsageLog;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\UserFile;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Routing\IntentRouter;
use App\Services\Chat\Turn\ChatTurn;
use App\Services\Chat\Turn\TurnHandler;
use App\Services\Chat\Turn\TurnOutcome;
use App\Services\External\ExternalLiteratureService;
use App\Support\ChatLlmCallTracker;
use App\Support\TokenEstimator;
use Illuminate\Support\Str;

/**
 * One turn, start to finish.
 *
 * Every side effect in a chat turn happens HERE and nowhere else: the single
 * LLM call, both ai_usage_logs rows, both chat_messages rows, the citation
 * pass, the conversation-context update, the title and counters. Handlers
 * describe what should happen (see App\Services\Chat\Turn\TurnOutcome) and
 * perform none of it.
 *
 * The original was a 542-line controller method whose five branches each
 * carried a byte-identical copy of the same try/catch, the same failure
 * AiUsageLog row, the same success row and the same markCalled(). It was the
 * duplication rather than the branching that made it hard to change safely —
 * three copies of a cost-accounting write are three chances for one to drift,
 * and the point of ai_usage_logs is that it is complete.
 *
 * Funnelling everything through one orchestrator also buys a guarantee no
 * amount of reviewing that chain could: a turn cannot make two LLM calls,
 * because handlers cannot make even one.
 */
class ChatTurnService
{
    /** @var array<string, TurnHandler> keyed by Intent value */
    private array $handlers = [];

    /**
     * @param  iterable<TurnHandler>  $handlers
     */
    public function __construct(
        iterable $handlers,
        private IntentRouter $router,
        private ChatAnswerService $answerer,
        private CitationBuilder $citations,
        private ConversationContext $context,
        private ChatLlmCallTracker $llmCallTracker,
        private ExternalLiteratureService $external,
        private TokenEstimator $tokens,
    ) {
        foreach ($handlers as $handler) {
            foreach ($handler->handles() as $intent) {
                // First registration wins, so a specialised handler declared
                // before a general one keeps the intent. ResearchQaHandler
                // claims ExternalLiterature as a FALLBACK for when the external
                // feature is disabled — registration order is what makes
                // ExternalLiteratureHandler win when it is enabled.
                $this->handlers[$intent->value] ??= $handler;
            }
        }
    }

    /**
     * @return array{
     *     user_message: ChatMessage,
     *     assistant_message: ChatMessage,
     *     suggested_research: array,
     *     tier: ?string,
     * }
     *
     * @throws ChatUnavailableException
     */
    public function respond(ChatConversation $conversation, string $rawQuestion, ?User $user): array
    {
        $requestId = (string) Str::uuid();

        $routed = $this->router->route(
            $rawQuestion,
            isPinnedConversation: $conversation->stud_file_id !== null,
            activeContext: $conversation->active_context,
        );

        // Persisted BEFORE anything else, deliberately: a provider timeout
        // should cost the user their answer, not their typing.
        $userMessage = $conversation->messages()->create([
            'role' => 'user',
            'content' => $rawQuestion,
        ]);

        $turn = new ChatTurn($routed, $conversation, $userMessage, $user, $requestId);
        $outcome = $this->handle($turn);

        $suggested = $outcome->suggestedResearch;

        if ($outcome->needsLlm()) {
            [$assistantMessage, $suggested] = $this->answerWithLlm($turn, $outcome);
        } else {
            $assistantMessage = $this->persistCanned($turn, $outcome);
        }

        if ($outcome->excludeUserMessage) {
            $userMessage->update(['excluded_from_history' => true]);
        }

        $this->updateContext($conversation, $outcome);
        $this->updateConversation($conversation, $routed, $turn->question());

        return [
            'user_message' => $userMessage,
            'assistant_message' => $assistantMessage,
            'suggested_research' => $this->suggestedResearchCards($suggested),
            'tier' => $outcome->retrieval?->tier?->tier->value,
        ];
    }

    /**
     * Handlers embed the question (RetrievalService, CatalogHandler) before any
     * answer call, so an embedding outage surfaces here rather than in
     * answerWithLlm(). Left uncaught it was a 500, which with APP_DEBUG on
     * showed the user OpenAI's raw error and wrote no failure row — a dead key
     * was invisible in ai_usage_logs.
     *
     * @throws ChatUnavailableException
     */
    private function handle(ChatTurn $turn): TurnOutcome
    {
        try {
            return $this->resolveHandler($turn->routed->intent)->handle($turn);
        } catch (EmbeddingFailedException $e) {
            AiUsageLog::record([
                'user_id' => $turn->user?->id,
                'conversation_id' => $turn->conversation->id,
                'request_id' => $turn->requestId,
                'operation' => 'query_embedding',
                'model' => config('services.openai.embedding_model'),
                'chunks_retrieved' => 0,
                'success' => false,
                'error_code' => class_basename($e),
            ]);

            throw ChatUnavailableException::providerError();
        }
    }

    /**
     * ExternalLiterature falls back to the research pipeline when the feature
     * is off, so a deployment with it disabled still ANSWERS the question from
     * the local corpus rather than refusing it.
     */
    private function resolveHandler(Intent $intent): TurnHandler
    {
        if ($intent === Intent::ExternalLiterature && ! $this->external->isAvailable()) {
            return $this->handlers[Intent::ResearchQa->value];
        }

        return $this->handlers[$intent->value]
            ?? $this->handlers[Intent::ResearchQa->value];
    }

    /**
     * The ONE place ChatAnswerService is called, and the only place a
     * chat_answer/catalog usage row is written.
     *
     * @return array{0: ChatMessage, 1: int[]} the message, and any suggestion
     *         ids derived from an uncited answer
     *
     * @throws ChatUnavailableException
     */
    private function answerWithLlm(ChatTurn $turn, TurnOutcome $outcome): array
    {
        $base = [
            'user_id' => $turn->user?->id,
            'conversation_id' => $turn->conversation->id,
            'request_id' => $turn->requestId,
            'operation' => $outcome->usageOperation,
            'chunks_retrieved' => $outcome->chunksRetrieved,
        ];

        // Captured before sending: ai_usage_logs stores no text, so this is the
        // only way to compare what TokenEstimator predicted against what the
        // provider actually billed (research:calibrate-token-estimator).
        $estimated = $this->tokens->estimateMessages($outcome->prompt['messages']);

        try {
            $answer = $this->answerer->answer($outcome->prompt['messages'], $outcome->maxOutputTokens);
            $this->llmCallTracker->markCalled();
        } catch (ChatUnavailableException $e) {
            AiUsageLog::record($base + [
                'model' => config('chat.models.answer'),
                'success' => false,
                'error_code' => class_basename($e),
            ]);

            throw $e;
        }

        AiUsageLog::record($base + [
            'model' => $answer['model'],
            'prompt_tokens' => $answer['prompt_tokens'],
            'cached_prompt_tokens' => $answer['cached_prompt_tokens'] ?? null,
            'completion_tokens' => $answer['completion_tokens'],
            'total_tokens' => ($answer['prompt_tokens'] ?? 0) + ($answer['completion_tokens'] ?? 0),
            'latency_ms' => $answer['latency_ms'],
            'success' => true,
        ]);

        $files = $outcome->retrieval?->files ?? $outcome->catalog?->files() ?? collect();
        $built = $this->citations->build($answer['content'], $outcome->prompt['evidence_order'] ?? [], $files);

        // The system prompt tells the model to cite any study it names, but
        // that is a prompt-only rule and gpt-4o-mini does not always follow it
        // (verified live). When evidence WAS retrieved and the model cited none
        // of it, surface the retrieved documents as suggestions so the user is
        // not left with an un-actionable wall of titles.
        $suggested = empty($built['citations'])
            ? ($outcome->retrieval?->documentIds() ?? $outcome->catalog?->documentIds() ?? [])
            : [];

        $message = $turn->conversation->messages()->create([
            'role' => 'assistant',
            'content' => $built['text'],
            'citations' => $built['citations'],
            'retrieval_meta' => $outcome->retrievalMeta + [
                'chunks_used' => count($built['citations']),
                'estimated_prompt_tokens' => $estimated,
            ],
            'model' => $answer['model'],
            'prompt_tokens' => $answer['prompt_tokens'],
            'completion_tokens' => $answer['completion_tokens'],
            'latency_ms' => $answer['latency_ms'],
        ]);

        return [$message, $suggested];
    }

    private function persistCanned(ChatTurn $turn, TurnOutcome $outcome): ChatMessage
    {
        // A zero-token turn that still consulted the catalog (the count
        // shortcut) or an external index records that, so the operation is
        // visible in cost reports even though it spent nothing.
        if ($outcome->usageOperation !== 'none') {
            AiUsageLog::record([
                'user_id' => $turn->user?->id,
                'conversation_id' => $turn->conversation->id,
                'request_id' => $turn->requestId,
                'operation' => $outcome->usageOperation,
                'model' => 'none',
                'chunks_retrieved' => 0,
                'success' => true,
            ]);
        }

        return $turn->conversation->messages()->create([
            'role' => 'assistant',
            'content' => $outcome->content,
            'external_sources' => $outcome->externalSources ?: null,
            'excluded_from_history' => $outcome->excludeFromHistory,
            'retrieval_meta' => $outcome->retrievalMeta,
        ]);
    }

    private function updateContext(ChatConversation $conversation, TurnOutcome $outcome): void
    {
        if ($outcome->retrieval !== null) {
            $this->context->update(
                $conversation,
                $outcome->retrieval,
                $outcome->retrieval->filters['is_follow_up'] ?? false,
            );

            return;
        }

        if ($outcome->catalog !== null) {
            $this->context->updateFromCatalog($conversation, $outcome->catalog);
        }
    }

    private function updateConversation(ChatConversation $conversation, $routed, string $question): void
    {
        // Titled from the first turn that actually asks something. A
        // conversation opening with a bare "hi" would otherwise be titled "hi"
        // forever in the history list.
        if ($conversation->title === null && ! $routed->isZeroToken()) {
            $conversation->title = Str::limit($question, 60);
        }

        $conversation->message_count += 2;
        $conversation->last_message_at = now();
        $conversation->save();
    }

    /**
     * Hydrates suggestion cards, collapsing duplicate PDFs.
     *
     * The corpus can hold the identical file submitted more than once, and
     * listing that content three times is noise rather than three suggestions.
     *
     * @param  int[]  $studFileIds
     * @return array<int, array{id: int, title: string, url: string}>
     */
    private function suggestedResearchCards(array $studFileIds): array
    {
        if (empty($studFileIds)) {
            return [];
        }

        $files = UserFile::whereIn('id', $studFileIds)->get(['id', 'title', 'pdf_hash'])->keyBy('id');

        $cards = [];
        $seenHashes = [];

        foreach ($studFileIds as $id) {
            $file = $files->get($id);

            if ($file === null) {
                continue;
            }

            // A row with no pdf_hash is never deduped against anything — a
            // missing hash carries no dedupe information.
            $key = $file->pdf_hash ?: "id:{$file->id}";

            if (isset($seenHashes[$key])) {
                continue;
            }

            $seenHashes[$key] = true;

            $cards[] = [
                'id' => $file->id,
                'title' => $file->title,
                'url' => route('research.show', $file->id),
            ];
        }

        return $cards;
    }
}

<?php

namespace App\Http\Controllers;

use App\Exceptions\ChatUnavailableException;
use App\Http\Requests\SendChatMessageRequest;
use App\Models\AiUsageLog;
use App\Models\ChatConversation;
use App\Models\UserFile;
use App\Services\Chat\ChatAnswerService;
use App\Services\Chat\CitationBuilder;
use App\Services\Chat\ConversationContext;
use App\Services\Chat\GreetingDetector;
use App\Services\Chat\IntentDetector;
use App\Services\Chat\PlatformQuestionDetector;
use App\Services\Chat\PromptBuilder;
use App\Services\Retrieval\CatalogService;
use App\Services\Retrieval\QueryPlanner;
use App\Services\Retrieval\RetrievalService;
use App\Support\ChatLlmCallTracker;
use App\Support\ChatQuota;
use App\Support\ResearchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * The AI research assistant's chat API — authenticated users only (see the
 * project's RAG plan: no anonymous /public chat). Retrieval, grounding, and
 * citation logic all live in the services this controller wires together;
 * nothing AI- or permission-specific is decided in this class itself.
 */
class ChatController extends Controller
{
    public function __construct(
        private RetrievalService $retrieval,
        private PromptBuilder $prompts,
        private ChatAnswerService $answerer,
        private CitationBuilder $citations,
        private ConversationContext $context,
        private ChatLlmCallTracker $llmCallTracker,
        private GreetingDetector $greetings,
        private PlatformQuestionDetector $platformQuestions,
        private IntentDetector $intents,
        private QueryPlanner $planner,
        private CatalogService $catalog,
    ) {}

    /** Last 20 conversations, most recently active first. */
    public function index()
    {
        $conversations = ChatConversation::where('user_id', Auth::id())
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'title', 'stud_file_id', 'last_message_at']);

        return response()->json(['conversations' => $conversations]);
    }

    /**
     * Creates a new, empty conversation. stud_file_id (optional) pins it
     * to a single research document (research/show's "Ask about this
     * research") — retrieval on every subsequent message() call is then
     * scoped to that document only.
     */
    public function store(Request $request)
    {
        $studFileId = $request->integer('stud_file_id') ?: null;

        if ($studFileId !== null) {
            $file = UserFile::findOrFail($studFileId);
            abort_unless(ResearchAccess::allows(Auth::user(), $file), 403);
        }

        $conversation = ChatConversation::create([
            'user_id' => Auth::id(),
            'stud_file_id' => $studFileId,
        ]);

        return response()->json([
            'id' => $conversation->id,
            'stud_file_id' => $conversation->stud_file_id,
        ], 201);
    }

    public function show(ChatConversation $conversation)
    {
        abort_unless($conversation->user_id === Auth::id(), 404);

        return response()->json([
            'id' => $conversation->id,
            'title' => $conversation->title,
            'stud_file_id' => $conversation->stud_file_id,
            'messages' => $conversation->messages()->get(['id', 'role', 'content', 'citations', 'created_at']),
        ]);
    }

    public function destroy(ChatConversation $conversation)
    {
        abort_unless($conversation->user_id === Auth::id(), 404);

        $conversation->delete();

        return response()->json(['success' => true]);
    }

    public function message(SendChatMessageRequest $request, ChatConversation $conversation)
    {
        abort_unless($conversation->user_id === Auth::id(), 404);

        // Re-checked on every turn, not just at conversation creation — a
        // pinned document's visibility can change mid-conversation, and
        // retrieval must never keep serving a paper that's since gone
        // private (see App\Support\ResearchAccess's class docblock).
        if ($conversation->stud_file_id !== null) {
            abort_unless(ResearchAccess::allows(Auth::user(), $conversation->studFile), 403);
        }

        $requestId = (string) Str::uuid();
        $question = $request->validated()['message'];

        $userMessage = $conversation->messages()->create(['role' => 'user', 'content' => $question]);

        $suggestedResearch = [];
        $result = null;
        $catalogResult = null;

        // Zero-token, zero-latency path — "hi" or "thanks" is not a
        // research question, and running it through retrieval just
        // produces a stiff "the evidence doesn't contain information
        // about your greeting" answer. Checked BEFORE retrieval, not just
        // before the LLM call, so a greeting never even costs an embedding
        // call. Deliberately narrow (see GreetingDetector's docblock) — a
        // message that only starts with "hi" but goes on to ask something
        // real still runs the full pipeline below.
        if ($this->greetings->isGreeting($question)) {
            $assistantMessage = $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $this->greetings->greetingReply($conversation->studFile?->title),
                'retrieval_meta' => ['skipped' => 'greeting'],
            ]);
        } elseif ($this->greetings->isAcknowledgement($question)) {
            $assistantMessage = $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $this->greetings->acknowledgementReply(),
                'retrieval_meta' => ['skipped' => 'acknowledgement'],
            ]);
        } elseif ($this->platformQuestions->isPlatformQuestion($question)) {
            // Answered from PromptBuilder::PLATFORM_INFO only — no
            // retrieval, no REPOSITORY EVIDENCE block at all. Retrieval is
            // skipped ENTIRELY here (not just the LLM call, unlike the
            // no-evidence path below) because running it anyway risks
            // exactly the contamination this path exists to prevent: a
            // weakly-matching paper's evidence getting attached to a
            // question that has nothing to do with any specific paper —
            // verified live, this is precisely what was happening before.
            $prompt = $this->prompts->buildPlatformOnly($conversation, $question);

            try {
                $answer = $this->answerer->answer($prompt['messages']);
                $this->llmCallTracker->markCalled();
            } catch (ChatUnavailableException $e) {
                AiUsageLog::record([
                    'user_id' => Auth::id(),
                    'conversation_id' => $conversation->id,
                    'request_id' => $requestId,
                    'operation' => 'chat_answer',
                    'model' => config('chat.models.answer'),
                    'chunks_retrieved' => 0,
                    'success' => false,
                    'error_code' => class_basename($e),
                ]);

                return response()->json(['success' => false, 'message' => $e->getMessage()], 503);
            }

            $assistantMessage = $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $answer['content'],
                'retrieval_meta' => ['skipped' => 'platform_question', 'prompt_version' => PromptBuilder::PROMPT_VERSION],
                'model' => $answer['model'],
                'prompt_tokens' => $answer['prompt_tokens'],
                'completion_tokens' => $answer['completion_tokens'],
                'latency_ms' => $answer['latency_ms'],
            ]);

            AiUsageLog::record([
                'user_id' => Auth::id(),
                'conversation_id' => $conversation->id,
                'request_id' => $requestId,
                'operation' => 'chat_answer',
                'model' => $answer['model'],
                'prompt_tokens' => $answer['prompt_tokens'],
                'completion_tokens' => $answer['completion_tokens'],
                'total_tokens' => ($answer['prompt_tokens'] ?? 0) + ($answer['completion_tokens'] ?? 0),
                'latency_ms' => $answer['latency_ms'],
                'chunks_retrieved' => 0,
                'success' => true,
            ]);
        } elseif ($conversation->stud_file_id === null && $this->intents->isCatalogQuestion($question)) {
            // Document-level path: "what research is available?", "recommend
            // papers about fish", "how many studies are there?". These ask
            // what EXISTS, and the answer lives in stud_file metadata — no
            // chunk of any PDF contains a list of the repository's contents,
            // so running these through chunk retrieval matched nothing above
            // min_similarity and always produced the no-evidence reply
            // (verified live against a fully indexed corpus). Skipped
            // entirely for a pinned single-research chat, which has nothing
            // to browse. See App\Services\Retrieval\CatalogService.
            $plan = $this->planner->plan($question, $conversation->active_context, null);

            // A bare inventory question ("list repository available") has no
            // real topic — embedding it produces a meaningless vector, so
            // skip the semantic arm and the embedding call with it.
            $queryVector = $this->intents->isBareInventory($question)
                ? null
                : $this->retrieval->topicVector($plan['topic']);

            $catalog = $catalogResult = $this->catalog->browse(Auth::user(), $plan, $queryVector);

            if ($catalog->isEmpty()) {
                // Zero-token path — nothing to describe, so nothing worth
                // asking the LLM to describe. Deliberately does NOT mark
                // ChatLlmCallTracker, so it never burns the daily quota.
                $assistantMessage = $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $this->catalog->emptyMessage($plan),
                    'retrieval_meta' => [
                        'skipped' => 'catalog_empty',
                        'intent' => IntentDetector::CATALOG,
                        'filters' => $plan,
                    ],
                ]);

                AiUsageLog::record([
                    'user_id' => Auth::id(),
                    'conversation_id' => $conversation->id,
                    'request_id' => $requestId,
                    'operation' => 'catalog',
                    'model' => 'none',
                    'chunks_retrieved' => 0,
                    'success' => true,
                ]);
            } else {
                $prompt = $this->prompts->buildCatalog($conversation, $catalog, $question);

                try {
                    $answer = $this->answerer->answer(
                        $prompt['messages'],
                        (int) config('chat.catalog.max_output_tokens', 450)
                    );
                    $this->llmCallTracker->markCalled();
                } catch (ChatUnavailableException $e) {
                    AiUsageLog::record([
                        'user_id' => Auth::id(),
                        'conversation_id' => $conversation->id,
                        'request_id' => $requestId,
                        'operation' => 'catalog',
                        'model' => config('chat.models.answer'),
                        'chunks_retrieved' => $catalog->documents->count(),
                        'success' => false,
                        'error_code' => class_basename($e),
                    ]);

                    return response()->json(['success' => false, 'message' => $e->getMessage()], 503);
                }

                // CitationBuilder consumes the catalog's synthetic
                // evidence_order exactly as it does a research turn's real
                // chunks — see PromptBuilder::buildCatalog().
                $built = $this->citations->build($answer['content'], $prompt['evidence_order'], $catalog->files());

                // Same guarantee as the research path: if the model listed
                // studies without citing them, the user still gets something
                // clickable rather than an un-actionable wall of titles.
                if (empty($built['citations'])) {
                    $suggestedResearch = $this->suggestedResearchCards($catalog->documentIds());
                }

                $assistantMessage = $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $built['text'],
                    'citations' => $built['citations'],
                    'retrieval_meta' => [
                        'intent' => IntentDetector::CATALOG,
                        'documents_shown' => $catalog->documents->count(),
                        'documents_total' => $catalog->total,
                        'semantic' => $catalog->semantic,
                        'filters' => $plan,
                        'prompt_version' => PromptBuilder::PROMPT_VERSION,
                    ],
                    'model' => $answer['model'],
                    'prompt_tokens' => $answer['prompt_tokens'],
                    'completion_tokens' => $answer['completion_tokens'],
                    'latency_ms' => $answer['latency_ms'],
                ]);

                AiUsageLog::record([
                    'user_id' => Auth::id(),
                    'conversation_id' => $conversation->id,
                    'request_id' => $requestId,
                    'operation' => 'catalog',
                    'model' => $answer['model'],
                    'prompt_tokens' => $answer['prompt_tokens'],
                    'completion_tokens' => $answer['completion_tokens'],
                    'total_tokens' => ($answer['prompt_tokens'] ?? 0) + ($answer['completion_tokens'] ?? 0),
                    'latency_ms' => $answer['latency_ms'],
                    'chunks_retrieved' => $catalog->documents->count(),
                    'success' => true,
                ]);
            }
        } else {
            $searchStart = microtime(true);
            $result = $this->retrieval->retrieve(
                question: $question,
                user: Auth::user(),
                activeContext: $conversation->active_context,
                pinnedStudFileId: $conversation->stud_file_id,
            );
            $retrievalLatencyMs = (int) round((microtime(true) - $searchStart) * 1000);

            AiUsageLog::record([
                'user_id' => Auth::id(),
                'conversation_id' => $conversation->id,
                'request_id' => $requestId,
                'operation' => 'query_embedding',
                'model' => config('services.openai.embedding_model'),
                'latency_ms' => $retrievalLatencyMs,
                'chunks_retrieved' => count($result->chunks),
                'success' => true,
            ]);

            if ($result->isEmpty()) {
                // The zero-token path — the LLM is never called when
                // nothing cleared min_similarity. See RetrievalResult::isEmpty().
                $suggestedResearch = $this->suggestedResearchCards($result->nearestDocumentIds);

                $assistantMessage = $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => "I couldn't find enough information in the research repository to answer that. "
                        .'Try a broader topic, different keywords, or browse the repository directly.',
                    'retrieval_meta' => [
                        'no_evidence' => true,
                        'nearest_document_ids' => $result->nearestDocumentIds,
                    ],
                ]);
            } else {
                $prompt = $this->prompts->build($conversation, $result, $question);

                try {
                    $answer = $this->answerer->answer($prompt['messages']);
                    $this->llmCallTracker->markCalled();
                } catch (ChatUnavailableException $e) {
                    AiUsageLog::record([
                        'user_id' => Auth::id(),
                        'conversation_id' => $conversation->id,
                        'request_id' => $requestId,
                        'operation' => 'chat_answer',
                        'model' => config('chat.models.answer'),
                        'chunks_retrieved' => count($result->chunks),
                        'success' => false,
                        'error_code' => class_basename($e),
                    ]);

                    return response()->json(['success' => false, 'message' => $e->getMessage()], 503);
                }

                $built = $this->citations->build($answer['content'], $prompt['evidence_order'], $result->files);

                // The system prompt instructs the model to cite any study
                // it names, even one it's only mentioning as "not what you
                // asked, but here's what's available" — but that's a
                // prompt-only rule, not a guarantee, and gpt-4o-mini
                // doesn't always follow it (verified live: it can describe
                // a specific study's topic/author/location in a "this
                // doesn't answer your question" reply with zero [n]
                // markers). When that happens the user is left with no way
                // to click through to something the answer clearly already
                // knew about. This is a hard guarantee instead of a hope:
                // if evidence was retrieved but the model cited none of it,
                // surface the actual retrieved documents as suggestions —
                // the same mechanism the true no-evidence path already uses.
                if (empty($built['citations'])) {
                    $suggestedResearch = $this->suggestedResearchCards($result->documentIds());
                }

                $assistantMessage = $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $built['text'],
                    'citations' => $built['citations'],
                    'retrieval_meta' => [
                        'no_evidence' => false,
                        'chunks_considered' => count($result->chunks),
                        'chunks_used' => count($built['citations']),
                        'documents_used' => count($result->documentIds()),
                        'filters' => $result->filters,
                        'relaxed_filters' => $result->relaxedFilters,
                        'prompt_version' => PromptBuilder::PROMPT_VERSION,
                    ],
                    'model' => $answer['model'],
                    'prompt_tokens' => $answer['prompt_tokens'],
                    'completion_tokens' => $answer['completion_tokens'],
                    'latency_ms' => $answer['latency_ms'],
                ]);

                AiUsageLog::record([
                    'user_id' => Auth::id(),
                    'conversation_id' => $conversation->id,
                    'request_id' => $requestId,
                    'operation' => 'chat_answer',
                    'model' => $answer['model'],
                    'prompt_tokens' => $answer['prompt_tokens'],
                    'completion_tokens' => $answer['completion_tokens'],
                    'total_tokens' => ($answer['prompt_tokens'] ?? 0) + ($answer['completion_tokens'] ?? 0),
                    'latency_ms' => $answer['latency_ms'],
                    'chunks_retrieved' => count($result->chunks),
                    'success' => true,
                ]);
            }
        }

        // Only a real retrieval or catalog turn has anything worth folding
        // into the conversation's active_context — a greeting/
        // acknowledgement leaves whatever topic/filters/research_ids were
        // already there alone (see ConversationContext::update()'s
        // no-evidence handling for the same reasoning).
        //
        // is_follow_up matters here: it tells ConversationContext to keep
        // the PRIOR topic rather than overwrite it with this turn's
        // pronoun-shaped one ("which one", "summarize it"), which is what
        // lets a third turn still resolve against the real subject.
        if ($result !== null) {
            $this->context->update($conversation, $result, $result->filters['is_follow_up'] ?? false);
        } elseif ($catalogResult !== null) {
            $this->context->updateFromCatalog($conversation, $catalogResult);
        }

        if ($conversation->message_count === 0) {
            $conversation->title = Str::limit($question, 60);
        }

        $conversation->message_count += 2;
        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'user_message' => ['id' => $userMessage->id, 'content' => $userMessage->content],
            'assistant_message' => [
                'id' => $assistantMessage->id,
                'content' => $assistantMessage->content,
                'citations' => $assistantMessage->citations ?? [],
                'suggested_research' => $suggestedResearch,
            ],
            'conversation' => ['id' => $conversation->id, 'title' => $conversation->title],
            // Lets the widget show "X of Y questions today" and disable
            // itself proactively, rather than the limit only ever
            // surfacing as a surprise 429 (see App\Support\ChatQuota).
            //
            // ChatDailyQuota records this turn's usage AFTER $next()
            // returns this response — the cache hasn't been incremented
            // yet at this point, so ChatQuota::status() alone would report
            // last turn's count. If this turn reached the LLM, it WILL be
            // counted by the time the client sees this, so account for it
            // here rather than show a number that's already one behind.
            'quota' => $this->quotaStatusIncludingThisTurn(),
        ]);
    }

    /**
     * See the 'quota' key's comment in message() for why this exists
     * instead of just calling ChatQuota::status() directly.
     *
     * @return array{used: int, limit: int, remaining: int}
     */
    private function quotaStatusIncludingThisTurn(): array
    {
        $status = ChatQuota::status(Auth::id());

        if ($this->llmCallTracker->wasCalled()) {
            $status['used']++;
            $status['remaining'] = max(0, $status['remaining'] - 1);
        }

        return $status;
    }

    /**
     * Lightweight cards for the "couldn't find enough evidence" state —
     * the best-scoring documents even though nothing cleared
     * min_similarity (see RetrievalResult::$nearestDocumentIds). These ids
     * already passed through ResearchAccess inside VectorSearchService, so
     * no further permission check is needed before hydrating them.
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

        // Preserve $studFileIds' relevance order (best-scoring first), and
        // keep only the first document per distinct PDF. The corpus can
        // contain the identical file submitted more than once (verified
        // live: 3 stud_file rows, same pdf_hash, from duplicate test
        // submissions) — showing that content three times as three
        // separate "you might mean this" cards is noise, not three real
        // suggestions. A row with no pdf_hash (a legacy record predating
        // that column) is never deduped against anything else, since a
        // missing hash carries no real dedupe information.
        $seenHashes = [];
        $cards = [];

        foreach ($studFileIds as $id) {
            $file = $files->get($id);

            if ($file === null) {
                continue;
            }

            $hashKey = $file->pdf_hash ?? "id:{$file->id}";

            if (isset($seenHashes[$hashKey])) {
                continue;
            }

            $seenHashes[$hashKey] = true;

            $cards[] = [
                'id' => $file->id,
                'title' => $file->title,
                'url' => route('research.show', $file->id),
            ];
        }

        return $cards;
    }
}

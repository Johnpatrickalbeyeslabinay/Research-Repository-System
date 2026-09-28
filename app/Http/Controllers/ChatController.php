<?php

namespace App\Http\Controllers;

use App\Exceptions\ChatUnavailableException;
use App\Http\Requests\SendChatMessageRequest;
use App\Models\ChatConversation;
use App\Models\UserFile;
use App\Services\Chat\ChatTurnService;
use App\Support\ChatLlmCallTracker;
use App\Support\ChatQuota;
use App\Support\ResearchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The AI research assistant's chat API — authenticated users only (see the
 * project's RAG plan: no anonymous /public chat).
 *
 * HTTP concerns only: ownership, the pinned-document access re-check, mapping a
 * provider outage onto a 503, and the JSON contract the widget depends on. One
 * turn's actual work — routing, retrieval, the single LLM call, usage logging,
 * persistence, citations — lives in App\Services\Chat\ChatTurnService and the
 * handlers behind it.
 *
 * message() was 542 lines across five branches, each carrying its own copy of
 * the same try/catch, failure log, success log and persistence.
 */
class ChatController extends Controller
{
    public function __construct(
        private ChatTurnService $turns,
        private ChatLlmCallTracker $llmCallTracker,
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
            // external_sources is selected too, so replaying history renders
            // external cards exactly as the live turn did rather than silently
            // dropping them.
            'messages' => $conversation->messages()->get([
                'id', 'role', 'content', 'citations', 'external_sources', 'created_at',
            ]),
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

        try {
            $turn = $this->turns->respond(
                $conversation,
                $request->validated()['message'],
                Auth::user(),
            );
        } catch (ChatUnavailableException $e) {
            // The user's message is already persisted (deliberately, before any
            // API call), so an outage costs them their answer rather than their
            // typing.
            return response()->json(['success' => false, 'message' => $e->getMessage()], 503);
        }

        return response()->json([
            'user_message' => [
                'id' => $turn['user_message']->id,
                'content' => $turn['user_message']->content,
            ],
            'assistant_message' => [
                'id' => $turn['assistant_message']->id,
                'content' => $turn['assistant_message']->content,
                'citations' => $turn['assistant_message']->citations ?? [],
                // A separate key from 'citations', matching the separate column.
                // The widget renders these into a separate container, so nothing
                // downstream has to check a discriminator to avoid presenting an
                // outside paper as one of this repository's holdings.
                'external_sources' => $turn['assistant_message']->external_sources ?? [],
                'suggested_research' => $turn['suggested_research'],
                // Which retrieval tier answered. The widget offers "Go deeper"
                // only on an abstract-tier answer, so the affordance appears
                // exactly when there is more text to read.
                'tier' => $turn['tier'],
            ],
            'conversation' => ['id' => $conversation->id, 'title' => $conversation->title],
            // Lets the widget show "X of Y questions today" and disable the
            // composer at zero, rather than surfacing the limit as a 429 after
            // the fact.
            'quota' => $this->quotaStatusIncludingThisTurn(),
        ]);
    }

    /**
     * The quota status including the turn just served.
     *
     * App\Http\Middleware\ChatDailyQuota increments the counter AFTER the
     * response is built, so reading ChatQuota::status() directly here would
     * always show a number one behind what the user just spent.
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
}

<?php

namespace App\Http\Middleware;

use App\Support\ChatLlmCallTracker;
use App\Support\ChatQuota;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Per-user, per-day cap on chatbot questions that actually reach the LLM
 * (config('chat.limits.per_day')) — on top of the route's throttle:6,1,
 * which only bounds burst rate. Counted against the `database` cache
 * store, keyed per user per calendar day (App\Support\ChatQuota is the
 * single source of truth for that key/limit — also used by ChatController
 * to show the user their remaining quota, not just enforce it here).
 *
 * Only a turn that actually called the LLM counts — ChatController marks
 * ChatLlmCallTracker (a request-scoped singleton, NOT a $request->attributes
 * flag — see that class's docblock for why the latter doesn't work here)
 * right after a successful ChatAnswerService::answer() call; the
 * zero-token "not enough evidence" path (RetrievalResult::isEmpty()) and
 * any validation/permission failure never mark it, so neither burns quota.
 */
class ChatDailyQuota
{
    public function __construct(private ChatLlmCallTracker $llmCallTracker) {}

    public function handle(Request $request, Closure $next): Response
    {
        $userId = Auth::id();
        $limit = ChatQuota::limit();
        $key = ChatQuota::key($userId);

        if (ChatQuota::used($userId) >= $limit) {
            return response()->json([
                'success' => false,
                'message' => "You've reached today's limit of {$limit} AI assistant questions. Please try again tomorrow.",
                'quota' => ['used' => $limit, 'limit' => $limit, 'remaining' => 0],
            ], 429);
        }

        $response = $next($request);

        if ($this->llmCallTracker->wasCalled()) {
            $this->recordUsage($key);
        }

        return $response;
    }

    /**
     * Increment is lock-serialized (the `database` cache store's
     * cache_locks table backs this) so two concurrent requests from the
     * same user can't both read the same pre-increment count and both
     * proceed — the pre-$next() limit check above is still a plain read
     * (deliberately: locking around the whole request, including the
     * OpenAI round-trip, would serialize a user's own concurrent chat
     * messages for no real benefit), so a true dead-heat can let at most
     * one request over the limit through, never an unbounded burst.
     */
    private function recordUsage(string $key): void
    {
        Cache::lock($key.':lock', 5)->block(2, function () use ($key) {
            Cache::add($key, 0, now()->endOfDay());
            Cache::increment($key);
        });
    }
}

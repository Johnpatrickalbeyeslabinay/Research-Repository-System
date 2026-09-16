<?php

namespace App\Support;

/**
 * Request-scoped flag letting App\Http\Controllers\ChatController tell
 * App\Http\Middleware\ChatDailyQuota that this turn actually reached the
 * LLM (as opposed to the zero-token no-evidence path, or a validation/
 * permission failure) — so only a real LLM call burns daily quota.
 *
 * Deliberately a container singleton, NOT a $request->attributes flag.
 * Laravel resolves a FormRequest-typed controller parameter (see
 * SendChatMessageRequest) into a NEW Request instance and rebinds it as
 * the container's "current request" — it does not mutate the original
 * Request object a middleware's `handle()` is holding. Setting an
 * attribute on the FormRequest inside the controller is therefore
 * invisible to middleware reading its own (different) $request object
 * after $next() returns. A singleton has no such aliasing problem: both
 * sides resolve the identical instance from the container.
 *
 * Bound as a singleton in AppServiceProvider — its lifetime is one HTTP
 * request (a fresh container per request in the normal, non-Octane setup
 * this app runs under).
 */
class ChatLlmCallTracker
{
    private bool $called = false;

    public function markCalled(): void
    {
        $this->called = true;
    }

    public function wasCalled(): bool
    {
        return $this->called;
    }
}

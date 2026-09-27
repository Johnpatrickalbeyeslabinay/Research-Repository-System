<?php

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Services\Chat\SmallTalk\CannedReplies;
use App\Services\Chat\SmallTalk\SmallTalkDetector;
use App\Services\Chat\SmallTalk\SmallTalkMatch;

/**
 * Deterministic, zero-token detection of greetings and short
 * acknowledgements ("hi", "thanks", "ok") that don't warrant running
 * retrieval or calling the LLM at all. Without this, "hi" was being sent
 * straight into the RAG pipeline as if it were a research question — it
 * would retrieve whatever weakly-matched chunks existed and the model
 * would (correctly, but unhelpfully) respond that the evidence doesn't
 * contain information about a greeting. A chat assistant should just say
 * hello back.
 *
 * Deliberately narrow: only matches a message that IS a greeting/
 * acknowledgement on its own, not one that merely starts with one — "hi,
 * what research is available about AI?" must still run retrieval, since
 * it's a real question wearing a greeting as a prefix.
 *
 * ---
 *
 * The matching itself now lives in App\Services\Chat\SmallTalk\
 * SmallTalkDetector; this class is the adapter that keeps the old API. The
 * anchored regexes it used to own (/^(hi+|hello+|hey+|...)$/) required the
 * message to be a greeting and nothing else whatsoever, so "hi there",
 * "hello po", "good day sir", "helo", "kamusta" and "magandang umaga" all
 * fell through to the full pipeline and cost a query embedding plus a
 * ~2,500-token answer call each. Users are at CSU Aparri in Cagayan, so the
 * Tagalog and Ilocano cases are ordinary traffic, not an edge case.
 *
 * The narrowness above is preserved exactly: SmallTalkDetector's residual
 * guard means a greeting with real content after it is never claimed here.
 */
class GreetingDetector
{
    public function __construct(
        private SmallTalkDetector $smallTalk,
        private CannedReplies $replies,
    ) {}

    public function isGreeting(string $message): bool
    {
        return $this->smallTalk->match($message)->isGreeting();
    }

    public function isAcknowledgement(string $message): bool
    {
        return $this->smallTalk->match($message)->isAcknowledgement();
    }

    /**
     * True when the message is neither a real question nor recognisable small
     * talk — keyboard mash, punctuation only, a single stray character. Also a
     * zero-token path: there is nothing to retrieve and nothing to answer.
     */
    public function isUnintelligible(string $message): bool
    {
        $match = $this->smallTalk->match($message);

        return $match->isWholeMessage && $match->category === SmallTalkMatch::UNINTELLIGIBLE;
    }

    /**
     * A short, warm reply — picked from a small pool so a session with
     * several "hi"s in it doesn't read as a robot repeating itself, without
     * spending an actual AI call on something this simple.
     *
     * $conversation and $message are optional only so the previous signature
     * keeps working. Without $conversation the rotation cannot advance and
     * every greeting returns the same sentence; without $message a Tagalog or
     * Ilocano greeting is answered from the English pool.
     */
    public function greetingReply(
        ?string $pinnedResearchTitle,
        ?ChatConversation $conversation = null,
        string $message = '',
    ): string {
        return $this->replies->greeting(
            $conversation ?? new ChatConversation,
            $pinnedResearchTitle,
            $this->isFilipinoGreeting($message),
        );
    }

    public function acknowledgementReply(?ChatConversation $conversation = null): string
    {
        return $this->replies->acknowledgement($conversation ?? new ChatConversation);
    }

    public function unintelligibleReply(?ChatConversation $conversation = null): string
    {
        return $this->replies->unintelligible($conversation ?? new ChatConversation);
    }

    public function identityReply(?ChatConversation $conversation = null): string
    {
        return $this->replies->identity($conversation ?? new ChatConversation);
    }

    public function outOfScopeReply(?ChatConversation $conversation = null): string
    {
        return $this->replies->outOfScope($conversation ?? new ChatConversation);
    }

    private function isFilipinoGreeting(string $message): bool
    {
        return (bool) preg_match('/\b(kamusta|kumusta|kmusta|musta|magandang|naimbag|salamat)\b/i', $message);
    }
}

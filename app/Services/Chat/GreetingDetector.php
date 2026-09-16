<?php

namespace App\Services\Chat;

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
 */
class GreetingDetector
{
    private const GREETING_PATTERN = '/^(hi+|hello+|hey+|yo|sup|greetings|good\s*(morning|afternoon|evening))[\s!.,]*$/i';

    private const ACKNOWLEDGEMENT_PATTERN = '/^(thanks?(?:\s*you)?|thx|ty|appreciate\s*it|ok(?:ay)?|cool|nice|great|got\s*it|alright|sounds?\s*good)[\s!.,]*$/i';

    public function isGreeting(string $message): bool
    {
        return (bool) preg_match(self::GREETING_PATTERN, trim($message));
    }

    public function isAcknowledgement(string $message): bool
    {
        return (bool) preg_match(self::ACKNOWLEDGEMENT_PATTERN, trim($message));
    }

    /**
     * A short, warm reply — picked from a small pool so a session with
     * several "hi"s in it doesn't read as a robot repeating itself, without
     * spending an actual AI call on something this simple.
     */
    public function greetingReply(?string $pinnedResearchTitle): string
    {
        if ($pinnedResearchTitle !== null) {
            return "Hi! Ask me anything about \"{$pinnedResearchTitle}\" — methodology, findings, recommendations, whatever you need.";
        }

        $replies = [
            "Hi there! I'm the AI research assistant for this repository. Ask me about any research on file — topics, methodology, findings — and I'll answer with citations.",
            'Hello! Happy to help you explore the research repository. What would you like to know?',
            "Hey! I can answer questions about the research papers in this repository, with citations. What are you looking for?",
        ];

        return $replies[array_rand($replies)];
    }

    public function acknowledgementReply(): string
    {
        $replies = [
            "You're welcome! Let me know if you have more questions about the research here.",
            'Glad that helped! Ask away if anything else comes up.',
            "Anytime! I'm here if you want to dig into more research.",
        ];

        return $replies[array_rand($replies)];
    }
}

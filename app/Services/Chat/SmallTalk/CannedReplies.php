<?php

namespace App\Services\Chat\SmallTalk;

use App\Models\ChatConversation;

/**
 * Zero-token replies for zero-token turns.
 *
 * Selection is seeded from the conversation's own message_count rather than
 * array_rand(). Two reasons: array_rand() is nondeterministic, which is
 * exactly why no test ever asserted a reply's content, and it can repeat the
 * same sentence twice in a row, which reads more robotic than a fixed
 * rotation would. message_count increments by 2 per turn, so with a pool size
 * coprime to 2 consecutive greetings always advance.
 *
 * Replies stay in English even when the greeting was Tagalog or Ilocano —
 * only the opening word is localised. The whole UI is English, and
 * half-translated copy reads worse than none.
 */
class CannedReplies
{
    /** @var string[] */
    private const GREETING = [
        "Hi there! I'm the AI research assistant for this repository. Ask me about any research on file — topics, methodology, findings — and I'll answer with citations.",
        'Hello! Happy to help you explore the research repository. What would you like to know?',
        'Hey! I can answer questions about the research papers in this repository, with citations. What are you looking for?',
        "Hi! Ask me anything about the research collected here — I'll cite the studies I draw from so you can read them yourself.",
        'Hello! You can ask me what research exists on a topic, or dig into any single study. What are you working on?',
    ];

    /** @var string[] */
    private const GREETING_FILIPINO = [
        "Kumusta! I'm the research assistant for this repository. Ask me about any study on file and I'll answer with citations.",
        'Kumusta! Happy to help you explore the research here. What would you like to know?',
        "Kumusta! Ask me what research exists on a topic, or about any single study — I'll cite my sources.",
        'Kumusta! I can help you find and understand the research in this repository. What are you looking for?',
        "Kumusta! Tell me a topic and I'll show you what research the repository holds on it.",
    ];

    /** @var string[] */
    private const ACKNOWLEDGEMENT = [
        "You're welcome! Let me know if you have more questions about the research here.",
        'Glad that helped! Ask away if anything else comes up.',
        "Anytime! I'm here if you want to dig into more research.",
        'Happy to help. Ask me anything else about the studies on file.',
        'No problem! Let me know what else you want to look into.',
    ];

    /** @var string[] */
    private const FAREWELL = [
        'Goodbye! Come back anytime you need to look something up in the repository.',
        'Take care! Ask me again whenever you need research on a topic.',
        'See you! The repository is here whenever you want to explore it.',
    ];

    /**
     * Answers "who are you" / "what can you do" for nothing. Each states the
     * real boundary — this repository's research, plus how the platform works —
     * because the honest limit is more useful than a vague offer to help, and
     * it is the same boundary SYSTEM_PROMPT enforces.
     *
     * @var string[]
     */
    private const IDENTITY = [
        "I'm the AI research assistant for the CSU Aparri fisheries research repository. I answer two kinds of questions: what the research on file says (with citations to the exact study), and how this platform works — submitting research, the review process, how to browse. Ask me about a topic, an author, a year, or an SDG.",
        "I'm this repository's research assistant — not a general-purpose chatbot. I can search the fisheries and aquatic research stored here and answer from it with citations, and I can explain how submission and approval work. What would you like to look up?",
        "I'm an AI assistant built into this research repository. I only know what's in the studies on file here, plus how the platform itself works — so I'll cite the papers I draw from, and I'll tell you plainly when the repository doesn't cover something.",
    ];

    /**
     * For clearly off-domain requests. Declines in one sentence and redirects,
     * with no lecture — the user asked a reasonable question in the wrong place.
     *
     * @var string[]
     */
    private const OUT_OF_SCOPE = [
        "That's outside what I can help with — I only cover the research stored in this repository and how this platform works. Ask me about a fisheries or aquaculture topic, an author, or a year and I'll search for you.",
        "I can't help with that one. I'm limited to the research papers in this repository and questions about the platform itself. Is there a study or topic here you're looking for?",
        "That's not something this repository covers. I can search the fisheries and aquatic research on file, or explain how submitting and reviewing research works here.",
    ];

    /** @var string[] */
    private const UNINTELLIGIBLE = [
        "Sorry, I didn't catch that. Try asking about a research topic — for example, \"what research is available about aquaculture?\"",
        "I'm not sure what you meant there. You can ask me what research exists on a topic, or about a specific study.",
        'That did not come through clearly. Ask me about a topic, an author, or a year and I will search the repository.',
    ];

    public function greeting(ChatConversation $conversation, ?string $pinnedResearchTitle, bool $filipino = false): string
    {
        // A pinned conversation gets one specific reply rather than a rotation:
        // naming the study the user is already looking at is more useful than
        // variety, and it tells them the chat is scoped to that paper.
        if ($pinnedResearchTitle !== null) {
            return "Hi! Ask me anything about \"{$pinnedResearchTitle}\" — methodology, findings, recommendations, whatever you need.";
        }

        return $this->pick($filipino ? self::GREETING_FILIPINO : self::GREETING, $conversation);
    }

    public function acknowledgement(ChatConversation $conversation): string
    {
        return $this->pick(self::ACKNOWLEDGEMENT, $conversation);
    }

    public function farewell(ChatConversation $conversation): string
    {
        return $this->pick(self::FAREWELL, $conversation);
    }

    public function unintelligible(ChatConversation $conversation): string
    {
        return $this->pick(self::UNINTELLIGIBLE, $conversation);
    }

    public function identity(ChatConversation $conversation): string
    {
        return $this->pick(self::IDENTITY, $conversation);
    }

    public function outOfScope(ChatConversation $conversation): string
    {
        return $this->pick(self::OUT_OF_SCOPE, $conversation);
    }

    /**
     * A one-line greeting to prepend to a real answer when the user opened
     * with a hello. Free — it never enters the prompt, it is stitched onto the
     * response afterwards, so the model is not asked to be polite on our
     * behalf.
     */
    public function greetingPrefix(bool $filipino = false): string
    {
        return $filipino ? 'Kumusta! ' : 'Hi! ';
    }

    /**
     * @param  string[]  $pool
     */
    private function pick(array $pool, ChatConversation $conversation): string
    {
        // message_count moves in steps of 2 (one user + one assistant row per
        // turn), so dividing by 2 gives a turn index that advances by exactly
        // one each time.
        $turn = (int) floor(((int) $conversation->message_count) / 2);

        return $pool[$turn % count($pool)];
    }
}

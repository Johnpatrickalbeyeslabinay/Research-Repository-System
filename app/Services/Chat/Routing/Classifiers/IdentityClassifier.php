<?php

namespace App\Services\Chat\Routing\Classifiers;

use App\Services\Chat\Routing\ClassificationInput;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Routing\RoutedIntent;
use App\Services\Chat\Routing\TurnClassifier;

/**
 * "Who are you", "what can you do", "are you ChatGPT".
 *
 * These were a live bug, not a missed optimisation. None of them match any of
 * PlatformQuestionDetector's six patterns, so every one fell through to full
 * research retrieval: a query embedding, then a ~2,500-token answer call,
 * against evidence that has nothing to do with the question — to produce a
 * sentence the application already knows.
 *
 * Priority 20 puts this ahead of PlatformClassifier, which would otherwise
 * claim "what can I do here" via its
 * /\bwhat can (i|you|we) do (here|on this ...)\b/ pattern and spend a real
 * call answering it from PLATFORM_INFO.
 */
final class IdentityClassifier implements TurnClassifier
{
    /**
     * Anchored on the whole message rather than searched within it, because
     * "who are the authors of the seaweed study" and "what can you tell me
     * about tilapia methodology" are real research questions that contain
     * these words. Trailing punctuation and politeness particles are allowed.
     */
    private const PATTERNS = [
        // Identity.
        '/^\s*(so\s+)?(who|what)\s+(are|r)\s+(you|u)\b[^.?!]{0,20}[?.!]*\s*$/i',
        '/^\s*(are|r)\s+(you|u)\s+(a\s+)?(chatgpt|gpt|chat\s*gpt|openai|an?\s+ai|a\s+bot|a\s+robot|a\s+human|a\s+person|real)\b[^.?!]{0,15}[?.!]*\s*$/i',
        '/^\s*who\s+(made|built|created|developed|trained)\s+(you|this\s+(bot|assistant|ai))\b[^.?!]{0,20}[?.!]*\s*$/i',
        '/^\s*what\s+(model|ai|llm)\s+(are\s+you|do\s+you\s+use|is\s+this)\b[^.?!]{0,20}[?.!]*\s*$/i',
        '/^\s*(what\'?s|what\s+is)\s+your\s+name\b[^.?!]{0,10}[?.!]*\s*$/i',

        // Capability. Deliberately does not claim "what can you tell me
        // about X" — that is a research question.
        '/^\s*what\s+can\s+(you|u)\s+do\b[^.?!]{0,20}[?.!]*\s*$/i',
        '/^\s*what\s+can\s+(i|we)\s+ask\b[^.?!]{0,25}[?.!]*\s*$/i',
        '/^\s*(what|how)\s+(do\s+you|can\s+you)\s+help\b[^.?!]{0,20}[?.!]*\s*$/i',
        '/^\s*help\s*[?.!]*\s*$/i',
        '/^\s*(what\s+are\s+your\s+capabilities|what\s+do\s+you\s+do)\b[^.?!]{0,15}[?.!]*\s*$/i',
        '/^\s*how\s+do\s+(i|you)\s+use\s+(you|this\s+(chat|assistant|bot))\b[^.?!]{0,20}[?.!]*\s*$/i',
    ];

    public function priority(): int
    {
        return 20;
    }

    public function classify(ClassificationInput $input): ?RoutedIntent
    {
        $question = trim($input->question);

        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, $question)) {
                return new RoutedIntent(
                    intent: Intent::Identity,
                    question: $input->question,
                    rawQuestion: $input->rawQuestion,
                    strippedSmallTalk: $input->strippedSmallTalk,
                    classifier: 'identity',
                    signals: ['matched_pattern' => $pattern],
                );
            }
        }

        return null;
    }
}

<?php

namespace App\Services\Chat;

/**
 * Deterministic, zero-token detection of questions about the PLATFORM
 * itself ("what is this system", "how do I submit", "how does approval
 * work") as opposed to questions about research content. Checked in
 * ChatController::message() BEFORE retrieval runs, so a platform question
 * never gets contaminated by an irrelevant paper's "evidence" the way it
 * did before this existed — verified live: "tell me about the research
 * repository system" was pulling in an unrelated document's metadata
 * chunk purely because that document happened to be titled "Research
 * Repository," producing a confusing near-miss answer instead of the
 * platform explanation the question actually wanted. See
 * App\Services\Chat\PromptBuilder::PLATFORM_INFO for what the model
 * answers from once this fires, and ::buildPlatformOnly() for the
 * evidence-free prompt used for it.
 *
 * Deliberately requires an explicit platform-referring word (system,
 * platform, website, app, steps, process, "how...work") — bare
 * "repository" alone is NOT enough, since "search the repository" /
 * "what's in the repository" are common, legitimate ways to ask a real
 * research-content question and must still run the full pipeline.
 */
class PlatformQuestionDetector
{
    private const PATTERNS = [
        // "what is this system/platform/website/app", "tell me about this platform"
        '/\b(what is|what\'s|explain|describe|tell me about)\b[^.?!]{0,40}\b(this|the)\b[^.?!]{0,20}\b(system|platform|website|web\s?app|application)\b/i',
        // The screenshot's exact phrasing, kept as its own explicit pattern
        // since "research repository system" is this app's own name, not
        // just a generic platform word.
        '/\bresearch repository system\b/i',
        // "how does this/the system/platform/website/app work"
        '/\bhow (does|do)\b[^.?!]{0,30}\b(this|the)?\s*(system|platform|website|app|application)\b[^.?!]{0,20}\bwork\b/i',
        // "how do I / can I submit/upload/publish/register/sign up/log in"
        '/\bhow (do|can|would) (i|you|we|one)\b[^.?!]{0,20}\b(submit|upload|publish|register|sign\s?up|log\s?in|login)\b/i',
        // "what are the steps", "what is the process", approval/review workflow
        '/\b(what are the steps|what is the process|how does (the )?(approval|review|submission) (process|work))\b/i',
        // "what can I do here / on this site"
        '/\bwhat can (i|you|we) do (here|on this (site|platform|system|app))\b/i',
    ];

    public function isPlatformQuestion(string $message): bool
    {
        $message = trim($message);

        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }
}

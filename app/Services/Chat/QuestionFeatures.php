<?php

namespace App\Services\Chat;

/**
 * Deterministic facts about a question, shared by everything that needs them.
 *
 * DEPTH_PATTERNS started life as IntentDetector::CONTENT_QUESTION_PATTERNS,
 * where it answered "is this about what studies SAY rather than what studies
 * EXIST?". The retrieval tier planner needs the same judgement for a different
 * decision — "does answering this require the body text, or will the abstract
 * do?" — and those two questions have exactly the same answer.
 *
 * Promoted to one shared const rather than copied, following the pattern
 * QueryCondenser and QueryPlanner already use for BACK_REFERENCE_PATTERN: two
 * classes that must agree cannot be allowed to hold separate copies of the
 * rule they agree on. If they drifted, a question would be sent to chunk
 * retrieval by one and answered from abstracts by the other.
 */
final class QuestionFeatures
{
    /**
     * Questions about what studies SAY rather than what studies EXIST.
     *
     * IntentDetector's original CONTENT_QUESTION_PATTERNS, verbatim and
     * unextended. Adding to this set changes what counts as a catalog
     * question, which is a different decision from what counts as needing full
     * text — a distinction worth stating because getting it wrong is easy:
     * an earlier version of this class folded the tiering patterns below into
     * this one, which immediately made "how many studies are in the
     * repository?" stop being a catalog question and start being a research
     * question. Extend DEPTH_PATTERNS for tiering; leave this alone.
     */
    public const CONTENT_PATTERNS = [
        '/\b(methodolog|method|approach|technique|procedure|instrument|sampling)\w*\b/i',
        '/\b(finding|result|conclusion|recommendation|implication|outcome)s?\b/i',
        '/\b(summari[sz]e|summary|explain|describe in detail|elaborate)\b/i',
        '/\b(compare|comparison|versus|vs\.?|difference between)\b/i',
        '/\b(data|dataset|sample size|respondents|participants|variables?)\b/i',
        '/\bwhat did\b/i',
        '/\bhow (did|was|were)\b/i',
        '/\bwhy\b/i',
    ];

    /**
     * Questions that need the study's body text, not just its abstract.
     *
     * A superset of CONTENT_PATTERNS: everything about what a study says needs
     * the text, plus these, which ask for the kind of specific the abstract
     * routinely omits. An abstract says "growth improved significantly"; only
     * the body says "by 23.4%".
     *
     * Safe to extend without touching catalog routing, because by the time a
     * question reaches the tier planner the router has already decided it is
     * not a catalog question.
     */
    public const DEPTH_PATTERNS = [
        ...self::CONTENT_PATTERNS,

        // Quantities.
        '/\bhow (many|much|long|often|large)\b/i',
        '/\b(percentage|percent|mean|average|median|correlation|p-value|significan\w*|standard deviation|deviation)\b/i',
        // Anything citing a specific artefact of the document.
        '/\b(table|figure|appendix|equation)\s*\d*\b/i',
        '/\bpage\s+\d+\b/i',
        // A request for the words themselves.
        '/\b(quote|verbatim|exact|word for word)\b/i',
        // Explicit drill-down — also how a user asks for more after an
        // abstract-tier answer offers it.
        '/\b(full text|in detail|more detail|details|deeper|drill down|dig in(to)?|read more|specifics|go deeper)\b/i',
    ];

    /**
     * True when the abstract will not be enough.
     *
     * Erring toward true is the cheap mistake: escalating reuses the cached
     * query vector, so a wrong guess costs one extra Postgres round trip and
     * zero API tokens, while a thin answer costs the user's trust.
     */
    public function needsDepth(string $question): bool
    {
        $question = trim($question);

        if ($question === '') {
            return false;
        }

        foreach (self::DEPTH_PATTERNS as $pattern) {
            if (preg_match($pattern, $question)) {
                return true;
            }
        }

        return false;
    }

    /**
     * True when the question names something an abstract almost never
     * contains — a number, a unit, a statistic.
     *
     * Narrower than needsDepth() and used by EvidenceSufficiencyGate AFTER
     * abstract-tier retrieval, to decide whether to escalate before answering.
     */
    public function demandsSpecifics(string $question): bool
    {
        $specifics = [
            '/\bhow (many|much)\b/i',
            '/\b\d+(\.\d+)?\s*(%|percent|kg|g|mg|ppm|ppt|ha|cm|mm|m|km|°c|c|liters?|litres?)\b/i',
            '/\bn\s*=\s*\d+/i',
            '/\b(p-value|p\s*<|p\s*=|significan\w*|confidence interval|standard deviation)\b/i',
            '/\b(sample size|respondents|participants)\b/i',
            '/\b(table|figure|appendix)\b/i',
            '/\bexact\b/i',
        ];

        foreach ($specifics as $pattern) {
            if (preg_match($pattern, $question)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Content terms, for measuring how much of the question the retrieved
     * evidence actually covers. Stopworded, deduped, 3+ characters.
     *
     * @return string[]
     */
    public function contentTerms(string $question): array
    {
        $stopwords = [
            'the', 'and', 'for', 'are', 'was', 'were', 'what', 'which', 'who',
            'how', 'why', 'when', 'where', 'this', 'that', 'these', 'those',
            'with', 'from', 'about', 'into', 'their', 'there', 'they', 'them',
            'has', 'have', 'had', 'been', 'being', 'does', 'did', 'can', 'could',
            'would', 'should', 'will', 'shall', 'may', 'might', 'must',
            'any', 'all', 'some', 'more', 'most', 'other', 'such', 'than',
            'then', 'also', 'only', 'very', 'much', 'many', 'study', 'studies',
            'research', 'paper', 'papers', 'tell', 'show', 'give', 'find',
            // Back-reference words. These carry no topic of their own — they
            // point at whatever was already being discussed — so counting them
            // as content terms would make a follow-up look like a question
            // about something the evidence does not cover.
            'one', 'ones', 'it', 'its', 'thing', 'about',
        ];

        preg_match_all('/[\p{L}\p{N}]+/u', mb_strtolower($question), $matches);

        $terms = array_filter(
            $matches[0],
            fn (string $word) => mb_strlen($word) >= 3 && ! in_array($word, $stopwords, true),
        );

        return array_values(array_unique($terms));
    }
}

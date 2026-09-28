<?php

namespace App\Services\Chat;

/**
 * Deterministic, zero-token detection of CATALOG questions — questions
 * about the repository's contents as a whole ("what research is
 * available?", "recommend papers about fish", "how many studies are
 * there?") rather than about what a particular study says.
 *
 * Why this has to exist at all: chunk-level vector search structurally
 * cannot answer a corpus-level question. No chunk of any PDF contains the
 * sentence "here is everything in this repository", so "list repository
 * available" matches nothing above min_similarity and falls into the
 * no-evidence path — verified live: it returned "I couldn't find enough
 * information in the research repository to answer that" against a fully
 * indexed corpus. The answer to that question lives in stud_file's
 * metadata, not in the chunk text, so it needs a different retrieval path
 * entirely (App\Services\Retrieval\CatalogService).
 *
 * Checked in ChatController::message() AFTER PlatformQuestionDetector and
 * BEFORE the research pipeline. That ordering matters in both directions:
 * "how does this system work" is a platform question, not a request to
 * list papers; and PlatformQuestionDetector deliberately does NOT claim
 * bare "repository" (see its docblock), precisely so that "what's in the
 * repository" can land here instead.
 *
 * Same deterministic-regex philosophy as GreetingDetector and
 * PlatformQuestionDetector: a lookup pass handles the overwhelming
 * majority of real phrasings for zero tokens, and an LLM classifier is
 * deferred until evaluation shows it's actually needed.
 */
class IntentDetector
{
    public const CATALOG = 'catalog';

    public const RESEARCH_QA = 'research_qa';

    /**
     * Inventory/browse phrasings — "what do you have", "list everything".
     * These ask WHAT EXISTS, not what a study found.
     */
    private const INVENTORY_PATTERNS = [
        // "list the research", "show me the studies", "what papers are available"
        '/\b(list|show me|show us|give me)\b[^.?!]{0,30}\b(research(es)?|stud(y|ies)|papers?|theses|thesis|capstones?|repository)\b/i',
        // "what research is available/stored/here", "what studies do you have"
        '/\bwhat\b[^.?!]{0,30}\b(research(es)?|stud(y|ies)|papers?|theses|thesis)\b[^.?!]{0,30}\b(available|stored|here|do you have|are there|exist|in the repository)\b/i',
        // "what's in the repository", "what is available in the repository"
        '/\bwhat(\'s|s|\s+is|\s+are)\b[^.?!]{0,30}\b(in|inside|stored in|available in|available on|on)\b[^.?!]{0,20}\b(repository|repo|database|system|collection)\b/i',
        // "repository available", "available research" — the terse phrasing
        // from the live bug report ("list repository available").
        '/\brepositor(y|ies)\s+available\b/i',
        '/\bavailable\s+(research(es)?|stud(y|ies)|papers?|theses)\b/i',
        // "how many studies", "how many papers are there"
        '/\bhow many\b[^.?!]{0,30}\b(research(es)?|stud(y|ies)|papers?|theses|thesis|capstones?)\b/i',
        // "what topics/subjects/areas are covered"
        '/\bwhat\b[^.?!]{0,20}\b(topics?|subjects?|areas?|themes?|fields?|categories)\b/i',
        // "what studies were published from 2024 to 2026", "which papers
        // were submitted last year" — a listing question whose only
        // qualifier is a date/provenance verb rather than an "available"
        // keyword. QueryPlanner extracts the year range from these; without
        // this pattern they fell through to chunk search, which has no way
        // to enumerate documents by year.
        '/\b(what|which|list|show)\b[^.?!]{0,30}\b(research(es)?|stud(y|ies)|papers?|theses|thesis|capstones?)\b[^.?!]{0,30}\b(published|written|submitted|conducted|completed|released|added)\b/i',
        // "browse the repository"
        '/\bbrowse\b/i',
    ];

    /**
     * Recommendation/discovery phrasings — "find me something about X".
     * These are topical but still document-level: the right answer is a
     * short list of studies, not a synthesis of one study's findings.
     */
    private const RECOMMENDATION_PATTERNS = [
        '/\b(recommend|suggest)\b/i',
        '/\bfind( me| us)?\b[^.?!]{0,25}\b(research(es)?|stud(y|ies)|papers?|theses|thesis)\b/i',
        '/\b(any|are there( any)?|is there( any)?|do you have( any)?)\b[^.?!]{0,25}\b(research(es)?|stud(y|ies)|papers?|theses)\b/i',
        '/\blooking for\b[^.?!]{0,25}\b(research(es)?|stud(y|ies)|papers?|theses|something|anything)\b/i',
        // "what research is there about X", "which studies are related to X"
        '/\b(which|what)\b[^.?!]{0,20}\b(research(es)?|stud(y|ies)|papers?|theses)\b[^.?!]{0,30}\b(about|on|related to|regarding|concerning|involve|involving|support|supports)\b/i',
    ];

    /**
     * Phrasings that look catalog-ish but are really asking about the
     * CONTENT of studies — these must stay on the full research pipeline,
     * where the actual chunk text lives. Checked first, and it wins.
     *
     * "What methodologies were used in chatbot studies?" matches a
     * recommendation pattern ("what studies ... about"), but answering it
     * needs the Methodology sections themselves, which the catalog path
     * (metadata only) cannot see.
     *
     * Sourced from App\Services\Chat\QuestionFeatures::CONTENT_PATTERNS rather
     * than held here, so the retrieval tier planner can build on the same base
     * instead of keeping a second copy that drifts.
     *
     * Note it is CONTENT_PATTERNS, not that class's DEPTH_PATTERNS. Depth adds
     * quantity phrasings ("how many", "what percentage") because an abstract
     * rarely carries a number — but those must NOT reach this constant, since
     * "how many studies are in the repository?" is a catalog question and
     * pointing this at DEPTH_PATTERNS silently turned it into a research one.
     */
    private const CONTENT_QUESTION_PATTERNS = QuestionFeatures::CONTENT_PATTERNS;

    /**
     * True when this question should be answered from document metadata
     * (CatalogService) rather than chunk text.
     *
     * Callers must not consult this for a pinned single-research
     * conversation — see ChatController::message(); a chat scoped to one
     * paper has nothing to browse.
     */
    public function isCatalogQuestion(string $message): bool
    {
        $message = trim($message);

        if ($message === '') {
            return false;
        }

        // A question about what studies SAY always beats a question about
        // what studies EXIST, even when it's phrased like the latter.
        if ($this->matchesAny($message, self::CONTENT_QUESTION_PATTERNS)) {
            return false;
        }

        return $this->matchesAny($message, self::INVENTORY_PATTERNS)
            || $this->matchesAny($message, self::RECOMMENDATION_PATTERNS);
    }

    /**
     * True for an inventory-style question with no real topic in it
     * ("list everything", "how many studies are there") — CatalogService
     * uses this to skip the semantic arm entirely and just list by
     * recency, since embedding "list repository available" produces a
     * meaningless query vector.
     */
    public function isBareInventory(string $message): bool
    {
        $message = trim($message);

        return $this->matchesAny($message, self::INVENTORY_PATTERNS)
            && ! $this->matchesAny($message, self::RECOMMENDATION_PATTERNS);
    }

    /** @param  string[]  $patterns */
    private function matchesAny(string $message, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }
}

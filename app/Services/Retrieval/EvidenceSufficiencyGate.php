<?php

namespace App\Services\Retrieval;

use App\Services\Chat\QuestionFeatures;

/**
 * Decides, after abstract-tier retrieval and BEFORE the answer call, whether
 * the abstract will actually do.
 *
 * Placement is the whole design. Running this before the LLM call means a turn
 * still makes exactly one answer call even when it escalates — the alternative
 * (answer from abstracts, notice it was thin, answer again from full text)
 * would double the expensive half of the bill to save on the cheap half.
 *
 * Escalating is close to free: RetrievalService caches the query vector for 24
 * hours and the planner/condenser have already run, so a second pass costs one
 * more Postgres round trip and ZERO API tokens. That asymmetry is why this gate
 * is deliberately eager — a needless escalation costs milliseconds, while a
 * thin answer costs the user's trust in every answer after it.
 */
class EvidenceSufficiencyGate
{
    public function __construct(private QuestionFeatures $features) {}

    /**
     * $chunks is the abstract-tier evidence, already reranked. Returns null to
     * answer at the current tier.
     *
     * @param  array<int, array{section:?string, content:string, stud_file_id:int}>  $chunks
     */
    public function shouldEscalate(string $question, array $chunks): ?TierDecision
    {
        // Nothing came back at all. Escalation cannot help — an empty abstract
        // tier means the topic is absent from the corpus, and the no-evidence
        // path (which is already zero-token) is the honest answer.
        if (empty($chunks)) {
            return null;
        }

        // The question names a number, a statistic, a table. Abstracts state
        // THAT something was found, rarely the figure.
        if ($this->features->demandsSpecifics($question)) {
            return TierDecision::escalate(Tier::FullText, 'demands_specifics');
        }

        // Every selected document came back with only its synthetic metadata
        // chunk and no real abstract section. That happens on short or scanned
        // PDFs where SectionDetector found no abstract heading — the metadata
        // chunk alone is a title, authors and keywords, which answers "what is
        // this called" but not "what does it say".
        if ((bool) config('chat.tiers.abstract.escalate_when_no_abstract_chunks', true)
            && ! $this->hasAnyAbstractSection($chunks)) {
            return TierDecision::escalate(Tier::FullText, 'no_abstract_section');
        }

        // The question's content terms barely appear in what came back — a
        // proxy for "the abstract does not discuss the thing being asked
        // about". Deliberately a coverage RATIO rather than a hit count, so a
        // long question is not penalised for having more terms.
        $coverage = $this->termCoverage($question, $chunks);
        $minimum = (float) config('chat.tiers.abstract.min_term_coverage', 0.40);

        if ($coverage < $minimum) {
            return TierDecision::escalate(Tier::FullText, 'low_term_coverage');
        }

        return null;
    }

    /**
     * @param  array<int, array{section:?string}>  $chunks
     */
    private function hasAnyAbstractSection(array $chunks): bool
    {
        foreach ($chunks as $chunk) {
            if (($chunk['section'] ?? null) === 'abstract') {
                return true;
            }
        }

        return false;
    }

    /**
     * What fraction of the question's content terms appear in the evidence.
     *
     * Substring matching rather than whole-word, so "salinity" is found inside
     * "salinities" without a stemmer. Crude, but it only has to separate "this
     * evidence is about the same thing" from "this evidence is about something
     * else", and a wrong call here costs one extra SQL query.
     *
     * @param  array<int, array{content:string}>  $chunks
     */
    private function termCoverage(string $question, array $chunks): float
    {
        $terms = $this->features->contentTerms($question);

        // Fewer than two terms is not a measurement, it is a coin flip: a bare
        // follow-up ("what about that one?") reduces to a single weak token,
        // and judging coverage on it would escalate nearly every pronoun
        // question — the opposite of what this rule is for. Returning full
        // coverage means "this rule has no opinion", leaving the specifics and
        // missing-abstract rules to decide.
        if (count($terms) < 2) {
            return 1.0;
        }

        $haystack = mb_strtolower(implode(' ', array_column($chunks, 'content')));

        $found = 0;

        foreach ($terms as $term) {
            if (str_contains($haystack, $term)) {
                $found++;
            }
        }

        return $found / count($terms);
    }
}

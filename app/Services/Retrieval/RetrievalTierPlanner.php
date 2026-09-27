<?php

namespace App\Services\Retrieval;

use App\Services\Chat\QuestionFeatures;

/**
 * Picks how deep to read before retrieval runs.
 *
 * Deliberately no AI call, matching QueryPlanner's stated position: a
 * regex/lookup pass decides this correctly for the overwhelming majority of
 * real questions at zero cost, and the penalty for being wrong is small —
 * EvidenceSufficiencyGate catches a too-shallow choice in the same turn, and
 * escalation reuses the cached query vector, so it costs a Postgres round trip
 * and no tokens.
 *
 * This is a SEPARATE axis from intent. An intent says what kind of question
 * this is; a tier says how much text answering it needs. Folding them together
 * would produce an enum with a case per combination and force a choice between
 * them at the wrong moment — which is why IntentRouter does not decide tiers.
 */
class RetrievalTierPlanner
{
    public function __construct(private QuestionFeatures $features) {}

    /**
     * @param  array  $queryPlan  QueryPlanner::plan() output
     * @param  ?array  $activeContext  ChatConversation::active_context
     */
    public function plan(
        string $question,
        array $queryPlan,
        ?array $activeContext = null,
        bool $isPinned = false,
    ): TierDecision {
        // 1. Explicit drill-down beats everything. This is how a user answers
        //    the "want me to look deeper into [2]?" offer an abstract-tier
        //    answer makes, so it has to be honoured before any heuristic.
        if ($this->isExplicitDrillDown($question)) {
            return new TierDecision(Tier::FullText, 'explicit_drill_down');
        }

        // 2. Questions about what a study SAYS, or that name a specific number,
        //    need the body text. QuestionFeatures::DEPTH_PATTERNS is the shared
        //    rule IntentDetector also builds on.
        if ($this->features->needsDepth($question)) {
            return new TierDecision(Tier::FullText, 'depth_marker');
        }

        // 3. A comparison needs enough of each study to compare, and abstracts
        //    are too lossy for that — two abstracts can both say "growth
        //    improved" while the bodies disagree about the mechanism.
        if (! empty($queryPlan['is_comparison'])) {
            return new TierDecision(Tier::FullText, 'comparison');
        }

        // 4. Escalation memory. Once a thread has gone deep, a follow-up like
        //    "and the sample size?" must not silently drop back to abstracts —
        //    it is continuing the same line of enquiry.
        //
        //    Only for FOLLOW-UPS: a genuinely fresh question re-runs the rules
        //    above, so one deep turn does not make the rest of the conversation
        //    expensive forever.
        if (($activeContext['tier'] ?? null) === Tier::FullText->value
            && ! empty($queryPlan['is_follow_up'])) {
            return new TierDecision(Tier::FullText, 'follow_up_after_depth');
        }

        // 5. A pinned chat that isn't asking for specifics is almost always
        //    "what is this study about" — which is precisely the abstract of
        //    precisely one document. ~300 tokens instead of ~2,500.
        if ($isPinned) {
            return new TierDecision(Tier::Abstract, 'pinned_overview');
        }

        return new TierDecision(Tier::Abstract, 'default');
    }

    /**
     * Phrases that ask, in so many words, to go deeper. Kept separate from
     * DEPTH_PATTERNS (which contains these too) so the reason recorded on the
     * turn distinguishes "the user asked for detail" from "the question
     * implied detail" — useful when reading tier distribution later.
     */
    private function isExplicitDrillDown(string $question): bool
    {
        return (bool) preg_match(
            '/\b(full text|in detail|more detail|details|go deeper|dig deeper|drill down|read more|tell me more|expand on)\b/i',
            $question,
        );
    }
}

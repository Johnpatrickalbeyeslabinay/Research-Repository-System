<?php

namespace App\Services\Chat\Routing;

/**
 * What a chat message is.
 *
 * This replaces a five-branch if/else chain inside a 542-line
 * ChatController::message(), where the taxonomy existed only as the ORDER of
 * those branches — invisible, untestable, and impossible to extend without
 * editing the chain again.
 *
 * Two of the intents below exist because their absence was a live bug, not a
 * missed optimisation:
 *
 *   Identity  — "who are you" matches none of PlatformQuestionDetector's six
 *               patterns, so it fell through to full retrieval: an embedding
 *               call plus a ~2,500-token answer call, to produce a sentence
 *               that could be a constant.
 *   OutOfScope — "recommend a good restaurant" matches IntentDetector's
 *               /\b(recommend|suggest)\b/i, so it reached CatalogService and
 *               spent an LLM call listing fisheries papers in reply to a
 *               question about dinner.
 *
 * The string values of Catalog and ResearchQa deliberately match the existing
 * IntentDetector::CATALOG / ::RESEARCH_QA constants, so retrieval_meta rows
 * already in the database and the eval harness's fixtures keep parsing.
 */
enum Intent: string
{
    case SmallTalk = 'small_talk';
    case Acknowledgement = 'acknowledgement';
    case Identity = 'identity';
    case Unintelligible = 'unintelligible';
    case Platform = 'platform';
    case OutOfScope = 'out_of_scope';
    case CatalogCount = 'catalog_count';
    case ExternalLiterature = 'external_literature';
    case Catalog = 'catalog';
    case ResearchQa = 'research_qa';

    /**
     * True when this intent must NEVER reach the OpenAI API.
     *
     * Asserted structurally in the test suite (Http::assertNothingSent() plus
     * quota.used === 0), so a future handler cannot quietly start spending
     * tokens on one of these.
     */
    public function isZeroToken(): bool
    {
        return match ($this) {
            self::SmallTalk,
            self::Acknowledgement,
            self::Identity,
            self::Unintelligible,
            self::OutOfScope,
            self::CatalogCount,
            self::ExternalLiterature => true,
            self::Platform,
            self::Catalog,
            self::ResearchQa => false,
        };
    }

    /**
     * The value recorded as retrieval_meta['skipped'], or null for a turn
     * that legitimately may call the model.
     */
    public function skipReason(): ?string
    {
        return $this->isZeroToken() ? $this->value : null;
    }
}

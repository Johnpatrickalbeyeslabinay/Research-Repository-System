<?php

namespace Tests\Unit;

use App\Services\Chat\QuestionFeatures;
use App\Services\Retrieval\RetrievalTierPlanner;
use App\Services\Retrieval\Tier;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Which questions get the cheap path and which need the body text.
 *
 * Tier SELECTION is unit-testable; tier RETRIEVAL is not — the abstract tier's
 * section-filtered query needs pgvector's <=> and a tsvector column, and
 * phpunit.xml points at sqlite where the embedding column does not exist. What
 * the filtered query actually returns is covered by research:eval-chat against
 * the real database.
 */
class RetrievalTierPlannerTest extends TestCase
{
    private RetrievalTierPlanner $planner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->planner = new RetrievalTierPlanner(new QuestionFeatures);
    }

    /**
     * @return array<string, array{0: string, 1: Tier, 2: string}>
     */
    public static function tierCases(): array
    {
        return [
            // --- Abstract is the default, and that is the whole saving ---
            'a plain topic question' => [
                'what is the seaweed study about', Tier::Abstract,
                'A general "what is this about" question is answered by the abstract.',
            ],
            'an author question' => [
                'who wrote the tilapia paper', Tier::Abstract,
                'Authorship is in the metadata chunk — no body text needed.',
            ],
            'a topic lookup' => [
                'anything on mangrove restoration in Aparri', Tier::Abstract,
                'Topical discovery does not need full text.',
            ],

            // --- Depth markers force full text ---
            'methodology' => [
                'what methodology did the seaweed study use', Tier::FullText,
                'Methodology lives in the body, not the abstract.',
            ],
            'findings' => [
                'what were the findings on growth rate', Tier::FullText,
                'A findings question needs the results section.',
            ],
            'why' => [
                'why did salinity affect the yield', Tier::FullText,
                'Causal questions need the discussion.',
            ],
            'how many respondents' => [
                'how many respondents were surveyed', Tier::FullText,
                'An abstract states that a survey happened, rarely its n.',
            ],
            'a statistic' => [
                'what was the p-value for the treatment group', Tier::FullText,
                'Statistics are never in an abstract.',
            ],
            'a table reference' => [
                'what does table 3 show', Tier::FullText,
                'Naming a table means the body.',
            ],
            'a quote request' => [
                'quote the exact recommendation', Tier::FullText,
                'A verbatim request needs the source text.',
            ],
        ];
    }

    #[DataProvider('tierCases')]
    public function test_it_picks_the_expected_tier(string $question, Tier $expected, string $why): void
    {
        $decision = $this->planner->plan($question, $this->plan());

        $this->assertSame(
            $expected,
            $decision->tier,
            "\"{$question}\" planned as {$decision->tier->value} (reason: {$decision->reason}), expected {$expected->value}. {$why}"
        );
    }

    public function test_an_explicit_drill_down_request_is_honoured(): void
    {
        $decision = $this->planner->plan('give me the full details from that study', $this->plan());

        $this->assertSame(Tier::FullText, $decision->tier);
        $this->assertSame(
            'explicit_drill_down',
            $decision->reason,
            'The reason must distinguish "the user asked for detail" from "the question implied it".'
        );
    }

    public function test_a_comparison_needs_full_text(): void
    {
        $decision = $this->planner->plan(
            'which of those had better yield',
            $this->plan(['is_comparison' => true]),
        );

        $this->assertSame(Tier::FullText, $decision->tier);
        $this->assertSame('comparison', $decision->reason);
    }

    /**
     * Once a thread has gone deep, a bare follow-up continues that line of
     * enquiry and must not silently drop back to abstracts.
     */
    public function test_a_follow_up_after_a_deep_turn_stays_deep(): void
    {
        $decision = $this->planner->plan(
            'and the sample size',
            $this->plan(['is_follow_up' => true]),
            activeContext: ['tier' => Tier::FullText->value],
        );

        $this->assertSame(Tier::FullText, $decision->tier);
    }

    /**
     * The counterpart, and the reason escalation memory is gated on
     * is_follow_up: one deep turn must not make the whole rest of the
     * conversation expensive.
     */
    public function test_a_fresh_question_after_a_deep_turn_de_escalates(): void
    {
        $decision = $this->planner->plan(
            'what is the milkfish study about',
            $this->plan(['is_follow_up' => false]),
            activeContext: ['tier' => Tier::FullText->value],
        );

        $this->assertSame(
            Tier::Abstract,
            $decision->tier,
            'A genuinely new question re-runs the rules; it does not inherit the previous turn\'s depth.'
        );
    }

    public function test_a_pinned_overview_question_uses_the_abstract(): void
    {
        $decision = $this->planner->plan('what is this study about', $this->plan(), isPinned: true);

        $this->assertSame(Tier::Abstract, $decision->tier);
        $this->assertSame('pinned_overview', $decision->reason);
    }

    public function test_a_pinned_depth_question_still_reads_the_body(): void
    {
        $decision = $this->planner->plan('what methodology did it use', $this->plan(), isPinned: true);

        $this->assertSame(Tier::FullText, $decision->tier);
    }

    /**
     * The tiers must actually differ in what they cost, or none of this saves
     * anything.
     */
    public function test_the_abstract_tier_is_cheaper_than_full_text(): void
    {
        $this->assertLessThan(
            Tier::FullText->evidenceTokenBudget(),
            Tier::Abstract->evidenceTokenBudget(),
        );

        // Output bills at 4x input on gpt-4o-mini, so this ceiling matters more
        // to the invoice than the evidence budget does.
        $this->assertLessThan(
            Tier::FullText->maxOutputTokens(),
            Tier::Abstract->maxOutputTokens(),
        );

        $this->assertLessThan(
            Tier::FullText->maxDocuments(),
            Tier::Abstract->maxDocuments(),
        );
    }

    /**
     * The guarantee that makes Abstract safe as the default: it always includes
     * the synthetic metadata chunk, which ChunkingService writes for every
     * document, so it can never come back empty where Metadata would have found
     * something.
     */
    public function test_the_abstract_tier_always_includes_the_metadata_chunk(): void
    {
        $this->assertContains('metadata', Tier::Abstract->sections());
        $this->assertContains('abstract', Tier::Abstract->sections());
    }

    public function test_full_text_does_not_restrict_sections(): void
    {
        $this->assertSame([], Tier::FullText->sections());
    }

    /** QueryPlanner::plan()'s shape, with only the keys the tier planner reads. */
    private function plan(array $overrides = []): array
    {
        return array_merge([
            'topic' => 'seaweed',
            'is_comparison' => false,
            'is_broad' => false,
            'is_follow_up' => false,
        ], $overrides);
    }
}

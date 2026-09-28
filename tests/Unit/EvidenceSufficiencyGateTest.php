<?php

namespace Tests\Unit;

use App\Services\Chat\QuestionFeatures;
use App\Services\Retrieval\EvidenceSufficiencyGate;
use App\Services\Retrieval\Tier;
use Tests\TestCase;

/**
 * When abstract-level evidence is not enough.
 *
 * This gate is what makes defaulting to the cheap tier honest: without it,
 * abstract-first would save tokens by answering thinner. It runs BEFORE the
 * answer call, so an escalating turn still makes exactly one LLM call — and
 * the retry reuses the cached query vector, so it costs a Postgres round trip
 * and no API tokens.
 *
 * That asymmetry is the design: escalating needlessly costs milliseconds,
 * answering thinly costs trust in every answer after it. So the gate is
 * deliberately eager, and these tests pin both directions of that.
 */
class EvidenceSufficiencyGateTest extends TestCase
{
    private EvidenceSufficiencyGate $gate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gate = new EvidenceSufficiencyGate(new QuestionFeatures);
    }

    /** An abstract chunk that genuinely discusses the topic. */
    private function abstractChunk(string $content = 'This study examines tilapia growth in brackish ponds across Aparri.'): array
    {
        return ['section' => 'abstract', 'content' => $content, 'stud_file_id' => 1];
    }

    private function metadataChunk(string $content = 'Tilapia Growth Study. Authors: Cruz. Year: 2025. Abstract: Not available.'): array
    {
        return ['section' => 'metadata', 'content' => $content, 'stud_file_id' => 1];
    }

    public function test_good_abstract_evidence_does_not_escalate(): void
    {
        $decision = $this->gate->shouldEscalate(
            'what is the tilapia growth study about',
            [$this->abstractChunk(), $this->metadataChunk()],
        );

        $this->assertNull($decision, 'A general topic question is exactly what the abstract tier is for.');
    }

    public function test_a_question_naming_a_statistic_escalates(): void
    {
        $decision = $this->gate->shouldEscalate(
            'what was the p-value for tilapia growth in brackish ponds',
            [$this->abstractChunk()],
        );

        $this->assertNotNull($decision);
        $this->assertSame(Tier::FullText, $decision->tier);
        $this->assertSame('demands_specifics', $decision->reason);
        $this->assertTrue($decision->escalated);
    }

    public function test_a_question_asking_how_many_escalates(): void
    {
        $decision = $this->gate->shouldEscalate(
            'how many tilapia ponds in Aparri were sampled',
            [$this->abstractChunk()],
        );

        $this->assertNotNull($decision);
        $this->assertSame('demands_specifics', $decision->reason);
    }

    /**
     * Short or scanned PDFs where SectionDetector found no abstract heading
     * leave only the synthetic metadata chunk — a title, authors and keywords.
     * That answers "what is this called", not "what does it say".
     */
    public function test_metadata_only_evidence_escalates(): void
    {
        $decision = $this->gate->shouldEscalate(
            'what does the tilapia growth study in Aparri say about brackish ponds',
            [$this->metadataChunk()],
        );

        $this->assertNotNull($decision);
        $this->assertSame('no_abstract_section', $decision->reason);
    }

    public function test_that_escalation_can_be_disabled_by_config(): void
    {
        config(['chat.tiers.abstract.escalate_when_no_abstract_chunks' => false]);

        $decision = $this->gate->shouldEscalate(
            'tilapia growth Aparri brackish ponds study examines',
            [$this->metadataChunk('Tilapia growth Aparri brackish ponds study examines')],
        );

        $this->assertNull($decision);
    }

    /**
     * The evidence came back about something else entirely — a proxy for "the
     * abstract does not discuss what was asked".
     */
    public function test_evidence_that_does_not_cover_the_question_escalates(): void
    {
        $decision = $this->gate->shouldEscalate(
            'mangrove seedling survival rates in Buguey wetlands',
            [$this->abstractChunk('This study examines tilapia growth in brackish ponds.')],
        );

        $this->assertNotNull($decision);
        $this->assertSame('low_term_coverage', $decision->reason);
    }

    /**
     * Empty evidence means the topic is absent from the corpus. Escalating
     * cannot help, and the no-evidence path is already zero-token — so burning
     * a second query on it would be pure waste.
     */
    public function test_empty_evidence_does_not_escalate(): void
    {
        $this->assertNull($this->gate->shouldEscalate('anything at all', []));
    }

    /**
     * A bare follow-up carries no content terms of its own. Escalating on
     * coverage would then fire on every pronoun question, which is the
     * opposite of what the coverage rule is for.
     */
    public function test_a_question_with_no_content_terms_does_not_escalate_on_coverage(): void
    {
        $decision = $this->gate->shouldEscalate('what about that one', [$this->abstractChunk()]);

        $this->assertNull($decision);
    }

    public function test_the_coverage_threshold_is_configurable(): void
    {
        $question = 'tilapia growth in Aparri brackish ponds and mangrove seedling survival in Buguey';

        // Demanding near-total coverage makes partially-relevant evidence
        // insufficient...
        config(['chat.tiers.abstract.min_term_coverage' => 0.99]);
        $this->assertNotNull($this->gate->shouldEscalate($question, [$this->abstractChunk()]));

        // ...while a permissive threshold accepts it.
        config(['chat.tiers.abstract.min_term_coverage' => 0.01]);
        $this->assertNull($this->gate->shouldEscalate($question, [$this->abstractChunk()]));
    }
}

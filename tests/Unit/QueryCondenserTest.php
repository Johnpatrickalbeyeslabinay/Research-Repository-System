<?php

namespace Tests\Unit;

use App\Services\Chat\QueryCondenser;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The deterministic half of follow-up resolution — the path that must
 * handle the overwhelming majority of real follow-ups without spending a
 * token. Http::preventStrayRequests() makes any accidental LLM call a hard
 * failure rather than a silent cost.
 */
class QueryCondenserTest extends TestCase
{
    private QueryCondenser $condenser;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->condenser = new QueryCondenser;
    }

    private function context(array $overrides = []): array
    {
        return array_merge([
            'topic' => 'rice disease detection',
            'research_ids' => [1],
            'documents' => [1 => 'Rice Disease Detection Using CNN'],
        ], $overrides);
    }

    public function test_a_pronoun_follow_up_inherits_the_prior_topic(): void
    {
        $result = $this->condenser->condense('which one used CNN?', $this->context());

        $this->assertTrue($result['is_follow_up']);
        $this->assertSame('deterministic', $result['method']);
        $this->assertStringContainsString('rice disease detection', $result['topic']);
        $this->assertStringContainsString('which one used CNN?', $result['topic']);
    }

    public function test_a_bare_why_inherits_the_prior_topic(): void
    {
        // "why?" carries no topic at all — embedding it verbatim is the
        // clearest case of the statelessness bug this class fixes.
        $result = $this->condenser->condense('why?', $this->context());

        $this->assertTrue($result['is_follow_up']);
        $this->assertStringContainsString('rice disease detection', $result['topic']);
    }

    public function test_this_research_is_recognised_as_a_back_reference(): void
    {
        // The original BACK_REFERENCE_PATTERN missed this exact phrasing,
        // so "summarize the findings of this research" searched the whole
        // corpus instead of the study under discussion.
        $result = $this->condenser->condense('summarize the findings of this research', $this->context());

        $this->assertTrue($result['is_follow_up']);
    }

    public function test_a_self_contained_question_is_left_alone(): void
    {
        $question = 'What methodologies were used in seaweed diversity studies conducted in Cagayan?';

        $result = $this->condenser->condense($question, $this->context());

        $this->assertFalse($result['is_follow_up']);
        $this->assertSame('none', $result['method']);
        $this->assertSame($question, $result['topic']);
    }

    public function test_no_prior_context_means_no_rewrite(): void
    {
        $result = $this->condenser->condense('which one used CNN?', null);

        $this->assertFalse($result['is_follow_up']);
        $this->assertSame('none', $result['method']);
        $this->assertSame('which one used CNN?', $result['topic']);
    }

    public function test_a_question_already_naming_the_topic_is_not_prefixed_twice(): void
    {
        $result = $this->condenser->condense('tell me more about rice disease detection', $this->context());

        $this->assertTrue($result['is_follow_up']);
        $this->assertSame('none', $result['method']);
        $this->assertSame('tell me more about rice disease detection', $result['topic']);
    }

    public function test_llm_fallback_is_not_used_when_only_one_document_is_in_scope(): void
    {
        // An ordinal reference with nothing to disambiguate between — the
        // deterministic prefix is sufficient, and a stray HTTP call here
        // would fail the test outright.
        $result = $this->condenser->condense('compare the second one', $this->context());

        $this->assertSame('deterministic', $result['method']);
    }

    public function test_llm_fallback_is_disabled_by_config(): void
    {
        config(['chat.condenser.llm_fallback' => false]);

        $result = $this->condenser->condense('compare the second one', $this->context([
            'documents' => [1 => 'Study One', 2 => 'Study Two'],
        ]));

        $this->assertSame('deterministic', $result['method']);
    }

    public function test_llm_fallback_resolves_an_ordinal_reference_across_two_documents(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'Study Two methodology']]],
                'usage' => ['prompt_tokens' => 60, 'completion_tokens' => 5, 'total_tokens' => 65],
            ]),
        ]);

        config(['chat.condenser.llm_fallback' => true, 'services.openai.api_key' => 'test-key']);

        $result = $this->condenser->condense('compare the second one', $this->context([
            'documents' => [1 => 'Study One', 2 => 'Study Two'],
        ]));

        $this->assertSame('llm', $result['method']);
        $this->assertSame('Study Two methodology', $result['topic']);
    }

    public function test_a_failed_llm_call_degrades_to_the_deterministic_rewrite(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(null, 500)]);

        config(['chat.condenser.llm_fallback' => true, 'services.openai.api_key' => 'test-key']);

        $result = $this->condenser->condense('compare the second one', $this->context([
            'documents' => [1 => 'Study One', 2 => 'Study Two'],
        ]));

        $this->assertSame('deterministic', $result['method']);
        $this->assertStringContainsString('rice disease detection', $result['topic']);
    }
}

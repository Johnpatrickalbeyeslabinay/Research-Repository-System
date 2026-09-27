<?php

namespace Tests\Unit;

use App\Services\Chat\PromptBudget;
use App\Support\TokenEstimator;
use Tests\TestCase;

/**
 * The whole-prompt ceiling.
 *
 * What makes this worth testing is not that it trims — it is WHAT it refuses
 * to trim. The system message carries the grounding and prompt-injection rules,
 * and the current question is the thing being answered; sacrificing either to
 * save tokens trades correctness for money, which is never the right trade.
 */
class PromptBudgetTest extends TestCase
{
    private PromptBudget $budget;

    protected function setUp(): void
    {
        parent::setUp();

        $this->budget = new PromptBudget(new TokenEstimator);
    }

    private function messages(int $historyTurns, int $charsEach = 400): array
    {
        $messages = [['role' => 'system', 'content' => str_repeat('SYSTEM RULES. ', 100)]];

        for ($i = 0; $i < $historyTurns; $i++) {
            $messages[] = ['role' => 'user', 'content' => "old question {$i} ".str_repeat('x', $charsEach)];
            $messages[] = ['role' => 'assistant', 'content' => "old answer {$i} ".str_repeat('y', $charsEach)];
        }

        $messages[] = ['role' => 'user', 'content' => 'CURRENT QUESTION: what about tilapia?'];

        return $messages;
    }

    public function test_a_prompt_that_already_fits_is_returned_untouched(): void
    {
        $messages = $this->messages(1);

        $result = $this->budget->fit($messages, [], 100000);

        $this->assertSame($messages, $result['messages']);
        $this->assertSame(0, $result['trimmed']['history_messages']);
    }

    public function test_an_oversized_prompt_is_brought_under_the_ceiling(): void
    {
        $result = $this->budget->fit($this->messages(20), [], 1500);

        $this->assertLessThanOrEqual(1500, $result['estimated_tokens']);
        $this->assertGreaterThan(0, $result['trimmed']['history_messages']);
    }

    public function test_the_system_message_is_never_dropped(): void
    {
        $messages = $this->messages(20);
        $system = $messages[0]['content'];

        $result = $this->budget->fit($messages, [], 500);

        $this->assertSame('system', $result['messages'][0]['role']);
        $this->assertSame(
            $system,
            $result['messages'][0]['content'],
            'The system message carries the grounding and injection rules — trimming it trades safety for tokens.'
        );
    }

    public function test_the_current_question_is_never_dropped(): void
    {
        $result = $this->budget->fit($this->messages(20), [], 500);

        $last = end($result['messages']);

        $this->assertStringContainsString(
            'CURRENT QUESTION',
            $last['content'],
            'Answering a question you deliberately only half-read is worse than any cost.'
        );
    }

    /** Oldest first — a question from four turns ago rarely changes this answer. */
    public function test_the_oldest_history_is_dropped_first(): void
    {
        $result = $this->budget->fit($this->messages(10), [], 1200);

        $remaining = implode("\n", array_column($result['messages'], 'content'));

        $this->assertStringNotContainsString('old question 0', $remaining);
        $this->assertStringContainsString('CURRENT QUESTION', $remaining);
    }

    /**
     * Even an impossible ceiling must leave a usable prompt rather than an
     * empty or malformed one.
     */
    public function test_an_impossible_ceiling_still_leaves_system_and_question(): void
    {
        $result = $this->budget->fit($this->messages(20), [], 1);

        $this->assertCount(2, $result['messages']);
        $this->assertSame('system', $result['messages'][0]['role']);
        $this->assertStringContainsString('CURRENT QUESTION', $result['messages'][1]['content']);
    }

    /**
     * Evidence stays intact. Trimming it would require rebuilding the [n]
     * numbering to keep it consistent with evidence_order, and a mismatch there
     * is how a citation ends up pointing at the wrong study.
     */
    public function test_evidence_order_survives_trimming_unchanged(): void
    {
        $evidence = [
            ['id' => 1, 'stud_file_id' => 10],
            ['id' => 2, 'stud_file_id' => 11],
        ];

        $result = $this->budget->fit($this->messages(20), $evidence, 800);

        $this->assertSame($evidence, $result['evidence_order']);
    }

    public function test_a_prompt_with_no_history_is_left_alone(): void
    {
        $messages = [
            ['role' => 'system', 'content' => str_repeat('rules ', 500)],
            ['role' => 'user', 'content' => str_repeat('question ', 500)],
        ];

        $result = $this->budget->fit($messages, [], 10);

        $this->assertSame($messages, $result['messages']);
    }
}

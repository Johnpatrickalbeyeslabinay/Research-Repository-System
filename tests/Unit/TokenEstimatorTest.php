<?php

namespace Tests\Unit;

use App\Support\TokenEstimator;
use Tests\TestCase;

/**
 * Extends Tests\TestCase rather than PHPUnit's directly because the
 * estimator reads config('chat.estimator.*') — the same reason
 * QueryCondenserTest boots the framework while IntentDetectorTest does not.
 *
 * The point of these tests is not that the estimate is accurate — it cannot
 * be, without a real BPE tokenizer. It is that the estimate is never LOW,
 * because every prompt budget in config/chat.php is spent against it, and
 * an under-estimate overspends the budget silently.
 */
class TokenEstimatorTest extends TestCase
{
    private TokenEstimator $estimator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->estimator = new TokenEstimator;
    }

    public function test_an_empty_string_costs_nothing(): void
    {
        $this->assertSame(0, $this->estimator->estimate(''));
    }

    /**
     * The defect this class was written to fix, asserted directly.
     *
     * str_word_count() counts runs of [A-Za-z'-] only, so every digit and
     * symbol below scored zero: the old formula valued this line at ~7
     * tokens against a real cost near 28. Results-section chunks look
     * exactly like this, and they are what the full-text retrieval tier
     * exists to fetch — so config('chat.context.max_evidence_tokens')
     * was admitting far more than the 4000 it names.
     */
    public function test_numeric_dense_text_is_not_wildly_underestimated(): void
    {
        $text = 'Mean length was 12.4 ± 0.8 cm (n = 120; p < 0.05)';

        $oldFormula = (int) round(str_word_count($text) * 4 / 3);
        $new = $this->estimator->estimate($text);

        $this->assertLessThan(
            12,
            $oldFormula,
            'Guard on the premise: if str_word_count no longer undercounts this, the regression is gone and this test is obsolete.'
        );

        $this->assertGreaterThan(
            $oldFormula * 1.5,
            $new,
            'The estimator must price digits and symbols far closer to reality than the word-count formula did.'
        );
    }

    /**
     * The invariant that makes the estimator safe to budget against: it
     * takes max() of a character- and a word-based figure, so it can be
     * too high but never too low. A future "improvement" that averages the
     * two instead would break this.
     */
    public function test_it_is_never_lower_than_either_component_estimate(): void
    {
        $samples = [
            'short',
            'A perfectly ordinary sentence of prose about tilapia aquaculture.',
            'CO2;;;;;;;;----====++++%%%%',
            str_repeat('supercalifragilistic ', 40),
        ];

        foreach ($samples as $text) {
            $charBased = (int) ceil(mb_strlen($text) / 4.0);
            $wordBased = (int) ceil(str_word_count($text) * (4 / 3));

            $this->assertGreaterThanOrEqual(
                max($charBased, $wordBased),
                $this->estimator->estimate($text),
                "Estimate came in under a component figure for: {$text}"
            );
        }
    }

    public function test_longer_text_never_costs_less_than_shorter_text(): void
    {
        $previous = -1;

        foreach ([1, 10, 100, 1000, 5000] as $length) {
            $estimate = $this->estimator->estimate(str_repeat('a', $length));

            $this->assertGreaterThan($previous, $estimate);
            $previous = $estimate;
        }
    }

    public function test_message_framing_overhead_is_counted(): void
    {
        $messages = [
            ['role' => 'system', 'content' => 'You are a research assistant.'],
            ['role' => 'user', 'content' => 'What research is available?'],
        ];

        $contentOnly = $this->estimator->estimate($messages[0]['content'])
            + $this->estimator->estimate($messages[1]['content']);

        // The whole reason estimateMessages() exists: budgeting the sum of
        // the contents ignores the per-message role/separator tokens, and a
        // prompt assembled right at the ceiling would then exceed it.
        $this->assertGreaterThan($contentOnly, $this->estimator->estimateMessages($messages));
    }

    public function test_an_empty_message_list_still_costs_the_request_overhead(): void
    {
        $this->assertGreaterThan(0, $this->estimator->estimateMessages([]));
    }

    /**
     * A misconfigured 0 must not take the chatbot down with a division by
     * zero — the estimator degrades to the word-based figure instead.
     */
    public function test_a_zero_chars_per_token_config_does_not_divide_by_zero(): void
    {
        config(['chat.estimator.chars_per_token' => 0]);

        $this->assertGreaterThan(0, $this->estimator->estimate('some ordinary words here'));
    }
}

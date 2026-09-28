<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The estimator calibration report.
 *
 * Every token budget in config/chat.php is spent against an estimate, so the
 * question that matters is not "is it accurate" but "does it ever come in
 * UNDER the real cost" — an over-estimate wastes an evidence slot, an
 * under-estimate overspends a budget silently. These assert the command
 * separates those two cases rather than reporting a single average.
 */
class CalibrateTokenEstimatorTest extends TestCase
{
    use RefreshDatabase;

    private function turn(int $actual, int $estimated): void
    {
        $conversation = ChatConversation::create([
            'user_id' => User::factory()->student()->create()->id,
        ]);

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => 'An answer.',
            'prompt_tokens' => $actual,
            'retrieval_meta' => ['estimated_prompt_tokens' => $estimated],
        ]);
    }

    public function test_it_reports_nothing_to_compare_without_crashing(): void
    {
        $this->artisan('research:calibrate-token-estimator')
            ->expectsOutputToContain('No comparable turns')
            ->assertSuccessful();
    }

    /** A conservative estimator — every prediction above the real cost. */
    public function test_a_conservative_estimator_reports_zero_under_estimates(): void
    {
        $this->turn(actual: 800, estimated: 1000);
        $this->turn(actual: 900, estimated: 1000);

        $this->artisan('research:calibrate-token-estimator')
            ->expectsOutputToContain('Under-estimates: 0')
            ->assertSuccessful();
    }

    /** The failure that matters: the provider billed more than we predicted. */
    public function test_under_estimates_are_reported(): void
    {
        $this->turn(actual: 1400, estimated: 1000);
        $this->turn(actual: 900, estimated: 1000);

        $this->artisan('research:calibrate-token-estimator')
            ->expectsOutputToContain('overspent their budget silently')
            ->assertSuccessful();
    }

    /** A suggestion has to be actionable — an env line, not an adjective. */
    public function test_it_suggests_a_concrete_config_value_when_under_estimating(): void
    {
        $this->turn(actual: 2000, estimated: 1000);

        $this->artisan('research:calibrate-token-estimator')
            ->expectsOutputToContain('CHAT_ESTIMATOR_CHARS_PER_TOKEN=')
            ->assertSuccessful();
    }

    /** Rows without an estimate recorded are skipped, not treated as zero. */
    public function test_turns_without_a_recorded_estimate_are_ignored(): void
    {
        $conversation = ChatConversation::create([
            'user_id' => User::factory()->student()->create()->id,
        ]);

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => 'An older answer, from before estimates were recorded.',
            'prompt_tokens' => 1200,
        ]);

        $this->artisan('research:calibrate-token-estimator')
            ->expectsOutputToContain('No comparable turns')
            ->assertSuccessful();
    }
}

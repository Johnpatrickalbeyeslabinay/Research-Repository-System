<?php

namespace Tests\Feature;

use App\Models\AiUsageLog;
use App\Support\AiCost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The cost report, and the pricing behind it.
 *
 * This matters more than a reporting command usually would: it is the
 * instrument the whole token-optimisation effort is measured with. Before
 * the telemetry fixes, document embedding and metadata suggestion wrote no
 * rows at all and query_embedding wrote rows with null tokens — so this
 * report would have shown the single largest consumer of the API key as
 * costing nothing, and any claim of a saving would have been unfalsifiable.
 */
class AiCostReportTest extends TestCase
{
    use RefreshDatabase;

    private function log(array $attributes): void
    {
        AiUsageLog::record(array_merge([
            'request_id' => (string) Str::uuid(),
            'success' => true,
        ], $attributes));
    }

    /**
     * The regression guard for a silent data-loss bug: AiUsageLog::record()
     * mass-assigns, so a column missing from $fillable is dropped without
     * any error at all — the write "succeeds" and the number is simply gone.
     */
    public function test_cached_prompt_tokens_is_actually_persisted(): void
    {
        $this->log([
            'operation' => 'chat_answer',
            'model' => 'gpt-4o-mini',
            'prompt_tokens' => 3000,
            'cached_prompt_tokens' => 1024,
            'completion_tokens' => 500,
        ]);

        $this->assertSame(1024, AiUsageLog::first()->cached_prompt_tokens);
    }

    public function test_cost_is_discounted_for_provider_cached_prompt_tokens(): void
    {
        $full = AiCost::usd('gpt-4o-mini', 2000, 0, null);
        $halfCached = AiCost::usd('gpt-4o-mini', 2000, 0, 1000);

        $this->assertLessThan($full, $halfCached);
        $this->assertGreaterThan(0, $halfCached);
    }

    /**
     * Output tokens bill at four times input on gpt-4o-mini. That asymmetry
     * is the reason per-tier max_output_tokens ceilings matter more to the
     * bill than prompt trimming does, so it is asserted rather than assumed.
     */
    public function test_output_tokens_cost_more_than_input_tokens(): void
    {
        $this->assertGreaterThan(
            AiCost::usd('gpt-4o-mini', 1000, 0),
            AiCost::usd('gpt-4o-mini', 0, 1000),
        );
    }

    public function test_more_cached_tokens_than_prompt_tokens_never_yields_a_negative_cost(): void
    {
        $this->assertGreaterThanOrEqual(0.0, AiCost::usd('gpt-4o-mini', 100, 0, 99999));
    }

    public function test_an_unpriced_model_costs_zero_rather_than_throwing(): void
    {
        $this->assertSame(0.0, AiCost::usd('some-model-we-never-configured', 1000, 1000));
        $this->assertFalse(AiCost::isPriced('some-model-we-never-configured'));
    }

    /** Zero-token paths log model 'none'; that is not a missing price. */
    public function test_the_none_model_is_priced_at_zero_without_a_warning(): void
    {
        $this->assertSame(0.0, AiCost::usd('none', 0, 0));
    }

    public function test_the_report_totals_every_operation_including_indexing(): void
    {
        $this->log([
            'operation' => 'chat_answer',
            'model' => 'gpt-4o-mini',
            'prompt_tokens' => 3000,
            'completion_tokens' => 500,
            'latency_ms' => 1200,
        ]);

        // The operation that used to be invisible entirely.
        $this->log([
            'operation' => 'doc_embedding',
            'model' => 'text-embedding-3-small',
            'prompt_tokens' => 120000,
            'chunks_retrieved' => 310,
        ]);

        $this->artisan('research:ai-cost --days=7')
            ->expectsOutputToContain('chat_answer')
            ->expectsOutputToContain('doc_embedding')
            ->expectsOutputToContain('TOTAL')
            ->assertSuccessful();
    }

    /**
     * Silence and "no spend" look identical in a report, and they call for
     * very different responses — so operations with no rows are named.
     */
    public function test_operations_with_no_rows_are_named_explicitly(): void
    {
        $this->log([
            'operation' => 'chat_answer',
            'model' => 'gpt-4o-mini',
            'prompt_tokens' => 100,
            'completion_tokens' => 10,
        ]);

        // One assertion, not two: expectsOutputToContain consumes output
        // lines in order, so a second call would search only the lines
        // AFTER the one the first matched. 'doc_embedding' can reach the
        // output here only via the missing-operations list, since
        // chat_answer is the sole operation logged.
        $this->artisan('research:ai-cost --days=7')
            ->expectsOutputToContain('doc_embedding')
            ->assertSuccessful();
    }

    public function test_it_reports_an_empty_window_without_failing(): void
    {
        $this->artisan('research:ai-cost --days=7')->assertSuccessful();
    }

    public function test_it_rejects_an_unknown_grouping(): void
    {
        $this->artisan('research:ai-cost --by=nonsense')->assertFailed();
    }

    public function test_grouping_by_day_works_on_this_driver(): void
    {
        $this->log([
            'operation' => 'chat_answer',
            'model' => 'gpt-4o-mini',
            'prompt_tokens' => 100,
            'completion_tokens' => 10,
        ]);

        // groupExpression() branches on the driver because production is
        // Postgres and the test database is sqlite.
        $this->artisan('research:ai-cost --days=7 --by=day')
            ->expectsOutputToContain(now()->format('Y-m-d'))
            ->assertSuccessful();
    }
}

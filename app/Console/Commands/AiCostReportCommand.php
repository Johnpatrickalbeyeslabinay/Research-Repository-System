<?php

namespace App\Console\Commands;

use App\Models\AiUsageLog;
use App\Support\AiCost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * What the OpenAI key has actually been spent on.
 *
 * This is the production counterpart to `research:eval-chat --tokens`: the
 * eval harness measures a token change against a fixed fixture set, this
 * reports whether the change held against real traffic. Both read the same
 * ai_usage_logs rows and price them through App\Support\AiCost.
 *
 * It is also the check that the accounting is complete. Before the
 * telemetry work, 'doc_embedding' and 'metadata' were never written and
 * 'query_embedding' was written on every turn with null tokens — so this
 * report would have shown document indexing, by far the largest consumer
 * of the key, as costing nothing at all. If an operation you know runs is
 * missing or shows 0 tokens here, the instrumentation is wrong, not the
 * spend.
 */
class AiCostReportCommand extends Command
{
    protected $signature = 'research:ai-cost
        {--days=30 : How far back to report}
        {--by=operation : Group by operation|model|user|day}
        {--peso : Also show a PHP column using config(ai_pricing.php_per_usd)}';

    protected $description = 'Summarise OpenAI token usage and estimated cost from ai_usage_logs';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $by = (string) $this->option('by');

        $groups = [
            'operation' => 'operation',
            'model' => 'model',
            'user' => 'user_id',
            'day' => null, // handled separately — needs a date expression
        ];

        if (! array_key_exists($by, $groups)) {
            $this->error('--by must be one of: '.implode(', ', array_keys($groups)));

            return self::FAILURE;
        }

        $since = now()->subDays($days);

        // Aggregate in SQL rather than hydrating rows: a month of traffic is
        // thousands of rows and the report only needs sums. Cost cannot be
        // summed in SQL (rates live in config), so the per-model token
        // subtotals come back and are priced in PHP — which is why 'model'
        // is always part of the grouping even when it is not the axis.
        $rows = AiUsageLog::query()
            ->where('created_at', '>=', $since)
            ->selectRaw($this->groupExpression($by).' as bucket')
            ->selectRaw('model')
            ->selectRaw('COUNT(*) as calls')
            ->selectRaw('SUM(CASE WHEN success THEN 0 ELSE 1 END) as failures')
            ->selectRaw('COALESCE(SUM(prompt_tokens), 0) as prompt_tokens')
            ->selectRaw('COALESCE(SUM(cached_prompt_tokens), 0) as cached_prompt_tokens')
            ->selectRaw('COALESCE(SUM(completion_tokens), 0) as completion_tokens')
            ->selectRaw('ROUND(AVG(latency_ms)) as avg_latency_ms')
            ->groupBy('bucket', 'model')
            ->orderBy('bucket')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn("No ai_usage_logs rows in the last {$days} day(s).");
            $this->line('If the app has been used in that window, the instrumentation is broken —');
            $this->line('every OpenAI call is supposed to write one row here.');

            return self::SUCCESS;
        }

        $showPeso = (bool) $this->option('peso');
        $unpriced = [];
        $table = [];
        $totals = ['calls' => 0, 'failures' => 0, 'in' => 0, 'cached' => 0, 'out' => 0, 'usd' => 0.0];

        foreach ($rows as $row) {
            $usd = AiCost::usd(
                $row->model,
                (int) $row->prompt_tokens,
                (int) $row->completion_tokens,
                (int) $row->cached_prompt_tokens,
            );

            if (! AiCost::isPriced($row->model) && (int) $row->prompt_tokens > 0) {
                $unpriced[$row->model] = true;
            }

            $line = [
                (string) ($row->bucket ?? '—'),
                $row->model,
                number_format((int) $row->calls),
                (int) $row->failures > 0 ? (int) $row->failures : '',
                number_format((int) $row->prompt_tokens),
                (int) $row->cached_prompt_tokens > 0 ? number_format((int) $row->cached_prompt_tokens) : '',
                number_format((int) $row->completion_tokens),
                (int) $row->avg_latency_ms,
                '$'.number_format($usd, 4),
            ];

            if ($showPeso) {
                $line[] = '₱'.number_format($usd * (float) config('ai_pricing.php_per_usd', 58.0), 2);
            }

            $table[] = $line;

            $totals['calls'] += (int) $row->calls;
            $totals['failures'] += (int) $row->failures;
            $totals['in'] += (int) $row->prompt_tokens;
            $totals['cached'] += (int) $row->cached_prompt_tokens;
            $totals['out'] += (int) $row->completion_tokens;
            $totals['usd'] += $usd;
        }

        $headers = ['By '.$by, 'Model', 'Calls', 'Fail', 'In', 'Cached', 'Out', 'ms', 'USD'];

        if ($showPeso) {
            $headers[] = 'PHP';
        }

        $this->newLine();
        $this->line("OpenAI usage, last {$days} day(s) — since ".$since->toDateTimeString());
        $this->newLine();
        $this->table($headers, $table);

        $this->line(sprintf(
            'TOTAL  %s calls (%s failed)  ·  %s in / %s out tokens  ·  $%s',
            number_format($totals['calls']),
            number_format($totals['failures']),
            number_format($totals['in']),
            number_format($totals['out']),
            number_format($totals['usd'], 4),
        ));

        if ($showPeso) {
            $this->line('       ≈ ₱'.number_format(
                $totals['usd'] * (float) config('ai_pricing.php_per_usd', 58.0), 2
            ).' at the configured rate');
        }

        if ($totals['cached'] > 0) {
            $this->line(sprintf(
                '       %s of %s prompt tokens (%.1f%%) were served from the provider cache at the discounted rate.',
                number_format($totals['cached']),
                number_format($totals['in']),
                $totals['in'] > 0 ? $totals['cached'] / $totals['in'] * 100 : 0,
            ));
        }

        // Operations that are configured to run but wrote nothing are worth
        // naming explicitly: silence here reads identically to "no spend",
        // and those two states need very different responses.
        $seen = $rows->pluck('bucket')->map(fn ($b) => (string) $b)->unique();

        if ($by === 'operation') {
            $missing = array_values(array_diff(AiUsageLog::OPERATIONS, $seen->all()));

            if (! empty($missing)) {
                $this->newLine();
                $this->comment('No rows for: '.implode(', ', $missing));
                $this->line('Expected if the feature is unused in this window; a bug if it is not.');
            }
        }

        if (! empty($unpriced)) {
            $this->newLine();
            $this->warn('No price on file (cost shown as $0) for: '.implode(', ', array_keys($unpriced)));
            $this->line('Add these to config/ai_pricing.php so the totals above stop understating spend.');
        }

        return self::SUCCESS;
    }

    /**
     * The grouping expression. 'day' needs a driver-appropriate date cast —
     * the app runs on Postgres but the test database is sqlite, and
     * DATE(created_at) is the one form both understand.
     */
    private function groupExpression(string $by): string
    {
        return match ($by) {
            'operation' => 'operation',
            'model' => 'model',
            'user' => 'user_id',
            'day' => DB::getDriverName() === 'pgsql'
                ? "to_char(created_at, 'YYYY-MM-DD')"
                : 'DATE(created_at)',
        };
    }
}

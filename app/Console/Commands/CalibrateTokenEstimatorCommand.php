<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use Illuminate\Console\Command;

/**
 * Checks App\Support\TokenEstimator against what OpenAI actually charged.
 *
 * Every budget in config/chat.php is spent against an estimate — there is no
 * BPE tokenizer in this project — so the honest question is not "is the
 * estimator right" but "does it ever come in UNDER the real cost". An
 * over-estimate wastes one chunk of evidence; an under-estimate overspends a
 * budget silently, and at the extreme sends a prompt the model refuses.
 *
 * ai_usage_logs deliberately stores no text, so a prompt cannot be
 * reconstructed after the fact. Instead ChatController records what the
 * estimator predicted at send time
 * (chat_messages.retrieval_meta.estimated_prompt_tokens), which this compares
 * against the prompt_tokens the provider billed on the same row.
 *
 * Read the UNDER-ESTIMATE COUNT first. Mean error is reassuring and almost
 * beside the point.
 */
class CalibrateTokenEstimatorCommand extends Command
{
    protected $signature = 'research:calibrate-token-estimator
        {--days=30 : How far back to sample}
        {--limit=2000 : Cap on rows examined}';

    protected $description = 'Compare TokenEstimator predictions against the prompt_tokens OpenAI actually billed';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        $rows = ChatMessage::query()
            ->where('role', 'assistant')
            ->whereNotNull('prompt_tokens')
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('id')
            ->limit(max(1, (int) $this->option('limit')))
            ->get(['id', 'prompt_tokens', 'retrieval_meta']);

        $samples = [];

        foreach ($rows as $row) {
            $estimated = $row->retrieval_meta['estimated_prompt_tokens'] ?? null;

            if (! is_numeric($estimated) || (int) $estimated <= 0 || (int) $row->prompt_tokens <= 0) {
                continue;
            }

            $samples[] = [
                'actual' => (int) $row->prompt_tokens,
                'estimated' => (int) $estimated,
                'ratio' => (int) $row->prompt_tokens / (int) $estimated,
            ];
        }

        if (empty($samples)) {
            $this->warn("No comparable turns in the last {$days} day(s).");
            $this->line('Rows need both prompt_tokens and retrieval_meta.estimated_prompt_tokens.');
            $this->line('The latter has only been recorded since the estimator work landed, so a');
            $this->line('database with only older traffic will legitimately report nothing here.');

            return self::SUCCESS;
        }

        $ratios = array_column($samples, 'ratio');
        sort($ratios);

        $count = count($ratios);
        $mean = array_sum($ratios) / $count;
        $median = $ratios[intdiv($count, 2)];
        $p95 = $ratios[min($count - 1, (int) floor($count * 0.95))];

        // The number that matters. ratio > 1 means the provider charged MORE
        // than we predicted, i.e. the budget was overspent without anyone
        // noticing.
        $under = count(array_filter($ratios, fn (float $r) => $r > 1.0));

        $this->newLine();
        $this->line("TokenEstimator vs. billed prompt_tokens — {$count} turns, last {$days} day(s)");
        $this->newLine();

        $this->table(
            ['Metric', 'actual / estimated'],
            [
                ['mean', number_format($mean, 3)],
                ['median', number_format($median, 3)],
                ['p95', number_format($p95, 3)],
                ['min', number_format($ratios[0], 3)],
                ['max', number_format($ratios[$count - 1], 3)],
            ],
        );

        if ($under === 0) {
            $this->info('Under-estimates: 0 — the estimator never came in below the real cost.');
        } else {
            $this->error(sprintf(
                'Under-estimates: %d of %d (%.1f%%) — these turns overspent their budget silently.',
                $under,
                $count,
                $under / $count * 100,
            ));
        }

        $this->newLine();
        $this->suggest($p95);

        return self::SUCCESS;
    }

    /**
     * Suggests a chars_per_token that would push p95 to 1.0 or below.
     *
     * The estimator takes max(chars/chars_per_token, words*tokens_per_word), so
     * LOWERING chars_per_token raises the estimate. Deliberately advice rather
     * than an automatic write: a config change this load-bearing should be a
     * decision someone makes and commits, not a side effect of running a report.
     */
    private function suggest(float $p95): void
    {
        $current = (float) config('chat.estimator.chars_per_token', 4.0);

        if ($p95 <= 1.0) {
            $this->line(sprintf(
                'chars_per_token = %.2f is holding. No change suggested.',
                $current,
            ));

            // Being wildly conservative is not free either — it wastes evidence
            // slots on chunks that would have fit.
            if ($p95 < 0.6) {
                $this->line(sprintf(
                    'It may be too conservative, though: p95 of %.2f means the estimate runs '
                        .'~%.0f%% above the real cost, which costs evidence that would have fit. '
                        .'Consider raising chars_per_token toward %.2f.',
                    $p95,
                    (1 / $p95 - 1) * 100,
                    $current / max($p95, 0.1),
                ));
            }

            return;
        }

        $this->line(sprintf(
            'p95 is above 1.0, so the estimator under-predicts on the worst turns. '
                .'Lowering chars_per_token from %.2f to about %.2f would bring p95 to ~1.0:',
            $current,
            $current / $p95,
        ));
        $this->newLine();
        $this->line(sprintf('    CHAT_ESTIMATOR_CHARS_PER_TOKEN=%.2f', $current / $p95));
    }
}

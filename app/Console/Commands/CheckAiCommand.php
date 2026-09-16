<?php

namespace App\Console\Commands;

use App\Services\Ai\MetadataAnalysisService;
use App\Services\Research\EmbeddingService;
use Illuminate\Console\Command;

/**
 * One-shot smoke test for both OpenAI-backed services used by the research
 * submission flow. Exists so a rotated OPENAI_API_KEY can be verified in a
 * single command instead of submitting a real document and reading logs —
 * see app/Services/Ai/MetadataAnalysisService.php and
 * app/Services/Research/EmbeddingService.php.
 */
class CheckAiCommand extends Command
{
    protected $signature = 'research:check-ai';

    protected $description = 'Verify the OpenAI API key works for both the metadata (generative) and embedding calls used by research submission';

    public function handle(MetadataAnalysisService $metadataAnalysis, EmbeddingService $embedder): int
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            $this->error('OPENAI_API_KEY is not set.');

            return self::FAILURE;
        }

        $this->line('Checking metadata (generative) call — '.config('services.openai.metadata_model').'…');
        $metadataOk = $this->check(function () use ($metadataAnalysis) {
            $metadataAnalysis->analyze(
                'Renewable Energy Adoption in Rural Communities',
                'This study examines barriers to solar microgrid adoption in rural areas '
                    .'and proposes a community financing model to accelerate deployment.'
            );
        });

        $this->line('Checking embeddings call — '.config('services.openai.embedding_model').'…');
        $embeddingOk = $this->check(function () use ($embedder) {
            $embedder->embedBatch(['Research submission AI smoke test.']);
        });

        if ($metadataOk && $embeddingOk) {
            $this->info('Both OpenAI-backed services are reachable and authenticated.');

            return self::SUCCESS;
        }

        $this->error('At least one check failed — see above. Submission and indexing will still work without AI (they degrade gracefully), but suggestions/search will be unavailable until this is fixed.');

        return self::FAILURE;
    }

    private function check(callable $fn): bool
    {
        $start = microtime(true);

        try {
            $fn();
            $ms = round((microtime(true) - $start) * 1000);
            $this->info("  OK ({$ms}ms)");

            return true;
        } catch (\Throwable $e) {
            $this->error('  FAILED: '.$e->getMessage());

            return false;
        }
    }
}

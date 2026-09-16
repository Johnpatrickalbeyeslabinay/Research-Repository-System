<?php

namespace App\Console\Commands;

use App\Jobs\ProcessResearchDocument;
use App\Models\UserFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Finds every stud_file whose research index is missing, failed, or stale
 * (indexed under a since-changed embedding model/version — see
 * config/research.php's 'embedding.version') and (re)dispatches
 * App\Jobs\ProcessResearchDocument for it.
 *
 * Two jobs in one command:
 *   - Bootstrap: nothing is searchable until this has run at least once —
 *     submission dispatches indexing, but nothing drains the queue until a
 *     worker (`php artisan queue:work`, or RESEARCH_INDEX_SYNC=true) exists.
 *   - Ongoing: the permanent re-embed path whenever RESEARCH_EMBEDDING_VERSION
 *     or OPENAI_EMBEDDING_MODEL changes. content_hash-based reuse
 *     (ProcessResearchDocument::resolveEmbeddings) can't tell "current" apart
 *     from "stale" on its own — this command is what finds the stale rows.
 */
class BackfillResearchIndexCommand extends Command
{
    protected $signature = 'research:index-backfill
        {--id=* : Only these stud_file ids (repeatable)}
        {--force : Re-index every matching document, even if already ready and current}
        {--sync : Run inline via dispatchSync() instead of queuing — for bootstrapping with no queue worker running}
        {--limit= : Cap how many documents this run touches}';

    protected $description = 'Index or re-index research documents whose search index is missing, failed, or stale for the configured embedding model/version';

    public function handle(): int
    {
        $ids = $this->resolveTargetIds();

        if (empty($ids)) {
            $this->info('Nothing to index — every document is already ready and current for the configured embedding model/version.');

            return self::SUCCESS;
        }

        $sync = (bool) $this->option('sync');
        $this->info(sprintf(
            '%s %d document(s)%s.',
            $sync ? 'Indexing' : 'Queuing',
            count($ids),
            $sync ? ' inline' : ' onto the queue — run `php artisan queue:work` to process them'
        ));

        $rows = [];
        $bar = $this->output->createProgressBar(count($ids));
        $bar->start();

        foreach ($ids as $id) {
            $file = UserFile::find($id);

            if (! $file) {
                $bar->advance();

                continue;
            }

            $before = $file->search_index_status;

            if ($sync) {
                ProcessResearchDocument::dispatchSync($id);
                $file->refresh();
                $outcome = $file->search_index_status;
            } else {
                ProcessResearchDocument::dispatch($id);
                $outcome = 'queued';
            }

            $rows[] = [$id, Str::limit($file->title, 50), $before, $outcome];
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['ID', 'Title', 'Prior status', 'Outcome'], $rows);

        $failed = collect($rows)->where(3, 'failed')->count();

        if ($failed > 0) {
            $this->warn("{$failed} document(s) failed — see search_index_error on stud_file for details, or retry once the underlying issue (e.g. a rate limit) has cleared.");
        }

        return self::SUCCESS;
    }

    /** @return int[] */
    private function resolveTargetIds(): array
    {
        $explicitIds = array_map('intval', $this->option('id'));

        if (! empty($explicitIds)) {
            return $explicitIds;
        }

        $query = DB::table('stud_file as sf');

        if (! $this->option('force')) {
            $version = (int) config('research.embedding.version', 1);
            $model = (string) config('services.openai.embedding_model');

            $query->leftJoin('research_chunks as rc', 'rc.stud_file_id', '=', 'sf.id')
                ->where(function ($q) use ($version, $model) {
                    $q->where('sf.search_index_status', '<>', 'ready')
                        ->orWhereNull('rc.id')
                        ->orWhere('rc.embedding_version', '<>', $version)
                        ->orWhere('rc.embedding_model', '<>', $model);
                });
        }

        $ids = $query->distinct()->orderBy('sf.id')->pluck('sf.id')->all();

        $limit = $this->option('limit');

        return $limit !== null ? array_slice($ids, 0, (int) $limit) : $ids;
    }
}

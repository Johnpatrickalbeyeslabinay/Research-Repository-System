<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Models\UserFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DatabaseBackup extends Command
{
    /**
     * Command:
     * php artisan backup:database
     */
    protected $signature = 'backup:database';

    /**
     * Command description.
     */
    protected $description = 'Create an automatic scheduled PostgreSQL database backup';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        set_time_limit(0);

        $this->info('Starting scheduled database backup...');

        /*
        |--------------------------------------------------------------------------
        | 1. Prepare backup directory
        |--------------------------------------------------------------------------
        */

        $backupDirectory = storage_path('app/backups');

        File::ensureDirectoryExists($backupDirectory, 0755);

        $fileName = 'research_repository_'.now()->format('Y-m-d_H-i-s').'.sql';

        $filePath = $backupDirectory.DIRECTORY_SEPARATOR.$fileName;

        /*
        |--------------------------------------------------------------------------
        | 2. Get PostgreSQL configuration
        |--------------------------------------------------------------------------
        */

        $db = config('database.connections.pgsql');

        $env = array_merge(getenv(), [
            'PGPASSWORD' => $db['password'],
            'PGSSLMODE' => 'require',
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            'windir' => getenv('windir') ?: 'C:\\Windows',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 3. Run pg_dump
        |--------------------------------------------------------------------------
        */

        $restrictKey = bin2hex(random_bytes(16));

        $process = new Process([
            config('services.pg_dump_path', 'pg_dump'),
            '-h', $db['host'],
            '-p', (string) $db['port'],
            '-U', $db['username'],
            '-d', $db['database'],
            '-F', 'p',
            '--schema=public',

            // Exclude temporary/runtime Laravel data
            '--exclude-table-data=public.sessions',
            '--exclude-table-data=public.cache',
            '--exclude-table-data=public.cache_locks',
            '--exclude-table-data=public.jobs',
            '--exclude-table-data=public.failed_jobs',
            '--exclude-table-data=public.job_batches',

            '--no-owner',
            '--no-privileges',
            '--no-publications',
            '--no-subscriptions',
            '--restrict-key', $restrictKey,

            '-f', $filePath,
        ], null, $env);

        $process->setTimeout(600);
        $process->run();

        /*
        |--------------------------------------------------------------------------
        | 4. Check if pg_dump succeeded
        |--------------------------------------------------------------------------
        */

        if (! $process->isSuccessful()) {

            File::delete($filePath);

            Log::error(
                'Scheduled database backup failed: '.$process->getErrorOutput()
            );

            $this->error('Scheduled backup failed.');
            $this->error($process->getErrorOutput());

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Fix backups ID sequence
        |--------------------------------------------------------------------------
        |
        | Important after restoring PostgreSQL backups.
        | This prevents:
        |
        | duplicate key value violates unique constraint "backups_pkey"
        |
        */

        try {
            DB::statement("
                SELECT setval(
                    pg_get_serial_sequence('backups', 'id'),
                    COALESCE((SELECT MAX(id) FROM backups), 1),
                    true
                )
            ");
        } catch (\Throwable $e) {
            Log::warning(
                'Could not synchronize backups ID sequence: '.$e->getMessage()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Save to Backup History
        |--------------------------------------------------------------------------
        */

        try {

            Backup::create([
                'name' => 'Scheduled Backup',
                'type' => 'automatic',
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => File::size($filePath),
                'records_count' => UserFile::count(),
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Backup file created but backup history failed: '.$e->getMessage()
            );

            $this->error(
                'Backup file was created, but saving to Backup History failed.'
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Success
        |--------------------------------------------------------------------------
        */

        $this->info('Scheduled database backup created successfully.');
        $this->info('File: '.$fileName);

        return self::SUCCESS;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function index()
    {
        $backups = Backup::latest()->get();

        $lastBackup = Backup::latest('created_at')->first();

        $totalBackups = Backup::count();

        $now = now();

        // Daily backup at 11:00 PM
        $nextScheduled = $now->copy()->setTime(23, 0, 0);

        // Kapag lumampas na ng 11:00 PM,
        // next schedule ay bukas ng 11:00 PM
        if ($now->greaterThanOrEqualTo($nextScheduled)) {
            $nextScheduled->addDay();
        }

        return view('admin', compact(
            'backups',
            'lastBackup',
            'totalBackups',
            'nextScheduled'
        ));
    }

    public function store()
    {
        set_time_limit(0); // PHP's 120s limit is what killed it, not setTimeout()

        $backupDirectory = storage_path('app/backups');
        File::ensureDirectoryExists($backupDirectory, 0755);

        $fileName = 'research_repository_'.now()->format('Y-m-d_H-i-s').'.sql';
        $filePath = $backupDirectory.DIRECTORY_SEPARATOR.$fileName;

        $db = config('database.connections.pgsql');

        $env = array_merge(getenv(), [
            'PGPASSWORD' => $db['password'],
            'PGSSLMODE' => 'require',
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            'windir' => getenv('windir') ?: 'C:\\Windows',
        ]);

        $restrictKey = bin2hex(random_bytes(16));

        $process = new Process([
            config('services.pg_dump_path', 'pg_dump'),
            '-h', $db['host'],
            '-p', (string) $db['port'],
            '-U', $db['username'],
            '-d', $db['database'],
            '-F', 'p',
            '--schema=public',
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

        // ...rest stays the same

        if (! $process->isSuccessful()) {
            File::delete($filePath);
            Log::error($process->getErrorOutput());

            return back()->with('error', 'Backup failed: '.$process->getErrorOutput());
        }

        Backup::create([
            'name' => 'Manual Backup',
            'type' => 'manual',
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => File::size($filePath),
            'records_count' => \App\Models\UserFile::count(),
        ]);

        return back()->with('success', 'Database backup created successfully.');
    }

    // BackupController.php
    public function download(Backup $backup): BinaryFileResponse
    {
        abort_unless(File::exists($backup->file_path), 404, 'Backup file not found.');

        return response()->download($backup->file_path, $backup->file_name);
    }

    public function restore(Backup $backup)
    {
        set_time_limit(0);

        // Make sure the selected backup exists
        abort_unless(
            File::exists($backup->file_path),
            404,
            'Backup file not found.'
        );

        $db = config('database.connections.pgsql');

        $env = array_merge(getenv(), [
            'PGPASSWORD' => $db['password'],
            'PGSSLMODE' => 'require',
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            'windir' => getenv('windir') ?: 'C:\\Windows',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 1. Create safety backup before restore
        |--------------------------------------------------------------------------
        */

        $backupDirectory = storage_path('app/backups');

        File::ensureDirectoryExists($backupDirectory, 0755);

        $safetyFileName = 'before_restore_'.now()->format('Y-m-d_H-i-s').'.sql';
        $safetyFilePath = $backupDirectory.DIRECTORY_SEPARATOR.$safetyFileName;

        $restrictKey = bin2hex(random_bytes(16));

        $backupProcess = new Process([
            config('services.pg_dump_path', 'pg_dump'),
            '-h', $db['host'],
            '-p', (string) $db['port'],
            '-U', $db['username'],
            '-d', $db['database'],
            '-F', 'p',
            '--schema=public',
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
            '-f', $safetyFilePath,
        ], null, $env);

        $backupProcess->setTimeout(600);
        $backupProcess->run();

        if (! $backupProcess->isSuccessful()) {
            File::delete($safetyFilePath);

            Log::error(
                'Safety backup before restore failed: '.
                $backupProcess->getErrorOutput()
            );

            return back()->with(
                'error',
                'Restore cancelled. Safety backup could not be created.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Save safety backup to Backup History
        |--------------------------------------------------------------------------
        */

        $safetyBackup = Backup::create([
            'name' => 'Before Restore - '.$backup->name,
            'type' => 'manual',
            'file_name' => $safetyFileName,
            'file_path' => $safetyFilePath,
            'file_size' => File::size($safetyFilePath),
            'records_count' => \App\Models\UserFile::count(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | 3. Restore selected backup
        |--------------------------------------------------------------------------
        */

        $restoreProcess = new Process([
            config('services.psql_path', 'psql'),
            '-h', $db['host'],
            '-p', (string) $db['port'],
            '-U', $db['username'],
            '-d', $db['database'],
            '-f', $backup->file_path,
        ], null, $env);

        $restoreProcess->setTimeout(600);
        $restoreProcess->run();

        if (! $restoreProcess->isSuccessful()) {

            Log::error(
                'Database restore failed: '.
                $restoreProcess->getErrorOutput()
            );

            return back()->with(
                'error',
                'Restore failed. Your current database was saved as '.
                $safetyFileName.'.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Restore successful
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            'Database restored successfully from '.$backup->file_name.'. '.
            'A safety backup was also created: '.$safetyFileName
        );
    }
}

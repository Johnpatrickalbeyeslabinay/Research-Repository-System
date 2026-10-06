<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function index()
    {
        $backups = Backup::latest()->get();

        $lastBackup = Backup::latest('created_at')->first();

        $totalBackups = Backup::count();

        return view('admin', compact(
            'backups',
            'lastBackup',
            'totalBackups'
        ));
    }

    public function store()
    {
        // Backup folder
        $backupDirectory = storage_path('app/backups');

        if (! File::exists($backupDirectory)) {
            File::makeDirectory($backupDirectory, 0755, true);
        }

        // Backup filename
        $timestamp = now()->format('Y-m-d_H-i-s');

        $fileName = "research_repository_{$timestamp}.sql";

        $filePath = $backupDirectory.DIRECTORY_SEPARATOR.$fileName;

        // PostgreSQL settings from .env
        $host = '52.68.3.1';
        $port = config('database.connections.pgsql.port');
        $database = config('database.connections.pgsql.database');
        $username = config('database.connections.pgsql.username');

        // Run pg_dump
        $restrictKey = bin2hex(random_bytes(32));

        $process = new Process([
            'C:\\Program Files\\PostgreSQL\\18\\bin\\pg_dump.exe',
            '-h',
            '52.68.3.1',
            '-p',
            '5432',
            '-U',
            'postgres.hhwndqcgpkxmgsjfeewo',
            '-d',
            'postgres',
            '-F',
            'p',
            '--no-publications',
            '--no-subscriptions',
            '--restrict-key',
            'BackupRepository2026',
            '-f',
            $filePath,
        ]);
        // $process->setEnv($_SERVER);

        $process->setTimeout(300);

        $process->run();

        $process->run(function ($type, $buffer) {
            \Log::info($buffer);
        });
        if (! $process->isSuccessful()) {
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            return back()->with(
                'error',
                'Backup failed: '.$process->getErrorOutput()
            );
        }

        // Get backup size
        $fileSize = File::size($filePath);

        // Count research records
        $recordsCount = \App\Models\UserFile::count();

        // Save backup metadata
        Backup::create([
            'name' => 'Manual Backup',
            'type' => 'manual',
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'records_count' => $recordsCount,
        ]);

        return back()->with('success', 'Database backup created successfully.');
    }
}

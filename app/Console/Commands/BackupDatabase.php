<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Illuminate\Support\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database/data directory and rotate backups.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sourceDir = database_path('data');
        $backupDir = database_path('backups');
        $maxBackups = config('database.num_backups');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // rotate backups
        $backups = collect(File::glob($backupDir . '/backup-*.zip'))
            ->sortByDesc(function ($file) {
                return filemtime($file);
            });

        if ($backups->count() >= $maxBackups) {
            $backupsToDelete = $backups->slice($maxBackups - 1);
            foreach ($backupsToDelete as $backup) {
                $this->info("Deleting old backup: $backup");
                File::delete($backup);
            }
        }

        // new backup
        $timestamp = Carbon::now()->format('Ymd-His');
        $backupFileName = "data-{$timestamp}.zip";
        $backupFilePath = $backupDir . '/' . $backupFileName;

        $zip = new ZipArchive();

        if ($zip->open($backupFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = File::allFiles($sourceDir);
            if (empty($files)) {
                $this->info("Source directory '{$sourceDir}' is empty. No backup created.");
                $zip->close();
                File::delete($backupFilePath);
                return 0;
            }

            foreach ($files as $file) {
                $relativePath = substr($file->getPathname(), strlen($sourceDir) + 1);
                $zip->addFile($file->getPathname(), $relativePath);
            }

            $zip->close();
            $this->info("Backup created successfully: {$backupFilePath}");
        } else {
            $this->error("Failed to create backup zip file.");
            return 1;
        }

        return 0;
    }
}

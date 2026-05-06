<?php

namespace App\Console\Commands;

use App\Models\Content;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ListOrphanedContentFiles extends Command
{
    protected $signature = 'content:list-orphaned';

    protected $description = 'List all files in storage/public/content that are not referenced in the Content model';

    public function handle()
    {
        $this->info('Scanning for orphaned content files...');
        $this->newLine();

        // Get all files from storage/public/content
        $allFiles = collect(Storage::disk('public')->allFiles('content'));
        
        if ($allFiles->isEmpty()) {
            $this->warn('No files found in storage/public/content');
            return Command::SUCCESS;
        }

        $this->info("Total files in storage/public/content: {$allFiles->count()}");
        
        // Get all referenced file paths from Content model
        $referencedFiles = collect();
        
        // Get file_path values
        $contents = Content::all();
        
        foreach ($contents as $content) {
            // Add the main file_path
            if ($content->file_path) {
                $referencedFiles->push($content->file_path);
                $referencedFiles->push('content/' . $content->file_path);
            }
            
            // Add all audio_options values
            if ($content->audio_options) {
                $audioOptions = is_string($content->audio_options) 
                    ? json_decode($content->audio_options, true) 
                    : $content->audio_options;
                
                if (is_array($audioOptions)) {
                    foreach ($audioOptions as $option) {
                        if (is_string($option)) {
                            $referencedFiles->push($option);
                            $referencedFiles->push('content/' . $option);
                        }
                    }
                }
            }
        }
        
        $referencedFiles = $referencedFiles->unique();
        $this->info("Total referenced files: {$referencedFiles->count()}");
        $this->newLine();
        
        // Find orphaned files
        $orphanedFiles = $allFiles->filter(function ($file) use ($referencedFiles) {
            // Check both with and without 'content/' prefix
            $filename = basename($file);
            $fileWithoutPrefix = str_replace('content/', '', $file);
            
            return !$referencedFiles->contains($file) 
                && !$referencedFiles->contains($fileWithoutPrefix)
                && !$referencedFiles->contains($filename);
        });
        
        if ($orphanedFiles->isEmpty()) {
            $this->info('✓ No orphaned files found. All files are referenced!');
            return Command::SUCCESS;
        }
        
        $this->warn("Found {$orphanedFiles->count()} orphaned file(s):");
        $this->newLine();
        
        $this->table(
            ['File Path', 'Size'],
            $orphanedFiles->map(function ($file) {
                $size = Storage::disk('public')->size($file);
                return [
                    $file,
                    $this->formatBytes($size)
                ];
            })
        );
        
        return Command::SUCCESS;
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

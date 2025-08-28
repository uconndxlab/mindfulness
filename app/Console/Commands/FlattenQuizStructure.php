<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlattenQuizStructure extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'quiz:flatten-structure {--dry-run : Run without making changes} {--skip-json : Skip updating the JSON file}';

    /**
     * The description of the console command.
     */
    protected $description = 'Flatten quiz structure: move questions array directly to question_options';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $skipJson = $this->option('skip-json');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🚀 Starting quiz structure flattening...');
        
        if (!$skipJson) {
            $this->info('📄 Will also update quizzes.json file');
        }
        
        $quizzes = Quiz::all();
        $this->info("Found {$quizzes->count()} quizzes to flatten");
        
        $flattened = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($quizzes as $quiz) {
            try {
                $this->line("Processing Quiz #{$quiz->id} (Activity {$quiz->activity_id})...");
                
                $questionOptions = $quiz->question_options;
                
                // Check if it's already in the redundant nested format
                if (isset($questionOptions['questions']) && is_array($questionOptions['questions'])) {
                    $flattenedStructure = $questionOptions['questions'];
                    $questionCount = $questionOptions['question_count'] ?? count($flattenedStructure);
                    
                    if ($dryRun) {
                        $this->info("  ✅ Would flatten from nested to direct array ({$questionCount} questions)");
                        $this->showStructureComparison($questionOptions, $flattenedStructure);
                    } else {
                        $quiz->update([
                            'question_options' => $flattenedStructure,
                            'question_count' => $questionCount
                        ]);
                        $this->info("  ✅ Flattened to direct array ({$questionCount} questions)");
                    }
                    
                    $flattened++;
                    
                } else {
                    $this->comment("  ⏭️  Already in flat format or old format");
                    $skipped++;
                }
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error flattening Quiz #{$quiz->id}: " . $e->getMessage());
                Log::error("Quiz flattening error", [
                    'quiz_id' => $quiz->id,
                    'error' => $e->getMessage()
                ]);
                $errors++;
            }
        }
        
        // Update JSON file if not skipped and not dry run
        if (!$dryRun && !$skipJson && $flattened > 0) {
            $this->newLine();
            $this->info('📄 Updating quizzes.json file...');
            try {
                $this->updateQuizzesJsonFile();
                $this->info('  ✅ JSON file updated successfully');
            } catch (\Exception $e) {
                $this->error('  ❌ Error updating JSON file: ' . $e->getMessage());
                Log::error('JSON file update error', ['error' => $e->getMessage()]);
            }
        }
        
        $this->newLine();
        if ($dryRun) {
            $this->info("🔍 DRY RUN COMPLETE");
            $this->info("  • {$flattened} quizzes would be flattened");
            $this->info("  • {$skipped} quizzes already flat/old format");
            $this->info("  • {$errors} errors encountered");
            if (!$skipJson && $flattened > 0) {
                $this->info("  • JSON file would also be updated");
            }
            $this->info("Run without --dry-run to apply changes");
        } else {
            $this->info("✅ FLATTENING COMPLETE");
            $this->info("  • {$flattened} quizzes flattened successfully");
            $this->info("  • {$skipped} quizzes already flat/old format");
            $this->info("  • {$errors} errors encountered");
            if (!$skipJson && $flattened > 0) {
                $this->info("  • JSON file updated");
            }
        }
    }
    
    private function showStructureComparison($nested, $flat)
    {
        if ($this->getOutput()->isVerbose()) {
            $this->line("    📋 Before: question_options.questions[{$nested['question_count']}]");
            $this->line("    📋 After:  question_options[{count($flat)}] (direct array)");
            
            foreach ($flat as $question) {
                $optionCount = isset($question['options']) ? count($question['options']) : 'slider';
                $this->line("      • Q{$question['number']}: {$question['type']} ({$optionCount} options)");
            }
        }
    }
    
    private function updateQuizzesJsonFile()
    {
        $jsonPath = database_path('data/quizzes.json');
        
        if (!file_exists($jsonPath)) {
            throw new \Exception("JSON file not found at: {$jsonPath}");
        }
        
        // Create backup of original file
        $backupPath = $jsonPath . '.backup.' . date('Y-m-d_H-i-s');
        copy($jsonPath, $backupPath);
        $this->line("  📋 Backup created: " . basename($backupPath));
        
        // Read current JSON data
        $jsonContent = file_get_contents($jsonPath);
        $quizData = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in file: " . json_last_error_msg());
        }
        
        // Get all flattened quizzes from database
        $flattenedQuizzes = Quiz::all()->keyBy('id');
        
        // Update each quiz in the JSON array
        foreach ($quizData as &$jsonQuiz) {
            $quizId = $jsonQuiz['id'];
            
            if ($flattenedQuizzes->has($quizId)) {
                $dbQuiz = $flattenedQuizzes->get($quizId);
                
                // Update the JSON quiz with flattened structure
                $jsonQuiz['question_options'] = $dbQuiz->question_options;
                $jsonQuiz['question_count'] = $dbQuiz->question_count;
                
                $this->line("    • Updated Quiz #{$quizId} in JSON");
            }
        }
        
        // Write updated JSON back to file
        $updatedJson = json_encode($quizData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($updatedJson === false) {
            throw new \Exception("Failed to encode JSON: " . json_last_error_msg());
        }
        
        file_put_contents($jsonPath, $updatedJson);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateQuizStructure extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'quiz:migrate-structure {--dry-run : Run without making changes} {--skip-json : Skip updating the JSON file}';

    /**
     * The description of the console command.
     */
    protected $description = 'Migrate quiz structure from old nested format to clean array format';

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

        $this->info('🚀 Starting quiz structure migration...');
        
        if (!$skipJson) {
            $this->info('📄 Will also update quizzes.json file');
        }
        
        $quizzes = Quiz::all();
        $this->info("Found {$quizzes->count()} quizzes to migrate");
        
        $migrated = 0;
        $errors = 0;
        
        foreach ($quizzes as $quiz) {
            try {
                $this->line("Processing Quiz #{$quiz->id} (Activity {$quiz->activity_id})...");
                
                $oldStructure = $quiz->question_options;
                $newStructure = $this->convertQuizStructure($oldStructure);
                
                if ($dryRun) {
                    $this->info("  ✅ Would migrate {$newStructure['question_count']} questions");
                    $this->showStructureComparison($oldStructure, $newStructure);
                } else {
                    $quiz->update([
                        'question_options' => $newStructure
                    ]);
                    $this->info("  ✅ Migrated {$newStructure['question_count']} questions");
                }
                
                $migrated++;
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error migrating Quiz #{$quiz->id}: " . $e->getMessage());
                Log::error("Quiz migration error", [
                    'quiz_id' => $quiz->id,
                    'error' => $e->getMessage()
                ]);
                $errors++;
            }
        }
        
        // Update JSON file if not skipped and not dry run
        if (!$dryRun && !$skipJson && $migrated > 0) {
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
            $this->info("  • {$migrated} quizzes would be migrated");
            $this->info("  • {$errors} errors encountered");
            if (!$skipJson) {
                $this->info("  • JSON file would also be updated");
            }
            $this->info("Run without --dry-run to apply changes");
        } else {
            $this->info("✅ MIGRATION COMPLETE");
            $this->info("  • {$migrated} quizzes migrated successfully");
            $this->info("  • {$errors} errors encountered");
            if (!$skipJson && $migrated > 0) {
                $this->info("  • JSON file updated");
            }
        }
    }

    private function convertQuizStructure($oldStructure)
    {
        $questions = [];
        $questionCount = 0;
        
        // Convert from question_1, question_2... to array
        foreach ($oldStructure as $key => $questionData) {
            if (strpos($key, 'question_') === 0) {
                $questionNumber = (int) str_replace('question_', '', $key);
                $questionCount++;
                
                $newQuestion = [
                    'number' => $questionNumber,
                    'question' => $questionData['question'],
                    'type' => $questionData['type']
                ];
                
                // Convert options based on question type
                if ($questionData['type'] === 'slider') {
                    $sliderConfig = $questionData['options_feedback'][0] ?? [];
                    $newQuestion['slider_config'] = [
                        'min' => $sliderConfig['min'] ?? 0,
                        'max' => $sliderConfig['max'] ?? 100,
                        'step' => $sliderConfig['step'] ?? 1,
                        'default' => $sliderConfig['default'] ?? 50,
                        'pips' => $sliderConfig['pips'] ?? null
                    ];
                } else {
                    // Radio and checkbox questions
                    $options = [];
                    foreach ($questionData['options_feedback'] as $index => $optionData) {
                        $option = [
                            'id' => $index,
                            'text' => $optionData['option'],
                            'feedback' => $optionData['feedback'],
                            'audio_path' => $optionData['audio_path'],
                            'special_behavior' => $optionData['above'] ?? null,
                            'allow_other' => $optionData['other'] ?? false
                        ];
                        $options[] = $option;
                    }
                    $newQuestion['options'] = $options;
                }
                
                $questions[] = $newQuestion;
            }
        }
        
        // Sort questions by number to ensure correct order
        usort($questions, function($a, $b) {
            return $a['number'] <=> $b['number'];
        });
        
        return [
            'questions' => $questions,
            'question_count' => $questionCount
        ];
    }
    
    private function showStructureComparison($old, $new)
    {
        if ($this->getOutput()->isVerbose()) {
            $this->line("    📋 Old structure keys: " . implode(', ', array_keys($old)));
            $this->line("    📋 New structure: {$new['question_count']} questions in array format");
            
            foreach ($new['questions'] as $question) {
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
        
        // Get all migrated quizzes from database
        $migratedQuizzes = Quiz::all()->keyBy('id');
        
        // Update each quiz in the JSON array
        foreach ($quizData as &$jsonQuiz) {
            $quizId = $jsonQuiz['id'];
            
            if ($migratedQuizzes->has($quizId)) {
                $dbQuiz = $migratedQuizzes->get($quizId);
                $newStructure = $dbQuiz->question_options;
                
                // Update the JSON quiz with new structure
                $jsonQuiz['question_count'] = $newStructure['question_count'];
                $jsonQuiz['question_options'] = $newStructure;
                
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

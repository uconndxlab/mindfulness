<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuizAnswers;
use App\Models\Quiz;
use Illuminate\Support\Facades\Log;

class MigrateQuizAnswers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'quiz:migrate-answers {--dry-run : Run without making changes}';

    /**
     * The description of the console command.
     */
    protected $description = 'Migrate quiz answers from old key-based format to clean question-based format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('🚀 Starting quiz answers migration...');
        
        $quizAnswers = QuizAnswers::with('quiz')->get();
        $this->info("Found {$quizAnswers->count()} answer sets to migrate");
        
        $migrated = 0;
        $errors = 0;
        
        foreach ($quizAnswers as $answerSet) {
            try {
                $this->line("Processing answers for Quiz #{$answerSet->quiz_id}, User #{$answerSet->user_id}...");
                
                $oldAnswers = $answerSet->answers;
                
                // Debug information
                if ($this->getOutput()->isVerbose()) {
                    $this->line("    📋 Raw answers type: " . gettype($oldAnswers));
                    if (is_string($oldAnswers)) {
                        $this->line("    📋 Raw answers string: " . substr($oldAnswers, 0, 100) . "...");
                    }
                }
                
                $newAnswers = $this->convertAnswerStructure($oldAnswers, $answerSet->quiz);
                
                if ($dryRun) {
                    $this->info("  ✅ Would migrate " . count($newAnswers) . " question answers");
                    
                    // Ensure oldAnswers is decoded for comparison display
                    $decodedOldAnswers = is_string($oldAnswers) ? json_decode($oldAnswers, true) : $oldAnswers;
                    $this->showAnswerComparison($decodedOldAnswers, $newAnswers);
                } else {
                    $answerSet->update([
                        'answers' => $newAnswers
                    ]);
                    $this->info("  ✅ Migrated " . count($newAnswers) . " question answers");
                }
                
                $migrated++;
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error migrating answers for Quiz #{$answerSet->quiz_id}: " . $e->getMessage());
                Log::error("Quiz answers migration error", [
                    'quiz_id' => $answerSet->quiz_id,
                    'user_id' => $answerSet->user_id,
                    'error' => $e->getMessage()
                ]);
                $errors++;
            }
        }
        
        $this->newLine();
        if ($dryRun) {
            $this->info("🔍 DRY RUN COMPLETE");
            $this->info("  • {$migrated} answer sets would be migrated");
            $this->info("  • {$errors} errors encountered");
            $this->info("Run without --dry-run to apply changes");
        } else {
            $this->info("✅ MIGRATION COMPLETE");
            $this->info("  • {$migrated} answer sets migrated successfully");
            $this->info("  • {$errors} errors encountered");
        }
    }

    private function convertAnswerStructure($oldAnswers, $quiz)
    {
        $newAnswers = [];
        $otherAnswers = [];
        
        // Ensure we have an array to work with
        if (is_string($oldAnswers)) {
            $oldAnswers = json_decode($oldAnswers, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON in answers: " . json_last_error_msg());
            }
        }
        
        if (!is_array($oldAnswers)) {
            throw new \Exception("Answers must be an array, got: " . gettype($oldAnswers));
        }
        
        // First pass: collect regular answers and other answers
        foreach ($oldAnswers as $key => $value) {
            if (preg_match('/^answer_(\d+)$/', $key, $matches)) {
                $questionNumber = $matches[1];
                $questionType = $this->getQuestionType($quiz, $questionNumber);
                
                // All questions get an array structure
                if ($questionType === 'checkbox') {
                    // Checkbox: array of option objects
                    $selectedOptions = is_array($value) ? array_map('intval', $value) : [intval($value)];
                    $newAnswers[$questionNumber] = [];
                    foreach ($selectedOptions as $optionId) {
                        $newAnswers[$questionNumber][] = [$optionId => null]; // null = no other text yet
                    }
                } else if ($questionType === 'radio') {
                    // Radio: single option in array
                    $optionId = intval($value);
                    $newAnswers[$questionNumber] = [[$optionId => null]];
                } else if ($questionType === 'slider') {
                    // Slider: just the numeric value in array
                    $newAnswers[$questionNumber] = [intval($value)];
                }
                
            } else if (preg_match('/^other_answer_(\d+)_(\d+)$/', $key, $matches)) {
                $questionNumber = $matches[1];
                $optionNumber = $matches[2];
                
                if (!isset($otherAnswers[$questionNumber])) {
                    $otherAnswers[$questionNumber] = [];
                }
                $otherAnswers[$questionNumber][$optionNumber] = $value;
            }
        }
        
        // Second pass: add other text to the appropriate options
        foreach ($otherAnswers as $questionNumber => $otherTexts) {
            if (isset($newAnswers[$questionNumber])) {
                // Find the option objects and update their text
                foreach ($newAnswers[$questionNumber] as &$item) {
                    if (is_array($item)) {
                        foreach ($item as $optionId => $currentText) {
                            if (isset($otherTexts[$optionId])) {
                                $item[$optionId] = $otherTexts[$optionId];
                            }
                        }
                    }
                }
            }
        }
        
        return $newAnswers;
    }
    
    private function getQuestionType($quiz, $questionNumber)
    {
        // Check if quiz has new structure
        if (isset($quiz->question_options['questions'])) {
            // New structure
            foreach ($quiz->question_options['questions'] as $question) {
                if ($question['number'] == $questionNumber) {
                    return $question['type'];
                }
            }
        } else {
            // Old structure
            $questionKey = "question_{$questionNumber}";
            if (isset($quiz->question_options[$questionKey])) {
                return $quiz->question_options[$questionKey]['type'];
            }
        }
        
        // Default fallback
        return 'radio';
    }
    
    private function showAnswerComparison($old, $new)
    {
        if ($this->getOutput()->isVerbose()) {
            $this->line("    📋 Old format: " . count($old) . " keys (" . implode(', ', array_keys($old)) . ")");
            $this->line("    📋 New format: " . count($new) . " questions");
            
            foreach ($new as $questionNum => $answerArray) {
                $summary = "Q{$questionNum}: ";
                
                if (is_array($answerArray)) {
                    $parts = [];
                    foreach ($answerArray as $item) {
                        if (is_array($item)) {
                            // Option object like {"6": "other text"} or {"3": null}
                            foreach ($item as $optionId => $otherText) {
                                if ($otherText !== null) {
                                    $parts[] = "option {$optionId} (+ other text)";
                                } else {
                                    $parts[] = "option {$optionId}";
                                }
                            }
                        } else {
                            // Simple value (slider)
                            $parts[] = "value {$item}";
                        }
                    }
                    $summary .= "[" . implode(', ', $parts) . "]";
                } else {
                    $summary .= "value {$answerArray}";
                }
                
                $this->line("      • {$summary}");
            }
        }
    }
}

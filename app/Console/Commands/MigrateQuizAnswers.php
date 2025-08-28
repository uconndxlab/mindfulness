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
                $newAnswers = $this->convertAnswerStructure($oldAnswers, $answerSet->quiz);
                
                if ($dryRun) {
                    $this->info("  ✅ Would migrate " . count($newAnswers) . " question answers");
                    $this->showAnswerComparison($oldAnswers, $newAnswers);
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
            
            foreach ($new as $questionNum => $answer) {
                $summary = "Q{$questionNum}: {$answer['type']}";
                if (isset($answer['values'])) {
                    $summary .= " [" . implode(',', $answer['values']) . "]";
                } else if (isset($answer['value'])) {
                    $summary .= " = {$answer['value']}";
                }
                if (isset($answer['other_text'])) {
                    $summary .= " + other text";
                }
                $this->line("      • {$summary}");
            }
        }
    }
}

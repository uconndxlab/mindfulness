<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quiz;

class TestQuizMigration extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'quiz:test-migration {quiz_id?}';

    /**
     * The description of the console command.
     */
    protected $description = 'Test quiz migration by showing before/after structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $quizId = $this->argument('quiz_id') ?? 1;
        
        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            $this->error("Quiz #{$quizId} not found");
            return;
        }
        
        $this->info("🧪 Testing Quiz #{$quiz->id} Migration");
        $this->newLine();
        
        // Show current structure
        $this->info("📄 CURRENT STRUCTURE:");
        $this->line("Raw question_options:");
        $this->line(json_encode($quiz->question_options, JSON_PRETTY_PRINT));
        
        $this->newLine();
        
        // Show how it would look with new accessor
        $this->info("🆕 NEW STRUCTURE (via accessor):");
        $this->line("Questions array:");
        $this->line(json_encode($quiz->questions, JSON_PRETTY_PRINT));
        
        $this->newLine();
        $this->info("Question count: " . $quiz->question_count);
        
        // Test answer compatibility
        $this->newLine();
        $this->info("🔧 TESTING ANSWER COMPATIBILITY:");
        
        // Use the exact example from the user
        $sampleAnswers = [
            'answer_1' => ['6'],
            'answer_2' => ['3'],
            'other_answer_1_6' => 'something'
        ];
        
        // Additional test cases for different scenarios (using questions that exist in this quiz)
        $comprehensiveTest = [
            'answer_1' => ['1', '3', '6'],  // Multiple checkbox selections
            'answer_2' => ['2'],            // Single radio selection
            'other_answer_1_1' => 'Custom text for option 1',
            'other_answer_1_6' => 'Custom text for option 6'
        ];
        
        $this->line("Sample old answers (your example):");
        $this->line(json_encode($sampleAnswers, JSON_PRETTY_PRINT));
        
        $this->newLine();
        $this->line("Would convert to (new clean format):");
        $converted = $this->convertSampleAnswers($sampleAnswers, $quiz);
        $this->line(json_encode($converted, JSON_PRETTY_PRINT));
        
        $this->newLine();
        $this->info("🔍 EXPLANATION:");
        $this->explainConversion($converted);
        
        $this->newLine();
        $this->info("🧪 COMPREHENSIVE TEST:");
        $this->line("Complex scenario with multiple selections and other text:");
        $this->line(json_encode($comprehensiveTest, JSON_PRETTY_PRINT));
        
        $this->newLine();
        $this->line("Would convert to:");
        $comprehensiveConverted = $this->convertSampleAnswers($comprehensiveTest, $quiz);
        $this->line(json_encode($comprehensiveConverted, JSON_PRETTY_PRINT));
        
        $this->newLine();
        $this->explainConversion($comprehensiveConverted);
    }
    
    private function convertSampleAnswers($oldAnswers, $quiz)
    {
        $newAnswers = [];
        $otherAnswers = [];
        
        // First pass: collect regular answers and other answers
        foreach ($oldAnswers as $key => $value) {
            if (preg_match('/^answer_(\d+)$/', $key, $matches)) {
                $questionNumber = $matches[1];
                $question = $this->findQuestion($quiz, $questionNumber);
                $questionType = $question['type'] ?? 'radio';
                
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
                    $optionId = is_array($value) ? intval($value[0]) : intval($value);
                    $newAnswers[$questionNumber] = [[$optionId => null]];
                } else if ($questionType === 'slider') {
                    // Slider: just the numeric value in array
                    $sliderValue = is_array($value) ? intval($value[0]) : intval($value);
                    $newAnswers[$questionNumber] = [$sliderValue];
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
    
    private function findQuestion($quiz, $questionNumber)
    {
        foreach ($quiz->questions as $question) {
            if ($question['number'] == $questionNumber) {
                return $question;
            }
        }
        return ['type' => 'radio']; // fallback
    }
    
    private function explainConversion($converted)
    {
        foreach ($converted as $questionNum => $answerArray) {
            $this->line("  Question {$questionNum}:");
            
            if (is_array($answerArray)) {
                foreach ($answerArray as $index => $item) {
                    if (is_array($item)) {
                        // This is a checkbox/radio option with potential other text
                        foreach ($item as $optionId => $otherText) {
                            if ($otherText === null) {
                                $this->line("    • Option {$optionId}: selected (no other text)");
                            } else {
                                $this->line("    • Option {$optionId}: selected with other text = \"{$otherText}\"");
                            }
                        }
                    } else {
                        // This is a simple value (slider)
                        $this->line("    • Value: {$item}");
                    }
                }
            }
        }
        
        $this->newLine();
        $this->info("📋 COMPONENT PERSPECTIVE:");
        $this->line("  • QuizController just passes the array to each component");
        $this->line("  • Each component interprets its array based on its type");
        $this->line("  • No type information needed in the data structure");
        $this->line("  • Other text travels with the specific option that has it");
    }
}

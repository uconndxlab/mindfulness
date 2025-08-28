<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quiz;

class TestFlattenStructure extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'quiz:test-flatten {quiz_id=1}';

    /**
     * The description of the console command.
     */
    protected $description = 'Test what the flatten structure would look like';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $quizId = $this->argument('quiz_id');
        $quiz = Quiz::find($quizId);
        
        if (!$quiz) {
            $this->error("Quiz #{$quizId} not found");
            return;
        }
        
        $this->info("🧪 Testing Flatten Structure for Quiz #{$quizId}");
        $this->newLine();
        
        $questionOptions = $quiz->question_options;
        
        $this->info("📄 CURRENT STRUCTURE (Nested):");
        $this->line("Raw question_options:");
        $this->line(json_encode($questionOptions, JSON_PRETTY_PRINT));
        
        $this->newLine();
        $this->info("🆕 FLATTENED STRUCTURE:");
        
        if (isset($questionOptions['questions'])) {
            $flattenedStructure = $questionOptions['questions'];
            $this->line("Direct question_options array:");
            $this->line(json_encode($flattenedStructure, JSON_PRETTY_PRINT));
            
            $this->newLine();
            $this->info("📊 STRUCTURE COMPARISON:");
            $this->line("• Before: question_options.questions[{$questionOptions['question_count']}]");
            $this->line("• After:  question_options[" . count($flattenedStructure) . "] (direct array)");
            $this->line("• Removed: Unnecessary nesting");
            $this->line("• Result: Cleaner, more direct structure");
            
            $this->newLine();
            $this->info("🎯 BENEFITS:");
            $this->line("• No more redundant 'questions' wrapper");
            $this->line("• Direct access to questions array");
            $this->line("• Simpler model accessors");
            $this->line("• More intuitive structure");
            
        } else {
            $this->comment("Quiz is in old format or already flattened");
        }
    }
}

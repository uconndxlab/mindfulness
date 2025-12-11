<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuizAnswers;

class BackfillQuizAnswerTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quiz-answers:backfill-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill quiz answer types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Backfilling quiz answer types...');

        $quizAnswers = QuizAnswers::all();
        foreach ($quizAnswers as $quizAnswer) {
            $quizAnswer->reflection_type = $quizAnswer->quiz->type;
            $quizAnswer->save();
        }
    }
}

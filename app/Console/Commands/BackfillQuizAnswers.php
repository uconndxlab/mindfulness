<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuizAnswers;
use App\Models\Activity;
use App\Models\Module;

class BackfillQuizAnswers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quiz-answers:backfill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill quiz answers following migration that adds subject, reflection type, activity id, and average columns.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Backfilling quiz answers...');

        $quizAnswers = QuizAnswers::all()->load('quiz', 'activity', 'quiz.activity', 'quiz.subject');

        foreach ($quizAnswers as $quizAnswer) {
            $subject = $quizAnswer->quiz->subject;

            $quizAnswer->subject_id = $subject->id;
            $quizAnswer->subject_type = get_class($subject);
            $quizAnswer->activity_id = $quizAnswer->quiz->activity_id;

            $this->info($quizAnswer->quiz->activity->title);
            $title = $quizAnswer->quiz->activity->title;
            if ($title === 'Quick Check-In' || $title === 'Rate My Awareness') {
                $quizAnswer->reflection_type = $title === 'Quick Check-In' ? 'check_in' : 'rate_my_awareness';

                $count = 0;
                $total = 0;
                // only concerned with first answer set
                $options = $quizAnswer->quiz->question_options[0]['options'];
                foreach ($quizAnswer->answers[1] as $answer) {
                    // if this q is inverse, invert the score
                    $score = $options[$count]['inverse_score'] ? (100 - current($answer)) : current($answer);
                    $this->info(current($answer));
                    $this->info($options[$count]['inverse_score'] ? ('inverse - '.$score) : ('normal - '.$score));
                    $count++;
                    $total += $score;
                }
                $average = $total / count($quizAnswer->answers[1]);
                $this->info('Average: '.$average);
                $quizAnswer->average = $average;
            }
            $quizAnswer->save();
        }
    }
}

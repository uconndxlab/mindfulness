<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InsertSliderActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:insert-slider-reflections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert slider activities. One after every practice, and one at the end of the module.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Inserting slider activities...');

        DB::transaction(function () {
            Activity::withoutEvents(function () {
                Quiz::withoutEvents(function () {
                    $this->insertActivities();
                });
            });
        });

        $this->updateActivitiesJson();
        $this->updateQuizzesJson();

        $this->info('Slider activities inserted successfully.');
        return Command::SUCCESS;
    }

    private function insertActivities()
    {
        $this->info('Calculating module boundaries...');
        // get last activity of first module
        $lastActivityId = Activity::where('optional', false)
            ->where('day_id', 5)
            ->orderBy('order', 'desc')->first()->id;

        // update order of activities based on number of reflections added
        $this->info('Processing activities...');
        $activities = Activity::orderBy('order')->get();

        $currentOrder = 1;

        $progressBar = $this->output->createProgressBar($activities->count());
        $progressBar->start();

        foreach ($activities as $activity) {
            $activity->order = $currentOrder;
            $activity->save();
            $currentOrder++;

            // add practice reflection
            if ($activity->type === 'practice' && !$activity->optional) {
                $this->createPracticeReflection($activity, $currentOrder);
                $currentOrder++;
            }

            // add module reflection
            if ($activity->id === $lastActivityId) {
                $this->createModuleReflection($activity, $currentOrder);
                $currentOrder++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');
    }

    private function createPracticeReflection(Activity $activity, int $currentOrder): void
    {
        $sliderActivity = Activity::create([
            'day_id' => $activity->day_id,
            'title' => 'Reflection',
            'type' => 'reflection',
            'order' => $currentOrder,
            'skippable' => false,
            'time' => 1
        ]);

        Quiz::create([
            'activity_id' => $sliderActivity->id,
            'question_count' => 2,
            'question_options' => $this->getPracticeReflection()
        ]);
    }

    private function createModuleReflection(Activity $activity, int $currentOrder): void
    {
        $sliderActivity = Activity::create([
            'day_id' => $activity->day_id,
            'title' => 'Reflection',
            'type' => 'reflection',
            'order' => $currentOrder,
            'skippable' => false,
            'time' => 4
        ]);

        Quiz::create([
            'activity_id' => $sliderActivity->id,
            'question_count' => 7,
            'question_options' => $this->getModuleReflection($activity->day->module->order)
        ]);
    }

    private function updateActivitiesJson()
    {
        $this->info('Updating activities.json...');
        $activities = Activity::orderBy('order')->get([
            'id', 'day_id', 'title', 'type', 'order', 'completion_message', 'skippable', 'time', 'optional'
        ])->toArray();
        $jsonContent = json_encode($activities, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        File::put(database_path('data/activities.json'), $jsonContent);
    }

    private function updateQuizzesJson()
    {
        $this->info('Updating quizzes.json...');
        $quizzes = Quiz::orderBy('id')->get([
            'id', 'activity_id', 'question_count', 'question_options',
        ])->toArray();
        $jsonContent = json_encode($quizzes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        File::put(database_path('data/quizzes.json'), $jsonContent);
    }

    private function getSliderOptions()
    {
        return [
            [
                "min" => 0,
                "max" => 100,
                "step" => 1,
                "default" => 50,
                "pips" => [
                    "0" => "Strongly Disagree",
                    "25" => "Disagree",
                    "50" => "Neutral",
                    "75" => "Agree",
                    "100" => "Strongly Agree"
                ]
            ]
        ];
    }

    private function getPracticeReflection()
    {
        return [
            'question_1' => [
                'number' => 1,
                'question' => 'During practice, I attempted to return to my present-moment experience, whether unpleasant, pleasant, or neutral.',
                'type' => 'slider',
                "options_feedback" => $this->getSliderOptions()
            ],
            'question_2' => [
                'number' => 2,
                'question' => 'During practice, I was actively avoiding or “pushing away” certain experiences.',
                'type' => 'slider',
                "options_feedback" => $this->getSliderOptions()
            ],
        ];
    }

    private function getModuleReflection($order)
    {
        return [
            'question_1' => [
                'number' => 1,
                'question' => 'During my practices in Part '.$order.', I attempted to return to my present-moment experience, whether unpleasant, pleasant, or neutral.',
                'type' => 'slider',
                "options_feedback" => $this->getSliderOptions()
            ],
            'question_2' => [
                'number' => 2,
                'question' => 'During my practices in Part '.$order.', I attempted to return to each experience, not matter how difficult, with a sense that "It\'s OK to experience this."',
                'type' => 'slider',
                "options_feedback" => $this->getSliderOptions()
            ],
            'question_3' => [
                'number' => 3,
                'question' => 'During my practices in Part '.$order.', I attempted to feel each experience as bare sensations in the body (tension in throat, movement in belly, etc.).',
                'type' => 'slider',
                "options_feedback" => $this->getSliderOptions()
            ],
            'question_4' => [
                'number' => 4,
                'question' => 'During my practices in Part '.$order.', I was "zoning out" or falling asleep.',
                'type' => 'slider',
                "options_feedback" => $this->getSliderOptions()
            ],
            'question_5' => [
                'number' => 5,
                'question' => 'During my practices in Part '.$order.', I was struggling against having certain experiences (e.g., unpleasant thoughts, emotions, and/or bodily sensations).',
                'type' => 'slider',
                "options_feedback" => $this->getSliderOptions()
            ],
            'question_6' => [
                'number' => 6,
                'question' => 'During my practices in Part '.$order.', I was actively avoiding or “pushing away” certain experiences.',
                'type' => 'slider',
                "options_feedback" => $this->getSliderOptions()
            ],
            'question_7' => [
                'number' => 7,
                'question' => 'During my practices in Part '.$order.', I was actively tyring to fix or change certain experiences, in order to get to a "better place."',
                'type' => 'slider',
                "options_feedback" => $this->getSliderOptions()
            ],
        ];
    }
}

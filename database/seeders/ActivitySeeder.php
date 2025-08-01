<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use Illuminate\Support\Facades\File;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $json = File::get(database_path('data/activities.json'));
        $activities = json_decode($json);

        foreach ($activities as $activity) {
            Activity::updateOrCreate(
                ['id' => $activity->id],
                [
                    'day_id' => $activity->day_id,
                    'title' => $activity->title,
                    'type' => $activity->type,
                    'time' => $activity->time,
                    'completion_message' => $activity->completion_message ?? null,
                    'order' => $activity->order,
                    'skippable' => $activity->skippable ?? true,
                    'optional' => $activity->optional ?? false,
                ]
            );
        }
    }
}

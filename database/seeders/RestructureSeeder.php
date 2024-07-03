<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Week;
use App\Models\Day;
use App\Models\Activity;

class RestructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        //wipe tables


        DB::table('activities')->truncate();
        DB::table('days')->truncate();
        DB::table('weeks')->truncate();

        //basic population
        $oldWeek = null;
        for ($i = 1; $i <= 4; $i++) {
            $week = Week::create([
                'name' => 'Week '.$i,
                'order' => $i,
            ]);
            //logic for assigning next attribute
            if ($i == 1) {
                $oldWeek = $week;
            }
            if ($oldWeek) {
                $oldWeek->next = $week->id;
                $oldWeek->save();
                $oldWeek = $week;
            }

            $oldDay = null;
            for ($j = 1; $j <= 6; $j++) {
                if ($j == 6) {
                    $day = Day::create([
                        'week_id' => $week->id,
                        'name' => 'Optional',
                        'order' => 0,
                    ]);
                }
                else {
                    $day = Day::create([
                        'week_id' => $week->id,
                        'name' => 'Day '.$j,
                        'order' => $j,
                    ]);
                }

                if ($j == 1) {
                    $oldDay = $day;
                }
                if ($oldDay) {
                    $oldDay->next = $day->id;
                    $oldDay->save();
                    $oldDay = $day;
                }
            }
        }

        //populating activities
        $activities = json_decode(file_get_contents(database_path('data/activitiesExamples.json')), true);

        $order = 0;
        //skipping next to avoid constraint error
        foreach ($activities as $activity) {
            $exceptNext = collect($activity)->except(['next_fake'])->toArray();
            Activity::create($exceptNext);
            $order++;
        }

        //applying the next
        foreach ($activities as $activity) {
            $act = Activity::findOrFail($activity['id']);
            $act->next = $activity['next_fake'];
            $act->save();
        }
    
        Day::all()->each(function ($day) use (&$order) {
            //make a bunch of fake activities
            $start = count($day->activities) + 1;
            for ($i = $start; $i <= 5; $i++) {
                Activity::create([
                    'day_id' => $day->id,
                    'title' => 'Example ' . $i,
                    'type' => 'lesson',
                    'order' => $order++,
                ]);
            }

        });
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Module;
use App\Models\Day;
use App\Models\Activity;

class RestructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * @param bool $examples
     * @return void
     */

    public function run(bool $examples = false): void
    {
        //basic population
        $oldModule = null;
        $day_order = 1;
        for ($i = 1; $i <= 4; $i++) {
            $module = Module::create([
                'name' => 'Module '.$i,
                'order' => $i,
                'description' => 'This is a sample description for Module '.$i
            ]);
            //logic for assigning next attribute
            if ($i == 1) {
                $oldModule = $module;
            }
            if ($oldModule) {
                $oldModule->next = $module->id;
                $oldModule->save();
                $oldModule = $module;
            }

            $oldDay = null;
            for ($j = 1; $j <= 5; $j++) {
                $day = Day::create([
                    'module_id' => $module->id,
                    'name' => 'Day '.$j,
                    'order' => $day_order,
                    'description' => 'This is a sample description for Day '.$j.', in Module '.$i
                ]);

                if ($j == 1) {
                    $oldDay = $day;
                }
                if ($oldDay) {
                    $oldDay->next = $day->id;
                    $oldDay->save();
                    $oldDay = $day;
                }
                $day_order++;
            }
        }

        //populating activities
        $ftype = $examples ? "Examples.json" : ".json";
        $activities = json_decode(file_get_contents(database_path('data/activities'.$ftype)), true);

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

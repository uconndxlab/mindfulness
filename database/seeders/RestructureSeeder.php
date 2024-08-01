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
        $ftype = $examples ? "Examples.json" : ".json";

        //populate modules and days with data
        $modules = json_decode(file_get_contents(database_path('data/modules.json')), true);
        $days = json_decode(file_get_contents(database_path('data/days.json')), true);

        foreach ($modules as $module) {
            $extract = collect($module)->toArray();
            Module::create($extract);
        }

        $day_order = 1;
        foreach ($days as $day) {
            $extract = collect($day)->toArray();
            Day::create($extract);
            $day_order++;
        }

        //populating extra example days
        foreach (Module::all() as $module) {
            for ($j = $module->id == 1 ? 3 : 1; $j <= 5; $j++) {
                $day = Day::create([
                    'module_id' => $module->id,
                    'name' => 'Day '.$j,
                    'order' => $day_order++,
                    'description' => 'This is a sample description for Day '.$j.', in '.$module->name
                ]);
            }
        }
        

        //populating activities
        $activities = json_decode(file_get_contents(database_path('data/activities'.$ftype)), true);

        $order = 1;
        //skipping next to avoid constraint error
        foreach ($activities as $activity) {
            $exceptNext = collect($activity)->except(['next_fake'])->toArray();
            $_ = Activity::create($exceptNext);
            if (!$_->optional) {
                $order++;
            }
        }

        //applying the next
        $act = null;
        foreach ($activities as $activity) {
            $new = Activity::findOrFail($activity['id']);
            $new->next = $activity['next_fake'];
            $new->deleted = false;
            $new->save();
            if (!$new->optional) {
                $act = $new;
            }
        }
    
        $end = $examples ? 7 : 5;
        Day::all()->each(function ($day) use (&$order, &$act, &$end) {
            //make a bunch of fake activities
            $start = count($day->activities) + 1;
            for ($i = $start; $i <= $end; $i++) {
                $new = Activity::create([
                    'day_id' => $day->id,
                    'title' => 'Example ' . $i,
                    'type' => null,
                    'order' => $i > 5 ? $order-1 : $order++,
                    'optional' => $i > 5
                ]);

                if ($i <= 5) {
                    $act->next = $new->id;
                    $act->save();
                    $act = $new;
                }
            }

        });
}
}

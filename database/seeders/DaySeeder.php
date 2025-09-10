<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Day;
use Illuminate\Support\Facades\File;

class DaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $json = File::get(database_path('data/days.json'));
        $days = json_decode($json);

        foreach ($days as $day) {
            Day::updateOrCreate(
                ['id' => $day->id],
                [
                    'module_id' => $day->module_id,
                    'name' => $day->name,
                    'description' => $day->description,
                    'completion_message' => $day->completion_message,
                    'media_path' => $day->media_path,
                    'order' => $day->order,
                    'is_check_in' => $day->is_check_in,
                ]
            );
        }
    }
}

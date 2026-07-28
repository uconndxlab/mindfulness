<?php

namespace Database\Seeders;

use App\Models\Day;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DaySeeder extends Seeder
{
    /**
     * Run the database seeds.
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
                    'num_petals' => $day->num_petals ?? null,
                    'order' => $day->order,
                    'is_check_in' => $day->is_check_in,
                ]
            );
        }
    }
}

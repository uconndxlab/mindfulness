<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Lesson;
use App\Models\Module;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //modules have lessons - run module seeder first
        Module::all()->each(function ($module) {
            for ($i = 1; $i <= $module->lesson_count; $i++) {
                Lesson::create([
                    'title' => 'Compass ' . $i,
                    'module_id' => $module->id,
                    'lesson_number' => $i,
                ]);
            }
        });
    }
}

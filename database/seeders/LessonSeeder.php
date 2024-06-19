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
     * 
     * @param bool $examples
     * @return void
     */
    public function run(bool $examples = false): void
    {
        //data to seed depends on the param
        $ftype = $examples ? "Examples.json" : ".json";
        $lessons = json_decode(file_get_contents(database_path('data/lessons'.$ftype)), true);
        
        //lessons in the data
        $order = 0;
        foreach ($lessons as $lesson) {
            $lesson['order'] = $order++;
            Lesson::create($lesson);
            $module = Module::find($lesson['module_id']);
            $module->lesson_count++;
            $module->save();
        }

        //other empty lessons
        Module::all()->each(function ($module) use (&$order) {
            $limit = rand(2, 4);
            //take into account the real lessons
            $start = $module->lesson_count + 1;
            for ($i = $start; $i <= $limit; $i++) {
                Lesson::create([
                    'title' => 'Example ' . $i,
                    'module_id' => $module->id,
                    'lesson_number' => $i,
                    'order' => $order++,
                ]);
                $module->lesson_count += 1;
            }
            $module->save();
        });
    }
}

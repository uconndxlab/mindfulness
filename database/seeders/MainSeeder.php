<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\User;

class MainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //intention is to be the final seeder for the app
        
        //Modules
        for ($i = 1; $i <= 4; $i++) {
            Module::create([
                'name' => 'Module '.$i,
                'module_number' => $i,
                'lesson_count' => rand(5, 7),
            ]);
        }

        //lessons
        Module::all()->each(function ($module) {
            for ($i = 1; $i <= $module->lesson_count; $i++) {
                Lesson::create([
                    'title' => 'Compass ' . $i,
                    'module_id' => $module->id,
                    'lesson_number' => $i,
                ]);
            }
        });

        //single admin
        User::factory()->create([
            'id' => User::max('id') + 1,
            'name' => 'Admin-1',
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass$'),
            'role' => 'admin',
        ]);

    }
}

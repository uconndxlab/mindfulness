<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            ConfigSeeder::class,
            ModuleSeeder::class,
            DaySeeder::class,
            ActivitySeeder::class,
            ContentSeeder::class,
            QuizSeeder::class,
            JournalSeeder::class,
            FaqSeeder::class,
            EmailSeeder::class,
            TeacherSeeder::class,
        ]);
    }
}

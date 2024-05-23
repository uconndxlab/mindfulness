<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Note;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //disable foreign keys
        DB::statement('PRAGMA foreign_keys = OFF;');

        //truncate the tables
        DB::table('modules')->truncate();
        DB::table('lessons')->truncate();
        DB::table('content')->truncate();
        DB::table('quizzes')->truncate();

        //enable foreign key checks for SQLite
        DB::statement('PRAGMA foreign_keys = ON;');

        //call seeders
        $this->call([
            ModuleSeeder::class,
            LessonSeeder::class,
            ContentSeeder::class,
            QuizSeeder::class,
        ]);
    }
}

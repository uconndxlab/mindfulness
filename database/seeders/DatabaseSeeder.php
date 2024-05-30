<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     * 
     * @param bool $examples
     * @return void
     */
    public function run(bool $examples = false): void
    {
        //disable foreign keys
        DB::statement('PRAGMA foreign_keys = OFF;');

        //truncate the tables
        DB::table('modules')->truncate();
        DB::table('lessons')->truncate();
        DB::table('content')->truncate();
        DB::table('quizzes')->truncate();
        DB::table('favorites')->truncate();

        //enable foreign key checks for SQLite
        DB::statement('PRAGMA foreign_keys = ON;');

        //call seeders
        $this->call(ModuleSeeder::class);
        $this->call(LessonSeeder::class, false, compact('examples'));
        $this->call(ContentSeeder::class, false, compact('examples'));
        $this->call(QuizSeeder::class);
        //right now just resetting progress, not resetting user table
        $this->call(ResetUserProgress::class);
    }
}

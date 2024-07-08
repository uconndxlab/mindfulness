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
        DB::table('days')->truncate();
        DB::table('activities')->truncate();
        DB::table('content')->truncate();
        DB::table('favorites')->truncate();
        DB::table('quizzes')->truncate();

        //enable foreign key checks for SQLite
        DB::statement('PRAGMA foreign_keys = ON;');

        //call seeders
        $this->call(RestructureSeeder::class, false, compact('examples'));
        $this->call(ContentSeeder::class, false, compact('examples'));
        $this->call(QuizSeeder::class);
        //right now just resetting progress, not resetting user table
        $this->call(ResetUserProgress::class);
    }
}

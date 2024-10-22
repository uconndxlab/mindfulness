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
        DB::table('config')->truncate();
        DB::table('modules')->truncate();
        DB::table('days')->truncate();
        DB::table('activities')->truncate();
        DB::table('content')->truncate();
        DB::table('quizzes')->truncate();
        DB::table('journals')->truncate();
        DB::table('faqs')->truncate();
        DB::table('quiz_answers')->truncate();
        DB::table('notes')->truncate();
        DB::table('teachers')->truncate();
        //favs, session, progress wiped in ResetUserProgress

        //enable foreign key checks for SQLite
        DB::statement('PRAGMA foreign_keys = ON;');

        //call seeders
        $this->call(ConfigSeeder::class);                                   //config
        $this->call(RestructureSeeder::class, false, compact('examples'));  //modules, days, activities
        $this->call(ContentSeeder::class, false, compact('examples'));      //content
        $this->call(QuizSeeder::class, false, compact('examples'));         //quiz
        $this->call(JournalSeeder::class);                                  //jounral
        $this->call(FaqSeeder::class);                                      //faq
        $this->call(ResetUserProgress::class);                              //favorites, session vars, progress
        $this->call(TeacherSeeder::class);                                  //teachers
    }
}

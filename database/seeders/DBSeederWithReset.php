<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DBSeederWithReset extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);                              //config, modules, days, activities, content, quiz, journal, faq, teachers
        $this->call(ResetUserProgress::class);                              //favorites, session vars, progress
    }
}

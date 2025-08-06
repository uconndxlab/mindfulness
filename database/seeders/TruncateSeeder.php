<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateSeeder extends Seeder
{
    /**
     * Truncate all seeded tables in the correct order.
     *
     * @return void
     */
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF;');
    
        // Tables to truncate in order (child tables first, then parent tables)
        $tables = [
            'quiz_answers',      // Depends on quizzes
            'quizzes',           // Depends on activities
            'activities',        // Depends on days
            'content',           // Depends on activities
            'days',              // Depends on modules
            'modules',           // Parent table
            'journals',          // Independent
            'faqs',              // Independent
            'email_bodies',      // Email content tables
            'email_subjects',    // Email content tables
            'teachers',          // Independent
            'config',            // Independent
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            } else {
            }
        }

        DB::statement('PRAGMA foreign_keys=ON;');
    }
} 
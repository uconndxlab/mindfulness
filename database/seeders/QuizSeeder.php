<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Quiz;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quizzes = json_decode(file_get_contents(database_path('data/quizzes.json')), true);
        
        foreach ($quizzes as $quiz) {
            Quiz::create($quiz);
        }
    }
}

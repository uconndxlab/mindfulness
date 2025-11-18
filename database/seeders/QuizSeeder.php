<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Quiz;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * @param bool $examples
     * @return void
     */

    public function run(bool $examples = false): void
    {
        $ftype = $examples ? "Examples.json" : ".json";
        $quizzes = json_decode(file_get_contents(database_path('data/quizzes'.$ftype)), true);
        
        foreach ($quizzes as $quiz) {
            Quiz::updateOrCreate(
                ['id' => $quiz['id']],
                [
                    'activity_id' => $quiz['activity_id'],
                    'type' => $quiz['type'],
                    'question_count' => $quiz['question_count'],
                    'question_options' => $quiz['question_options'],
                    'subject_id' => $quiz['subject_id'],
                    'subject_type' => $quiz['subject_type'],
                ]
            );
        }
    }
}

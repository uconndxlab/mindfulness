<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Faq::create([
                'question' => '#'.$i.' This is an example of a question?',
                'answer' => 'This is the answer for question '.$i,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Email_Body;
use App\Models\Email_Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = json_decode(file_get_contents(database_path('data/mail/subjects.json')), true);
        foreach ($subjects as $item) {
            Email_Subject::create($item);
        }

        $bodies = json_decode(file_get_contents(database_path('data/mail/bodies.json')), true);
        foreach ($bodies as $item) {
            Email_Body::create($item);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = json_decode(file_get_contents(database_path('data/teachers.json')), true);
        foreach ($faqs as $item) {
            Teacher::updateOrCreate(
                ['id' => $item['id']],
                [
                    'name' => $item['name'],
                    'bio' => $item['bio'],
                    'profile_picture' => $item['profile_picture'],
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Journal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $journals = json_decode(file_get_contents(database_path('data/journals.json')), true);
        
        foreach ($journals as $item) {
            Journal::create($item);
        }
    }
}

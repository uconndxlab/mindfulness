<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            Module::create([
                'name' => 'Module '.$i,
                'module_number' => $i,
                'lesson_count' => rand(5, 7),
            ]);
        }
    }
}

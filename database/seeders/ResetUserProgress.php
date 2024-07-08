<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class ResetUserProgress extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->update(['progress_module' => 1, 'progress_day' => 1, 'progress_activity' => 1]);
    }
}

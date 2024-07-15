<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ResetUserProgress extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_activity')->truncate();

        foreach (User::all() as $user) {
            lockAll($user->id);
            unlockFirst($user->id);
        }
    }
}

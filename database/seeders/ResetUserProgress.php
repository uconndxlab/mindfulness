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
        DB::statement('PRAGMA foreign_keys = OFF;');

        DB::table('user_activity')->truncate();
        DB::table('user_day')->truncate();
        DB::table('user_module')->truncate();
        DB::table('sessions')->truncate();

        DB::statement('PRAGMA foreign_keys = ON;');

        foreach (User::all() as $user) {
            unlockFirst($user->id);
            $user->current_activity = 1;
            $user->last_day_completed_at = null;
            $user->last_day_name = null;
            $user->block_next_day_act = null;
            $user->save();
        }
    }
}

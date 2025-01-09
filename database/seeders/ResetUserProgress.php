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
        DB::table('favorites')->truncate();
        DB::table('sessions')->truncate();

        foreach (User::all() as $user) {
            // lockAll($user->id);
            unlockFirst($user->id);
            $user->current_activity = 1;
            $user->last_day_completed_at = null;
            $user->last_day_name = null;
            $user->block_next_day_act = null;
            $user->save();
        }
    }
}

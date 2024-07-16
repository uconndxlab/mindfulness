<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Facades\Session;

class ResetUserProgress extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_activity')->truncate();
        DB::table('sessions')->truncate();

        foreach (User::all() as $user) {
            lockAll($user->id);
            unlockFirst($user->id);
        }
    }
}

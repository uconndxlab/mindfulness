<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;

class FavoriteAllSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // get user from email
        $email = '';
        $user = User::where('email', $email)->first();

        // favorite all activities
        $activities = Activity::all();
        foreach ($activities as $activity) {
            DB::table('user_activity')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'activity_id' => $activity->id
                ],
                [
                    'favorited' => true,
                    'updated_at' => now()
                ]
            );
        }
    }
}

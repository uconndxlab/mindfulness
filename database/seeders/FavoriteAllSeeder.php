<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Activity;
use App\Models\Favorite;

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
            Favorite::updateOrCreate([
                'user_id' => $user->id,
                'activity_id' => $activity->id
            ]);
        }
    }
}

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
        foreach (User::all() as $user) {
            foreach (Activity::all() as $activity) {
                Favorite::updateOrCreate([
                    "user_id" => $user->id,
                    "activity_id" => $activity->id,
                ]);
            }
        }
    }
}

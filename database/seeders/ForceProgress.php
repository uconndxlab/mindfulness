<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ForceProgress extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $order = 25;
        $user_name = 'Zoro';
        // find user and list of activities up to point
        $user_id = User::where('name', $user_name)->first()->id;
        $activity_ids = Activity::where('order', '<=', $order)->pluck('order', 'id')->toArray();

        // update user progress to complete
        foreach ($activity_ids as $activity_id => $activity_order) {
            $status = $activity_order == $order ? 'unlocked' : 'completed';
            UserActivity::updateOrCreate([
                'user_id' => $user_id,
                'activity_id' => $activity_id
            ], [
                'status' => $status
            ]);
        }
    }
}

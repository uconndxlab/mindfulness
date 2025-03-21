<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Support\Facades\DB;
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
        $email = '';
        // find user and list of activities up to point
        $user_id = User::where('email', $email)->first()->id;
        $activity_ids = Activity::all()->pluck('order', 'id')->toArray();

        // clear users session
        DB::table('sessions')->where('user_id', $user_id)->delete();

        // update user progress to complete
        foreach ($activity_ids as $activity_id => $activity_order) {
            $status = $activity_order == $order ? 'unlocked' : ($activity_order < $order ? 'completed' : 'locked');
            UserActivity::updateOrCreate([
                'user_id' => $user_id,
                'activity_id' => $activity_id
            ], [
                'status' => $status
            ]);
        }
    }
}

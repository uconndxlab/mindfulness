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
        $order = 3;
        $email = 'zoro@op.com';
        
        // update user progress up to the point of order
        $user = User::where('email', $email)->first();
        $activities = Activity::where('order', '<=', $order)->get();

        foreach ($activities as $activity) {
            DB::table('user_activity')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'activity_id' => $activity->id
                ],
                [
                    'unlocked' => true,
                    'completed' => true,
                    'updated_at' => now()
                ]
            );

            // check day and module
            $day = $activity->day;
            $module = $day->module;

            if (!$day->canbeAccessedBy($user)) {
                DB::table('user_day')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'day_id' => $day->id
                    ],
                    [
                        'unlocked' => true,
                        'completed' => false,
                        'updated_at' => now()
                    ]
                );
            }

            if (!$module->canBeAccessedBy($user)) {
                DB::table('user_module')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'module_id' => $module->id
                    ],
                    [
                        'unlocked' => true,
                        'completed' => false,
                        'updated_at' => now()
                    ]
                );
            }
        }
    }
}

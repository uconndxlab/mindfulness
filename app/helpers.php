<?php

use App\Models\User;
use App\Models;
use App\Models\UserActivity;
use App\Models\Activity;
use App\Models\Day;
use App\Models\Module;
use Illuminate\Support\Facades\Session;

if (!function_exists('getConfig')) {
    function getConfig($key, $default = null)
    {
        $config = Models\Config::where('key', $key)->first();
        return $config ? $config->value : $default;
    }
}

if (!function_exists('updateConfig')) {
    function updateConfig($key, $value = null)
    {
        Models\Config::updateOrCreate([
            'key' => $key
        ],[
            'value' => $value
        ]);
    }
}

if (!function_exists('lastActivityInDay')) {
    function lastActivityInDay(Activity $activity, User $user)
    {
        $day = $activity->day;
        if ($activity->optional) {
            return false;
        }

        // non optional activities except current
        $requiredActivities = $day->activities()
            ->where('optional', false)
            ->where('id', '!=', $activity->id)
            ->get();
        
        // check if required activities are completed
        foreach ($requiredActivities as $requiredActivity) {
            if (!$user->isActivityCompleted($requiredActivity)) {
                // another activity not completed
                return false;
            }
        }
        
        return true;
    }
}

if (!function_exists('checkUserDay')) {
    function checkUserDay($user_id, $day_id)
    {
        // check all activities within the day to see if they are all completed
        $day = Day::findOrFail($day_id);
        $activity_ids = $day->activities->pluck('id')->toArray();
        $activity_progress = User::findOrFail($user_id)->load('progress_activities')->progress_activities;

        foreach ($activity_ids as $activity_id) {
            // if optional skip
            $activity = Activity::findOrFail($activity_id);
            if ($activity->optional) {
                continue;
            }
            // if not completed return false
            $status = $activity_progress->where('activity_id', $activity_id)->first()->status ?? 'locked';
            if ($status != 'completed') {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('unlockFirst')) {
    function unlockFirst($user_id)
    {
        // unlocking the first activity/day/module
        $user = User::findOrFail($user_id);
        $firstActId = getConfig('first_activity_id');
        $activity = Activity::findOrFail($firstActId);

        if ($activity) {
            DB::table('user_activity')->updateOrInsert(
                [
                    'user_id' => $user_id,
                    'activity_id' => $firstActId
                ],
                [
                    'unlocked' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            $day = $activity->day;
            if ($day) {
                DB::table('user_day')->updateOrInsert(
                    [
                        'user_id' => $user_id,
                        'day_id' => $day->id
                    ],
                    [
                        'unlocked' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                $module = $day->module;
                if ($module) {
                    DB::table('user_module')->updateOrInsert(
                        [
                            'user_id' => $user_id,
                            'module_id' => $module->id
                        ],
                        [
                            'unlocked' => true,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }
            }
        }
    }
}

if (!function_exists('lockAll')) {
    function lockAll($user_id)
    {
        foreach (Activity::all() as $activity) {
            UserActivity::updateOrCreate([
                "user_id" => $user_id,
                "activity_id" => $activity->id,
                "status" => 'locked'
            ]);
        }
    }
}

if (!function_exists('unlockAll')) {
    function unlockAll($user_id)
    {
        foreach (Activity::all() as $activity) {
            UserActivity::updateOrCreate([
                "user_id" => $user_id,
                "activity_id" => $activity->id,
            ],[
                "status" => 'unlocked'
            ]);
        }
    }
}

if (!function_exists('formatPhone')) {
    function formatPhone($phone)
    {
        $phone = preg_replace('/[^\d]/', '', $phone);
        if (strlen($phone) == 10) {
            return '('.substr($phone, 0, 3).') '.substr($phone, 3, 3).'-'.substr($phone, 6);
        }
        return $phone;
    }
}

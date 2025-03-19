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
    function lastActivityInDay(?Activity $activity, ?User $user)
    {
        if (!$activity || !$user) {
            return false;
        }
        
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
    function lockAll($user_id) {
        // lock all activities in pivot table
        foreach (Module::all() as $module) {
            DB::table('user_module')->updateOrInsert(
                [
                    'user_id' => $user_id,
                    'module_id' => $module->id
                ],
                [
                    'unlocked' => false,
                    'completed' => false,
                    'updated_at' => now()
                ]
            );

            foreach ($module->days as $day) {
                DB::table('user_day')->updateOrInsert(
                    [
                        'user_id' => $user_id,
                        'day_id' => $day->id
                    ],
                    [
                        'unlocked' => false,
                        'completed' => false,
                        'updated_at' => now()
                    ]
                );

                foreach ($day->activities as $activity) {
                    DB::table('user_activity')->updateOrInsert(
                        [
                            'user_id' => $user_id,
                            'activity_id' => $activity->id
                        ],
                        [
                            'unlocked' => false,
                            'completed' => false,
                            'updated_at' => now()
                        ]
                    );
                }
            }
        }
    }
}

if (!function_exists('unlockAll')) {
    function unlockAll($user_id)
    {
        foreach (Module::all() as $module) {
            DB::table('user_module')->updateOrInsert(
                [
                    'user_id' => $user_id,
                    'module_id' => $module->id
                ],
                [
                    'unlocked' => true,
                    'completed' => false,
                    'updated_at' => now()
                ]
            );

            foreach ($module->days as $day) {
                DB::table('user_day')->updateOrInsert(
                    [
                        'user_id' => $user_id,
                        'day_id' => $day->id
                    ],
                    [
                        'unlocked' => true,
                        'completed' => false,
                        'updated_at' => now()
                    ]
                );

                foreach ($day->activities as $activity) {
                    DB::table('user_activity')->updateOrInsert(
                        [
                            'user_id' => $user_id,
                            'activity_id' => $activity->id
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

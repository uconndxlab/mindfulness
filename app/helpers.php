<?php

use App\Models\Config;
use App\Models\UserActivity;
use App\Models\Activity;
use App\Models\Day;
use App\Models\Module;
use App\Models\User;

if (!function_exists('getConfig')) {
    function getConfig($key, $default = null)
    {
        $config = Config::where('key', $key)->first();
        return $config ? $config->value : $default;
    }
}

if (!function_exists('getModuleProgress')) {
    function getModuleProgress($user_id, $module_id)
    {
        $module = Module::findOrFail($module_id);
        $module_status = 'locked';
        $completed_count = 0;
        $total = $module->days->count();
        foreach ($module->days as $day) {
            $day_progress = getDayProgress($user_id, $day->id)['status'];

            if ($day_progress == 'unlocked') {
                $module_status = 'unlocked';
            }
            else if ($day_progress == 'completed') {
                $completed_count++;
            }
        }

        if ($completed_count == $total) {
            $module_status = 'completed';
        }

        return ['status' => $module_status, 'completed' => $completed_count, 'total' => $total];
    }
}

if (!function_exists('getDayProgress')) {
    function getDayProgress($user_id, $day_id)
    {
        $activity_progress = User::findOrFail($user_id)->load('progress_activities')->progress_activities;

        $day = Day::findOrFail($day_id);
        $day_status = 'locked';
        $completed_count = 0;
        $total = $day->activities->where('optional', false)->count();
        $activity_status = [];
        foreach ($day->activities as $activity) {
            $status = $activity_progress->where('activity_id', $activity->id)->first()->status;
            $activity_status[$activity->id] = $status;
            if ($status == 'unlocked') {
                $day_status = 'unlocked';
            }
            else if ($status == 'completed' && $activity->optional == false) {
                $completed_count++;
            }
        }

        if ($completed_count == $total) {
            $day_status = 'completed';
        }

        return ['status' => $day_status, 'completed' => $completed_count, 'total' => $total, 'activity_status' => $activity_status];
    }
}

if (!function_exists('unlockFirst')) {
    function unlockFirst($user_id)
    {
        UserActivity::updateOrCreate([
            "user_id" => $user_id,
            "activity_id" => getConfig('first_activity_id'),
        ],[
            "status" => 'unlocked'
        ]);
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

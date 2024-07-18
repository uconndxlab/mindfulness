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


if (!function_exists('getModuleProgress')) {
    function getModuleProgress($user_id, $module_ids)
    {
        //access stored progress
        $progress = Session::get('progress_modules', []);
        //check if any missing
        $missing_module_ids = array_diff($module_ids, array_keys($progress));

        if (!empty($missing_module_ids)) {
            //get day progress and missing modules
            $day_ids = Day::whereIn('module_id', $missing_module_ids)->pluck('id')->toArray();
            $day_progress = getDayProgress($user_id, $day_ids);
            $modules = Module::whereIn('id', $missing_module_ids)->with('days')->get();

            foreach ($modules as $module) {
                //set vars
                $module_status = 'locked';
                $completed_count = 0;
                $total = $module->days->count();

                //iterate through days of the module
                foreach ($module->days as $day) {
                    $status = $day_progress[$day->id]['status'] ?? 'locked';

                    if ($status == 'unlocked') {
                        $module_status = 'unlocked';
                    }
                    else if ($status == 'completed') {
                        $completed_count++;
                    }
                }

                if ($completed_count == $total) {
                    $module_status = 'completed';
                }

                $progress[$module->id] = ['status' => $module_status, 'completed' => $completed_count, 'total' => $total];
            }

            //put new modules in the session
            Session::put('progress_modules', $progress);
        }
        //return only the modules of interest
        return array_intersect_key($progress, array_flip($module_ids));
    }
}

if (!function_exists('getDayProgress')) {
    function getDayProgress($user_id, $day_ids)
    {
        //access stored progress
        $progress = Session::get('progress_days', []);
        //check if any missing
        $missing_day_ids = array_diff($day_ids, array_keys($progress));

        if (!empty($missing_day_ids)) {
            //get activity progress and missing days
            $activity_progress = User::findOrFail($user_id)->load('progress_activities')->progress_activities;
            $days = Day::whereIn('id', $missing_day_ids)->with('activities')->get();

            //show determines which one will open in the accordion
            $show = [];
            foreach ($days as $day) {
                //set vars
                $day_status = 'locked';
                $completed_count = 0;
                $total = $day->activities->where('optional', false)->count();
                $activity_status = [];

                //iterate through activities for that day
                foreach ($day->activities as $activity) {
                    $activity_info = $activity_progress->where('activity_id', $activity->id)->first();
                    $status = $activity_info->status ?? 'locked';
                    $activity_status[$activity->id] = $status;

                    if ($status == 'unlocked') {
                        $day_status = 'unlocked';
                        $show[$day->module->id] = $day->id;
                    }
                    else if ($status == 'completed' && $activity->optional == false) {
                        $completed_count++;
                    }
                }

                if ($completed_count == $total) {
                    $day_status = 'completed';
                }
                $progress[$day->id] = ['status' => $day_status, 'completed' => $completed_count, 'total' => $total, 'activity_status' => $activity_status, 'show' => false];
            }
            foreach ($show as $_ => $day_id) {
                $progress[$day_id]['show'] = true;
            }
            //put new days in the session
            Session::put('progress_days', $progress);
        }
        //return only the days of interest
        return array_intersect_key($progress, array_flip($day_ids));
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

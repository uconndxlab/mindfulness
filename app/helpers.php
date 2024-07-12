<?php

use App\Models\Config;
use App\Models\UserActivity;
use App\Models\UserDay;
use App\Models\UserModule;

if (!function_exists('getConfig')) {
    function getConfig($key, $default = null)
    {
        $config = Config::where('key', $key)->first();
        return $config ? $config->value : $default;
    }
}

if (!function_exists('unlockFirst')) {
    function unlockFirst($user_id)
    {
        UserActivity::updateOrCreate([
            "user_id" => $user_id,
            "activity_id" => getConfig('first_activity_id'),
            "status" => 'unlocked'
        ]);

        UserDay::updateOrCreate([
            "user_id" => $user_id,
            "day_id" => getConfig('first_day_id'),
            "status" => 'unlocked'
        ]);

        UserModule::updateOrCreate([
            "user_id" => $user_id,
            "module_id" => getConfig('first_module_id'),
            "status" => 'unlocked'
        ]);
    }
}

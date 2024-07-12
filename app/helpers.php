<?php

use App\Models\Config;

if (!function_exists('getConfig')) {
    function getConfig($key, $default = null)
    {
        $config = Config::where('key', $key)->first();
        return $config ? $config->value : $default;
    }
}

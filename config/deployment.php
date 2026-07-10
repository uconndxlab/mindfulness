<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Deployment Configuration
    |--------------------------------------------------------------------------
    |
    | These settings are for the file synchronization script.
    | Use DEPLOYMENT_SYNC_DRIVER=auto to select WinSCP on Windows and rsync elsewhere.
    |
    */

    'user' => env('DEPLOYMENT_USER'),
    'host' => env('DEPLOYMENT_HOST'),
    'driver' => env('DEPLOYMENT_SYNC_DRIVER', 'auto'),
    
    'winscp_path' => env('DEPLOYMENT_WINSCP_PATH'),
    'rsync_path' => env('DEPLOYMENT_RSYNC_PATH', 'rsync'),
    'key' => env('DEPLOYMENT_KEY_PATH'),

    'sync' => [
        'content' => [
            'local' => env('DEPLOYMENT_LOCAL_PATH') . '/storage/app/public',
            'remote' => env('DEPLOYMENT_PATH_CONTENT'),
        ],
        'data' => [
            'local' => env('DEPLOYMENT_LOCAL_PATH') . '/database/data',
            'remote' => env('DEPLOYMENT_PATH_DATA'),
        ],
    ],
];

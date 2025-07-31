<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Deployment Configuration
    |--------------------------------------------------------------------------
    |
    | These settings are for the WinSCP-based synchronization script.
    | All paths should be in the standard Windows format (e.g., "C:/...").
    |
    */

    'user' => env('DEPLOYMENT_USER'),
    'host' => env('DEPLOYMENT_HOST'),
    
    'winscp_path' => env('DEPLOYMENT_WINSCP_PATH'),
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

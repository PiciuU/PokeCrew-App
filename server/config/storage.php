<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Disk
    |--------------------------------------------------------------------------
    |
    | This value represents the default disk that will be used for file
    | storage operations unless a specific disk is specified. It should
    | match one of the keys defined in the "disks" configuration array.
    |
    */

    'disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Disk Configurations
    |--------------------------------------------------------------------------
    |
    | Here you can define different disk configurations for your application.
    | Each disk configuration consists of a root directory where files are
    | stored and an optional URL for generating file URLs or paths.
    |
    | Supported Options:
    | - root: The root directory for the disk.
    | - url: The URL for generating file URLs or paths (optional).
    | - log_exceptions: A flag indicating whether to log exceptions (optional, default false).
    | - throw: A flag indicating whether to throw exceptions on errors (optional, default false).
    |
    */

    'disks' => [

        'local' => [
            'root' => storage_path('app'),
            'log_exceptions' => true,
            'throw' => false,
        ],

        'public' => [
            'root' => env('OVERRIDED_STORAGE_PATH') ?: storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'log_exceptions' => true,
            'throw' => false,
        ],

        'log' => [
            'root' => storage_path('logs'),
        ]

    ],
];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" for your application.
    | The default guard is used when an authentication request is not specified.
    |
    */

    'defaults' => [
        'guard' => 'web'
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Define the authentication guards for your application.
    | Guards are the mechanisms that handle the actual authentication process.
    | Here, you can specify the driver for each guard, such as session or request.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session'
        ],
        'api' => [
            'driver' => 'request'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | User providers define how the users are retrieved out of your database
    | or other storage mechanisms used by this application to persist user data.
    |
    */

    'providers' => [
        'users' => [
            'model' => App\Models\User::class,
        ],
    ],
];
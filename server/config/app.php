<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This option allows you to define the name of your application. It is
    | commonly used for display purposes and can be configured in your
    | environment file (".env"). The default value is 'DreamFork'.
    |
    */

    'name' => env('APP_NAME', 'DreamFork'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    | Options: 'production', 'local'
    |
    | In the default .env configuration, the application is set to 'local'
    | mode for development purposes. However, for production deployments,
    | it's recommended to change the environment to 'production'.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    | Possible values: true, false
    |
    | By default, the debug mode is turned on (true) during development
    | and debugging, allowing you to see detailed error information.
    | However, it is strongly recommended to turn it off (false) in a
    | production environment to provide a more user-friendly error page
    | and to secure your logs from unauthorized access.
    |
    | In the default .env configuration, debug mode is enabled (true) for
    | development purposes, but you should disable it (false) for
    | production deployments.
    |
    */

    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This configuration defines the primary URL for your application. It plays a crucial role in
    | generating links and resource access throughout the application. The value of `APP_URL` is used
    | for creating full URLs, which is essential for generating links to various pages and resources.
    | By default, it is set to "http://localhost," which is used in a local environment for development
    | and testing. However, in a real production environment, you should set this URL to the publicly
    | accessible server where your application runs, e.g., "https://my-application.com."
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    | The 'timezone' option allows you to set the default timezone for your
    | application. It's important for accurate date and time handling. By
    | default, it is set to 'UTC', but you can change it to your desired
    | timezone, such as 'Europe/Warsaw'.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale sets the default language for your application.
    | Feel free to change this value to any supported locale in your project.
    |
    */

    'locale' => 'en',
];
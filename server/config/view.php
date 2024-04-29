<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | This section allows you to define the paths where your application will
    | search for view templates. By default, Dreamfork includes the main view
    | path for you. You can customize this array to add additional locations.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option specifies the directory where all the compiled Vision templates
    | will be stored. Typically, Dreamfork uses the storage directory for this.
    | However, you have the flexibility to modify this value if needed.
    |
    */

    'compiled' => env('VIEW_COMPILED_PATH', realpath(storage_path('framework/views'))),

];

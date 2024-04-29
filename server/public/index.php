<?php

use Framework\Http\Kernel;
use Framework\Http\Request;

define('DREAMFORK_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| This section checks whether the application is in maintenance mode,
| as set by the "down" command. If it is, the script loads the maintenance
| file to display pre-rendered content instead of starting the framework.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Dreamfork utilizes Composer's auto-generated class loader for its classes.
| This script includes the Composer-generated autoload.php file, which
| automatically loads the necessary classes for the application.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| After initializing the application, we handle incoming requests using
| the application's HTTP kernel. Then, we send the response back to the
| client's browser, enabling them to interact with our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
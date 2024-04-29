<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| This section is responsible for creating a new instance of the framework's application.
| The application instance acts as the central "glue" that ties together important components of the framework.
|
*/

$app = new Framework\Http\Application(dirname(__DIR__));

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. It separates the process of building the application's
| instances from the actual execution of the application and handling responses.
|
*/

return $app;
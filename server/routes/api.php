<?php

use Framework\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This section defines the API routes for the application. Any routes registered here will be loaded by the
| RouteServiceProvider and associated with the 'api' interface. These routes are used to handle API requests and define
| the behavior for various endpoints. Each route is defined using the Route class, allowing for the definition
| of routes with different HTTP methods, paths, and handlers, such as controller methods or closures.
|
*/

Route::post('/upload', [UploadController::class, 'upload']);
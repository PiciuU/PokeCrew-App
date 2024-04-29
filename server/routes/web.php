<?php

use Framework\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WEB Routes
|--------------------------------------------------------------------------
|
| This section defines the WEB routes for the application. Any routes registered here will be loaded by the
| RouteServiceProvider and associated with the 'web' interface. These routes are responsible for handling web-based
| requests and defining the behavior of various web pages and views. Each route is defined using the Route class,
| allowing for the definition of routes with different HTTP methods, paths, and handlers, such as controller
| methods or closures.
|
*/

Route::get('/', function() {
    return view('welcome');
});
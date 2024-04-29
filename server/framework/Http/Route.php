<?php

namespace Framework\Http;

use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

use Framework\Services\Auth\Middleware\Authenticate;

/**
 * Class Route
 *
 * The Route class provides a simplified interface for defining and managing routes in the application. It allows for
 * the creation of routes with various HTTP methods and different handlers, such as controller methods or closures.
 * Additionally, it supports route parameter validation.
 *
 * @package Framework\Http
 */
abstract class Route
{
    /**
     * An array to store defined routes.
     *
     * @var array
     */
    private static $routes;

    /**
     * The current interface being defined (e.g., 'api' or 'web').
     *
     * @var string
     */
    private static $interface;

    /**
     * The route prefix for the current interface (e.g., '/api' or '/').
     *
     * @var string
     */
    private static $prefix;

    /**
     * Get the defined routes.
     *
     * @return array
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * Create a collection of route groups based on the provided keys.
     *
     * @param array $keys
     */
    public static function collection($keys)
    {
        foreach($keys as $key) {
            self::$routes[$key] = new RouteCollection();
        }
    }

    /**
     * Define the interface and route prefix for subsequent route definitions.
     *
     * @param string $interface
     * @param string $prefix
     */
    public static function interface($interface, $prefix)
    {
        self::$interface = $interface;
        self::$prefix = $prefix;
    }

    /**
     * Prepare a Symfony route instance based on the provided path and handler.
     *
     * @param string $path
     * @param mixed $handler
     * @return \Symfony\Component\Routing\Route
     */
    private static function prepareRoute($path, $handler)
    {
        $route = new SymfonyRoute(
            self::$prefix.$path,
        );

        if ($handler instanceof \Closure) {
            $route->setDefault('_controller', $handler);
        } else {
            $route->setDefault('controller', $handler[0]);
            $route->setDefault('method', $handler[1]);
        }

        return $route;
    }

    /**
     * Define a GET route.
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     * @param array $params
     */
    public static function get($name, $path, $handler, $params = [])
    {
        $route = self::prepareRoute($path, $handler);

        $route->setRequirements($params)->setMethods('GET');

        self::$routes[self::$interface]->add($name, $route);
    }

    /**
     * Define a POST route.
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     */
    public static function post($name, $path, $handler)
    {
        $route = self::prepareRoute($path, $handler);

        $route->setMethods('POST');

        self::$routes[self::$interface]->add($name, $route);
    }

    /**
     * Define a PUT route.
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     */
    public static function put($name, $path, $controller)
    {
        $route = self::prepareRoute($path, $handler);

        $route->setMethods('PUT');

        self::$routes[self::$interface]->add($name, $route);
    }

    /**
     * Define a DELETE route.
     *
     * @param string $name
     * @param string $path
     * @param mixed $handler
     */
    public static function delete($name, $path, $controller)
    {
        $route = self::prepareRoute($path, $handler);

        $route->setMethods('DELETE');

        self::$routes[self::$interface]->add($name, $route);
    }

    /**
     * Define a fallback route to handle unmatched requests.
     *
     * This method defines a fallback route that will be executed when no other route matches the incoming request.
     * The fallback route can be used to display a custom response or perform specific actions when a route is not found.
     *
     * @param mixed $handler The handler for the fallback route, which can be a closure or a controller method.
     */
    public static function fallback($handler)
    {
        $route = self::prepareRoute('/{any}', $handler);

        self::$routes[self::$interface]->add('fallback', $route);
    }

    /**
     * Apply authentication middleware to the route.
     *
     * @param mixed $next The next middleware or handler.
     */
    public static function guard($next)
    {
        if (!Authenticate::handle($next)) return;

        $next();
    }

}
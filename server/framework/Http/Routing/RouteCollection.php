<?php

namespace Framework\Http\Routing;

use Framework\Support\Str;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

/**
 * Class RouteCollection
 *
 * The RouteCollection class manages a collection of routes in the framework.
 * It provides methods to add routes, convert routes to Symfony's RouteCollection, and retrieve routes by name.
 *
 * @package Framework\Http\Routing
 */
class RouteCollection
{
    /**
     * The array containing added routes.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Add a route to the collection.
     *
     * @param mixed $route The route to add.
     * @return mixed The added route.
     */
    public function add($route)
    {
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Convert routes to Symfony's RouteCollection.
     *
     * @return SymfonyRouteCollection The Symfony RouteCollection containing converted routes.
     */
    public function toSymfonyRouteCollection()
    {
        $symfonyRoutes = new SymfonyRouteCollection;

        foreach ($this->routes as $route) {
            $symfonyRoutes = $this->addToSymfonyRoutesCollection($symfonyRoutes, $route);
        }

        return $symfonyRoutes;
    }

    /**
     * Add a route to Symfony's RouteCollection.
     *
     * @param SymfonyRouteCollection $symfonyRoutes The Symfony RouteCollection to add the route to.
     * @param mixed $route The route to add.
     * @return SymfonyRouteCollection The updated Symfony RouteCollection.
     */
    protected function addToSymfonyRoutesCollection(SymfonyRouteCollection $symfonyRoutes, $route)
    {
        $route->name($this->generateRouteName());

        $symfonyRoutes->add($route->getName(), $route->toSymfonyRoute());

        return $symfonyRoutes;
    }

    /**
     * Generate a unique route name.
     *
     * @return string The generated route name.
     */
    protected function generateRouteName()
    {
        return 'generated::'.Str::random();
    }

    /**
     * Get all routes in the collection.
     *
     * @return array The array containing all added routes.
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Get a route by its name.
     *
     * @param string $name The name of the route to retrieve.
     * @return mixed|null The route with the specified name or null if not found.
     */
    public function getRouteByName($name)
    {
        return array_reduce($this->routes, function ($foundRoute, $route) use ($name) {
            return $foundRoute ?: ($route->getName() == $name ? $route : null);
        });
    }

}

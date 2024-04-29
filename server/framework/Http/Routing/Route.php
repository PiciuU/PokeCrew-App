<?php

namespace Framework\Http\Routing;

use Symfony\Component\Routing\Route as SymfonyRoute;

/**
 * Class Route
 *
 * The Route class represents a route in the framework, defining the URI, HTTP methods, action, name, and guarded status.
 * It provides methods for converting the route to Symfony's Route, setting a name, getting the name, and checking if it is guarded.
 *
 * @package Framework\Http\Routing
 */
class Route
{
    /**
     * The URI of the route.
     *
     * @var string
     */
    public $uri;

    /**
     * The HTTP methods allowed for the route.
     *
     * @var array
     */
    public $methods;

    /**
     * The action to be performed when the route is matched.
     *
     * @var mixed
     */
    public $action;

    /**
     * The name of the route.
     *
     * @var string|null
     */
    public $name;

    /**
     * The guarded status of the route.
     *
     * @var bool
     */
    public $guarded;

    /**
     * Create a new Route instance.
     *
     * @param mixed $methods The HTTP methods allowed for the route.
     * @param string $uri The URI of the route.
     * @param mixed $action The action to be performed when the route is matched.
     * @param bool $guarded The guarded status of the route. Defaults to false.
     */
    public function __construct($methods, $uri, $action, $guarded = false)
    {
        $this->uri = $uri;
        $this->methods = (array) $methods;
        $this->action = $action;
        $this->guarded = $guarded;

        if (in_array('GET', $this->methods) && !in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
        }
    }

    /**
     * Convert the route to Symfony's Route.
     *
     * @return SymfonyRoute The Symfony Route representing the current route.
     */
    public function toSymfonyRoute()
    {
        $route = new SymfonyRoute(
            $this->uri
        );

        if ($this->action instanceof \Closure) {
            $route->setDefault('_controller', $this->action);
        } else {
            $route->setDefault('controller', $this->action[0]);
            $route->setDefault('method', $this->action[1]);
        }

        $route->setMethods($this->methods);

        return $route;
    }

    /**
     * Set the name of the route.
     *
     * @param string $name The name to set for the route.
     * @return void
     */
    public function name($name)
    {
        $this->name = $name;
    }

    /**
     * Get the name of the route.
     *
     * @return string|null The name of the route or null if not set.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Check if the route is guarded.
     *
     * @return bool The guarded status of the route.
     */
    public function isGuarded()
    {
        return (bool) $this->guarded;
    }
}
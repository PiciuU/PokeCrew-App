<?php

namespace Framework\Http\Routing;

/**
 * Trait AddRoutes
 *
 * The AddRoutes trait provides methods for adding routes to the router and managing route groups.
 *
 * @package Framework\Http\Routing
 */
trait AddRoutes
{
    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * The guard status for the currently handled route.
     *
     * @var bool
     */
    protected $guard = false;

    /**
     * The prefix for the current route group.
     *
     * @var string
     */
    protected $groupPrefix = '';

    /**
     * All of the verbs supported by the router.
     *
     * @var array
     */
    public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * Register a GET route with the router.
     *
     * @param string $uri The URI of the route.
     * @param mixed $action The action to be performed when the route is matched.
     * @return mixed The result of adding the route.
     */
    public function get($uri, $action = null)
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * Register a POST route with the router.
     *
     * @param string $uri The URI of the route.
     * @param mixed $action The action to be performed when the route is matched.
     * @return mixed The result of adding the route.
     */
    public function post($uri, $action = null)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Register a PUT route with the router.
     *
     * @param string $uri The URI of the route.
     * @param mixed $action The action to be performed when the route is matched.
     * @return mixed The result of adding the route.
     */
    public function put($uri, $action = null)
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a PATCH route with the router.
     *
     * @param string $uri The URI of the route.
     * @param mixed $action The action to be performed when the route is matched.
     * @return mixed The result of adding the route.
     */
    public function patch($uri, $action = null)
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Register a DELETE route with the router.
     *
     * @param string $uri The URI of the route.
     * @param mixed $action The action to be performed when the route is matched.
     * @return mixed The result of adding the route.
     */
    public function delete($uri, $action = null)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Register a OPTIONS route with the router.
     *
     * @param string $uri The URI of the route.
     * @param mixed $action The action to be performed when the route is matched.
     * @return mixed The result of adding the route.
     */
    public function options($uri, $action = null)
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    /**
     * Register a route that matches all HTTP verbs.
     *
     * @param string $uri The URI of the route.
     * @param mixed $action The action to be performed when the route is matched.
     * @return mixed The result of adding the route.
     */
    public function any($uri, $action = null)
    {
        return $this->addRoute(self::$verbs, $uri, $action);
    }

    /**
     * Register a fallback route.
     *
     * @param mixed $action The action to be performed when the route is matched.
     * @return mixed The result of adding the route.
     */
    public function fallback($action)
    {
        return $this->any("/{any}", $action);
    }

    /**
     * Add a route to the router.
     *
     * @param array|string $methods The HTTP methods allowed for the route.
     * @param string $uri The URI of the route.
     * @param mixed $action The action to be performed when the route is matched.
     * @return mixed The result of adding the route.
     */
    public function addRoute($methods, $uri, $action)
    {
        $route = $this->newRoute(
            $methods, $this->prefix($uri), $action
        );

        return $this->routes->add($route);
    }

    /**
     * Create a new route instance.
     *
     * @param array|string $methods The HTTP methods allowed for the route.
     * @param string $uri The URI of the route.
     * @param mixed $action The action to be performed when the route is matched.
     * @return Route The new route instance.
     */
    protected function newRoute($methods, $uri, $action)
    {
        $route = new Route($methods, $uri, $action, $this->guard);

        return $route;
    }

    /**
     * Add the group prefix to the given URI.
     *
     * @param string $uri The URI to prefix.
     * @return string The URI with the group prefix added.
     */
    protected function prefix($uri)
    {
        return trim(trim($this->getLastGroupPrefix(), '/').'/'.$this->groupPrefix().trim($uri, '/'), '/') ?: '/';
    }

    /**
     * Get the last group prefix from the group stack.
     *
     * @return string The last group prefix.
     */
    public function getLastGroupPrefix()
    {
        if ($this->hasGroupStack()) {
            $last = end($this->groupStack);

            return $last['prefix'] ?? '';
        }

        return '';
    }

    /**
     * Update the route group stack with the provided group.
     *
     * @param array $group The route group information.
     * @return void
     */
    public function updateGroupStack($group)
    {
        $this->groupStack[] = $group;
    }

    /**
     * Check if the route group stack is not empty.
     *
     * @return bool True if the route group stack is not empty; otherwise, false.
     */
    public function hasGroupStack()
    {
        return !empty($this->groupStack);
    }

    /**
     * Get the prefix for the current route group.
     *
     * @return string The prefix for the current route group.
     */
    protected function groupPrefix()
    {
        return $this->groupPrefix ? trim($this->groupPrefix, '/').'/' : '';
    }

    /**
     * Add a route group to the router.
     *
     * @param string $prefix The prefix for the currently handled routes.
     * @param callable $next The callback function to execute within the route group.
     * @return void
     */
    public function group($prefix, $next)
    {
        $this->groupPrefix = $prefix;
        $next();
        $this->groupPrefix = '';
    }

    /**
     * Set the guard status for the currently handled routes.
     *
     * @param callable $next The callback function to execute with the guard status set to true.
     * @return void
     */
    public function guard($next)
    {
        $this->guard = true;
        $next();
        $this->guard = false;
    }

}
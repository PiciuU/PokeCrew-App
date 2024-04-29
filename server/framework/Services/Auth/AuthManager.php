<?php

namespace Framework\Services\Auth;

use InvalidArgumentException;

/**
 * Class AuthManager
 *
 * The AuthManager class is responsible for managing and resolving authentication guards in the framework.
 * It provides methods to create and retrieve different authentication guards based on the specified configuration.
 *
 * @package Framework\Services\Auth
 */
class AuthManager
{
    /**
     * The array of resolved guards.
     *
     * @var array
     */
    protected $guards = [];

    /**
     * Get a guard instance by name.
     *
     * @param  string|null  $name The name of the guard to retrieve.
     * @return mixed The resolved guard instance.
     *
     * @throws InvalidArgumentException If the specified guard is not defined.
     */
    public function guard($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }

    /**
     * Resolve the given guard instance by name.
     *
     * @param  string  $name The name of the guard to resolve.
     * @return mixed The resolved guard instance.
     *
     * @throws InvalidArgumentException If the specified guard is not defined or the driver method is not found.
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
        }

        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($name, $config);
        }

        throw new InvalidArgumentException(
            "Auth driver [{$config['driver']}] for guard [{$name}] is not defined."
        );
    }

    /**
     * Create a session driver instance.
     *
     * @param  string  $name The name of the guard.
     * @param  array  $config The configuration for the guard.
     * @return \Framework\Services\Auth\SessionGuard The session guard instance.
     */
    public function createSessionDriver($name, $config)
    {
        $guard = new SessionGuard();

        return $guard;
    }

    /**
     * Create a request driver instance.
     *
     * @param  string  $name The name of the guard.
     * @param  array  $config The configuration for the guard.
     * @return \Framework\Services\Auth\RequestGuard The request guard instance.
     */
    public function createRequestDriver($name, $config)
    {
        $guard = new RequestGuard();

        return $guard;
    }

    /**
     * Get the configuration for a guard.
     *
     * @param  string  $name The name of the guard.
     * @return array|null The configuration array for the guard.
     */
    protected function getConfig($name)
    {
        return config("auth.guards.{$name}");
    }

    /**
     * Set the default guard driver the factory should serve.
     *
     * @param  string  $name
     * @return void
     */
    public function shouldUse($name)
    {
        $name = $name ?: $this->getDefaultDriver();

        $this->setDefaultDriver($name);
    }

    /**
     * Set the default authentication driver name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        config()->set('auth.defaults.guard', $name);
    }


    /**
     * Get the default guard driver.
     *
     * @return string The default guard driver name.
     */
    public function getDefaultDriver()
    {
        return config('auth.defaults.guard');
    }

    /**
     * Dynamically call the default guard instance.
     *
     * @param  string  $method The method to call.
     * @param  array  $parameters The method parameters.
     * @return mixed The result of the method call on the default guard.
     */
    public function __call($method, $parameters)
    {
        return $this->guard()->{$method}(...$parameters);
    }

}
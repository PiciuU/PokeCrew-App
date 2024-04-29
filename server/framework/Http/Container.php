<?php

namespace Framework\Http;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Class Container
 *
 * This class represents a basic dependency injection container that can be used
 * for managing and resolving dependencies in an application. It allows for the
 * registration of bindings, shared instances, and type aliases.
 *
 * @package Framework\Http
 */
class Container {
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * The container's bindings.
     *
     * @var array[]
     */
    public $bindings = [];

    /**
     * The container's shared instances.
     *
     * @var object[]
     */
    public $instances = [];

    /**
     * The registered type aliases.
     *
     * @var string[]
     */
    public $aliases = [];

    /**
     * Set the shared instance of the container.
     */
    public static function setInstance($container = null)
    {
        return static::$instance = $container;
    }

    /**
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $abstract The abstract identifier.
     * @param  mixed  $instance  The concrete instance to be shared.
     * @return mixed
     */
    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;

        return $instance;
    }

    /**
     * Register an alias for a binding.
     *
     * @param  string  $alias   The alias name.
     * @param  string  $abstract   The abstract identifier.
     * @return void
     */
    public function alias($alias, $abstract)
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * Register a shared binding in the container.
     *
     * @param  string  $abstract   The abstract identifier.
     * @param  \Closure|string|null  $concrete   The concrete implementation or class name (optional).
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register a binding with the container.
     *
     * @param  string  $abstract   The abstract identifier.
     * @param  \Closure|string|null  $concrete   The concrete implementation or class name (optional).
     * @param  bool  $shared   Indicates whether the binding should be shared (default is false).
     * @return void
     *
     * @throws \TypeError
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        // If the factory is not a Closure, it means it is just a class name which is
        // bound into this container to the abstract type and we will just wrap it
        // up inside its own Closure to give us more convenience when extending.
        if (!$concrete instanceof Closure) {
            if (!is_string($concrete)) {
                throw new TypeError(self::class.'::bind(): Argument #2 ($concrete) must be of type Closure|string|null');
            }
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Resolve and retrieve an instance from the container by its abstract identifier.
     *
     * This method attempts to resolve the given abstract identifier to its concrete
     * implementation by delegating to the `resolve()` method. If resolution fails and
     * an exception is thrown, it is re-thrown as a new `Exception` to provide context
     * regarding the failed resolution.
     *
     * @param  string $id The abstract identifier of the instance to retrieve.
     * @return mixed      The resolved instance.
     *
     * @throws Exception If resolution of the abstract identifier fails.
     */
    public function get(string $id)
    {
        try {
            return $this->resolve($id);
        } catch (Exception $e) {
            throw new Exception($id, is_int($e->getCode()) ? $e->getCode() : 0, $e);
        }
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param  string  $abstract The abstract identifier to check.
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]) ||
               isset($this->instances[$abstract]) ||
               $this->isAlias($abstract);
    }

    /**
     * Determine if a given string is an alias.
     *
     * @param  string  $name The alias name.
     * @return bool
     */
    public function isAlias($name)
    {
        return isset($this->aliases[$name]);
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param  string  $abstract The abstract identifier to check.
     * @return bool
     */
    public function has(string $abstract): bool
    {
        return $this->bound($abstract);
    }

    /**
     * Check if an instance of the specified abstract is registered in the container.
     *
     * This method determines whether an instance has been created and registered in the
     * container for the given abstract identifier. It is useful for verifying if a particular
     * dependency has already been resolved.
     *
     * @param string $abstract The abstract identifier to check for an existing instance.
     * @return bool True if an instance for the abstract is registered, otherwise false.
     */
    public function isResolved(string $abstract) : bool
    {
        if (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }

        return isset($this->instances[$abstract]);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string|callable  $abstract   The abstract identifier or callable.
     * @param  array  $parameters   Additional parameters for resolution (optional).
     * @return mixed
     */
    public function resolve($abstract, $parameters = [])
    {
        if (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract]['concrete'];
            $shared = $this->bindings[$abstract]['shared'];

            if ($concrete instanceof Closure) {
                $object = $concrete($this, $parameters);
            } else {
                $object = $this->build($concrete);
            }

            if ($shared) {
                $this->instances[$abstract] = $object;
            }

            return $object;
        }

        $object = $this->build($abstract);

        $this->instances[$abstract] = $object;

        return $object;
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param  \Closure|string  $concrete   The concrete implementation or class name.
     * @return mixed
     *
     * @throws Exception
     */
    public function build($abstract)
    {
        try {
            $reflector = new ReflectionClass($abstract);
        } catch (ReflectionException $e) {
            throw new Exception("Target class [$abstract] does not exist.", 0, $e);
        }

        return $reflector->newInstance();
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string|callable  $abstract   The abstract identifier or callable.
     * @param  array  $parameters   Additional parameters for resolution (optional).
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Dynamically access container services.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this[$key];
    }

    /**
     * Dynamically set container services.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }
}
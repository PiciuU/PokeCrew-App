<?php

namespace Framework\Support;

use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;

/**
 * Class Env
 *
 * The Env class provides a convenient interface for interacting with environment variables.
 * It utilizes the Dotenv library for managing environment variables and supports the Putenv adapter.
 *
 * @package Framework\Support
 */
class Env
{
    /**
     * Indicates if the Putenv adapter is enabled for managing environment variables.
     *
     * @var bool
     */
    protected static $putenv = true;

    /**
     * Get the environment repository instance.
     *
     * @return \Dotenv\Repository\RepositoryInterface|null The environment repository instance.
     */
    protected static $repository;

    public static function getRepository()
    {
        if (static::$repository === null) {
            $builder = RepositoryBuilder::createWithDefaultAdapters();

            if (static::$putenv) {
                $builder = $builder->addAdapter(PutenvAdapter::class);
            }

            static::$repository = $builder->immutable()->make();
        }

        return static::$repository;
    }

    /**
     * Get the value of an environment variable or a default value if the variable is not set.
     *
     * @param string $key The name of the environment variable.
     * @param mixed $default The default value to return if the environment variable is not set.
     * @return mixed The value of the environment variable or the default value.
     */
    public static function get($key, $default = null)
    {
        return self::getOption($key) ?? $default;
    }

    /**
     * Get the value of an environment variable using the Dotenv repository.
     *
     * @param string $key The name of the environment variable.
     * @return mixed The value of the environment variable.
     */
    protected static function getOption($key)
    {
        return static::getRepository()->get($key);
    }
}
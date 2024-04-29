<?php

namespace Framework\Services\Auth\Middleware;

use Framework\Services\Auth\Exceptions\AuthenticationException;

use Closure;

/**
 * Class Authenticate
 *
 * The Authenticate class provides a middleware for authentication checks based on configured guards.
 * It allows specifying an array of guards to check, and it throws an AuthenticationException if none of the guards pass.
 *
 * @package Framework\Services\Auth\Middleware
 */
abstract class Authenticate
{
    /**
     * Perform authentication check for the specified guards.
     *
     * @param array|null $guards The array of guard names to check. Defaults to all configured guards if not provided.
     * @throws AuthenticationException If none of the specified guards pass, an AuthenticationException is thrown.
     * @return void
     */
    public static function authenticate($guards = null)
    {
        if (empty($guards)) $guards = array_keys(config('auth.guards'));

        foreach($guards as $guard) {
            if (app('auth')->guard($guard)->check()) {
                return app('auth')->shouldUse($guard);
            }
        }

        throw new AuthenticationException('Unauthenticated.');
    }
}
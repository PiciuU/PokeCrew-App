<?php

namespace Framework\Services\Auth\Traits;

use Framework\Services\Auth\Exceptions\AuthenticationException;

/**
 * Trait GuardHelpers
 *
 * The GuardHelpers trait provides helper methods for managing user authentication within guard classes.
 * It includes methods for authentication, user presence checks, guest checks, and setting and retrieving the user instance.
 *
 * @package Framework\Services\Auth\Traits
 */
trait GuardHelpers
{
    /**
     * The authenticated user instance.
     *
     * @var mixed|null
     */
    protected $user;

    /**
     * The user provider instance.
     *
     * @var \Framework\Services\Auth\ORMUserProvider
     */
    protected $provider;

    /**
     * Authenticate the user and retrieve the authenticated user instance.
     *
     * @return mixed|null The authenticated user instance.
     * @throws \Framework\Services\Auth\Exceptions\AuthenticationException If authentication fails.
     */
    public function authenticate()
    {
        if (!is_null($user = $this->user())) {
            return $user;
        }

        throw new AuthenticationException();
    }

    /**
     * Check if a user is authenticated.
     *
     * @return bool True if a user is authenticated, false otherwise.
     */
    public function hasUser()
    {
        return !is_null($this->user);
    }

    /**
     * Check if a user is authenticated.
     *
     * @return bool True if a user is authenticated, false otherwise.
     */
    public function check()
    {
        return !is_null($this->user());
    }

    /**
     * Check if a user is a guest (not authenticated).
     *
     * @return bool True if the user is a guest, false if authenticated.
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Get the ID for the authenticated user.
     *
     * @return mixed|null The ID of the authenticated user or null if the user is not authenticated.
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
    }

    /**
     * Set the current user.
     *
     * @param mixed $user The authenticated user instance.
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Forget (unset) the current user.
     *
     * @return $this
     */
    public function forgetUser()
    {
        $this->user = null;

        return $this;
    }

    /**
     * Get the user provider instance.
     *
     * @return \Framework\Services\Auth\ORMUserProvider The user provider instance.
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set the user provider instance.
     *
     * @param \Framework\Services\Auth\ORMUserProvider $provider The user provider instance.
     * @return void
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }
}
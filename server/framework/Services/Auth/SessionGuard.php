<?php

namespace Framework\Services\Auth;

/**
 * Class SessionGuard
 *
 * The SessionGuard class is responsible for handling user authentication using session storage.
 * It provides methods for user login, logout, and retrieval based on session data.
 *
 * @package Framework\Services\Auth
 */
class SessionGuard
{
    use Traits\GuardHelpers;


    /**
     * The user we last attempted to retrieve.
     *
     * @var \Framework\Services\Auth\User
     */
    protected $lastAttempted;

    /**
     * Indicates whether the user has been logged out.
     *
     * @var bool
     */
    protected $loggedOut = false;

    /**
     * SessionGuard constructor.
     *
     * Initializes the user provider instance with the configured user model.
     */
    public function __construct()
    {
        $this->provider = new ORMUserProvider(config('auth.providers.users.model'));
    }

    /**
     * Get the currently authenticated user.
     *
     * @return mixed|null The authenticated user instance or null if the user is not authenticated or logged out.
     */
    public function user()
    {
        if ($this->loggedOut) {
            return;
        }

        if (!is_null($this->user)) {
            return $this->user;
        }

        return;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return mixed|null The ID of the authenticated user or null if the user is not authenticated or logged out.
     */
    public function id()
    {
        if ($this->loggedOut) {
            return;
        }

        return $this->user()->getAuthIdentifier() ?? null;
    }

    /**
     * Attempt to authenticate a user based on the given credentials.
     *
     * @param array $credentials The user credentials.
     * @param bool $remember Indicates whether to remember the user.
     * @return bool True if the authentication attempt is successful, false otherwise.
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        return false;
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param mixed $user The user instance.
     * @param array $credentials The user credentials.
     * @return bool True if the user has valid credentials, false otherwise.
     */
    public function hasValidCredentials($user, $credentials)
    {
        return !is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Log the user into the application.
     *
     * @param mixed $user The authenticated user instance.
     * @param bool $remember Indicates whether to remember the user.
     * @return void
     */
    public function login($user, $remember)
    {
        $this->setUser($user);
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        $this->user = null;
        $this->loggedOut = true;
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

        $this->loggedOut = false;

        return $this;
    }

    /**
     * Get the current user.
     *
     * @return mixed|null The authenticated user instance or null if the user is not authenticated.
     */
    public function getUser()
    {
        return $this->user;
    }

}
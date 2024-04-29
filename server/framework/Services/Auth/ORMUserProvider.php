<?php

namespace Framework\Services\Auth;

use Framework\Support\Facades\Hash;

/**
 * Class ORMUserProvider
 *
 * The ORMUserProvider class is responsible for retrieving and validating user credentials in the authentication system.
 * It interacts with the underlying ORM model to perform operations related to user authentication.
 *
 * @package Framework\Services\Auth
 */
class ORMUserProvider
{
    /**
     * The ORM model class to use for retrieving and validating user credentials.
     *
     * @var string
     */
    private $model;

    /**
     * ORMUserProvider constructor.
     *
     * @param  string  $model The ORM model class to use.
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials The user credentials.
     * @return mixed|null The user instance or null if not found.
     */
    public function retrieveByCredentials(array $credentials)
    {
        $credentials = array_filter(
            $credentials,
            fn ($key) => ! str_contains($key, 'password'),
            ARRAY_FILTER_USE_KEY
        );

        if (empty($credentials)) {
            return;
        }

        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } elseif ($value instanceof Closure) {
                $value($query);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * Validate a user's credentials.
     *
     * @param  mixed  $user The user instance.
     * @param  array  $credentials The user credentials.
     * @return bool True if the credentials are valid; otherwise, false.
     */
    public function validateCredentials($user, array $credentials)
    {
        if (is_null($plain = $credentials['password'])) {
            return false;
        }

        return Hash::check($plain, $user->getAuthPassword());
    }

    /**
     * Create a new query instance for the model.
     *
     * @param  null  $model The model class (optional).
     * @return mixed The model query instance.
     */
    protected function newModelQuery($model = null)
    {
        $query = is_null($model)
                ? $this->createModel()->newQuery()
                : $model->newQuery();

        return $query;
    }

    /**
     * Create a new instance of the model.
     *
     * @return mixed The new model instance.
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }

    /**
     * Get the model class.
     *
     * @return string The model class.
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the model class.
     *
     * @param  string  $model The model class.
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

}
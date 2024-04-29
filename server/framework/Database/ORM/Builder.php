<?php

namespace Framework\Database\ORM;

use Framework\Support\Collections\Collection;

/**
 * Class Builder
 *
 * This class represents an ORM query builder, responsible for building and executing queries on a database.
 * It encapsulates a database query instance and provides methods to interact with the underlying query.
 * The Builder class is closely associated with a specific model, allowing for convenient operations on the associated database table.
 *
 * @package Framework\Database\ORM
 */
class Builder
{
    /**
     * The underlying database query instance.
     *
     * @var \Framework\Database\Query\Builder
     */
    private $query;

    /**
     * The model instance being queried.
     *
     * @var \Framework\Database\ORM\Model|static
     */
    private $model;

    /**
     * Builder constructor.
     *
     * @param \Framework\Database\Query\Builder $query
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * Set the model instance for the builder.
     *
     * @param \Framework\Database\ORM\Model $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        $this->query->from($model->getTable());

        return $this;
    }

    /**
     * Get the model instance being queried.
     *
     * @return \Framework\Database\ORM\Model|static
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Create a new model instance with the given attributes.
     *
     * @param array $attributes
     * @return \Framework\Database\ORM\Model
     */
    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes)->setConnectionName($this->query->getConnection()->getName());
    }

    /**
     * Create a new model instance with the given attributes and save it to the database.
     *
     * @param array $attributes
     * @return \Framework\Database\ORM\Model
     */
    public function create(array $attributes = [])
    {
        $instance = $this->newModelInstance($attributes);

        $instance->save();

        return $instance;
    }

    /**
     * Handle dynamic method calls to the builder.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function handleCall($method, $parameters)
    {
        $result = $this->query->$method(...$parameters);

        if ($result instanceof Collection)
        {
            $models = $this->hydrate($result->all());

            return $models;
        }
        else if (is_numeric($result))
        {
            return $result;
        }

        return $this;
    }

    /**
     * Check if any records exist in the result set.
     *
     * @return bool True if records exist; otherwise, false.
     */
    public function exists()
    {
        return $this->query->exists();
    }

    /**
     * Execute the query and return the first result or null if no results are found.
     *
     * @param mixed ...$parameters Additional parameters for the query execution.
     * @return \Framework\Database\ORM\Model|null The first result or null if no results are found.
     */
    public function first(...$parameters)
    {
        $result = $this->query->first(...$parameters);

        if (!$result) return null;

        return $this->hydrate([$result])->first();
    }

    /**
     * Execute the query and return the first result or null if no results are found.
     *
     * @param mixed ...$parameters Additional parameters for the query execution.
     * @return \Framework\Database\ORM\Model|null The first result or null if no results are found.
     */
    public function find(...$parameters)
    {
        $columns = isset($parameters[1]) ? $parameters[1] : ['*'];
        $result = $this->query->where($this->model->getKeyName(), '=', $parameters[0])->first($columns);

        if (!$result) return null;

        return $this->hydrate([$result])->first();
    }

    /**
     * Hydrate the given items into model instances.
     *
     * @param array $items
     * @return \Framework\Support\Collections\Collection
     */
    public function hydrate(array $items)
    {
        $instance = $this->newModelInstance();

        return $instance->newCollection(array_map(function ($item) use ($items, $instance) {
            $model = $instance->newFromBuilder($item);

            return $model;
        }, $items));
    }

    /**
     * Dynamically handle calls to the builder.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->handleCall($method, $parameters);
    }
}
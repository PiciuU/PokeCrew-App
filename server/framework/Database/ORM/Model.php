<?php

namespace Framework\Database\ORM;

use JsonSerializable;

use Framework\Support\Str;

use Carbon\Carbon;

/**
 * Class Model
 *
 * This abstract class serves as the base for all ORM (Object-Relational Mapping) models in the framework. It incorporates traits
 * for attribute handling, guards, and attribute visibility. Models extend this class to interact with the database using
 * a query builder and provide an object-oriented interface for database operations.
 *
 * @package Framework\Database\ORM
 */
abstract class Model implements JsonSerializable
{
    use Traits\GuardsAttributes, Traits\HasAttributes, Traits\HidesAttributes;

    /**
     * The database connection name.
     *
     * @var string|null
     */
    public $connection;

    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates whether the IDs are auto-incrementing.
     *
     * @var bool
     */
    protected $incrementing = true;

    /**
     * Indicates if the model exists in the database.
     *
     * @var bool
     */
    protected $exists = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The name of the "created at" column.
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     */
    const UPDATED_AT = 'updated_at';

    /**
     * Model constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->syncOriginal();
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Framework\Database\ORM\Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Framework\Database\ORM\Builder
     */
    public function newQuery()
    {
        return $this->newORMBuilder(
            $this->newBaseQueryBuilder()
        )->setModel($this);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Framework\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        return $this->getConnection()->query();
    }

   /**
     * Create a new ORM query builder for the model.
     *
     * @param  \Framework\Database\Query\Builder  $query
     * @return \Framework\Database\ORM\Builder|static
     */
    public function newORMBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Create a new instance of the model with the given attributes.
     *
     * @param array $attributes
     * @param bool $exists
     * @return \Framework\Database\ORM\Model
     */
    public function newInstance($attributes = [], $exists = false)
    {
        $model = new static;

        $model->exists = $exists;

        $model->setConnectionName(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());


        $model->fill((array) $attributes);

        return $model;
    }

    /**
     * Create a new model instance that is existing from raw attributes.
     *
     * @param array $attributes
     * @param string|null $connection
     * @return \Framework\Database\ORM\Model
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = $this->newInstance([], true);

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnectionName($connection ?: $this->getConnectionName());

        return $model;
    }

    /**
     * Create a new ORM Collection instance.
     *
     * @param  array  $models
     * @return \Framework\Database\ORM\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    /**
     * Retrieve all records from the model.
     *
     * @param array $columns
     * @return \Framework\Database\ORM\Collection
     */
    public static function all($columns = ['*'])
    {
        return static::query()->get(
            is_array($columns) ? $columns : func_get_args()
        );
    }

    /**
     * Set the table associated with the model.
     *
     * @param string $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Update the model in the database.
     *
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (!$this->exists) {
            return false;
        }

        return $this->fill($attributes)->save($options);
    }

    /**
     * Fill the model's attributes.
     *
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes)
    {
        $fillable = $this->fillableFromArray($attributes);

        foreach($fillable as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * Save the model to the database.
     *
     * @param array $options
     * @return bool
     */
    public function save($options = [])
    {
        $query = $this->newQuery();

        if ($this->exists) {
            $saved = $this->isDirty() ? $this->performUpdate($query) : true;
        }
        else {
            $saved = $this->performInsert($query);
        }

        if ($saved) {
            $this->finishSave($options);
        }

        return $saved;
    }

    /**
     * Perform any actions required after the model is saved.
     *
     * @param array $options
     * @return void
     */
    public function finishSave($options)
    {
        $this->syncOriginal();
    }


    /**
     * Delete the model from the database.
     *
     * @return bool
     */
    public function delete()
    {
        if (is_null($this->getKeyName())) {
            throw new LogicException('No primary key defined on model.');
        }

        if (!$this->exists) return;

        $this->performDeleteOnModel();

        return true;
    }

    /**
     * Perform the actual deletion of the model from the database.
     *
     * @return void
     */
    protected function performDeleteOnModel()
    {
        $this->setKeysForSaveQuery($this->newQuery())->delete();

        $this->exists = false;
    }

    /**
     * Perform the actual update of the model in the database.
     *
     * @param \Framework\Database\Query\Builder $query
     * @return void
     */
    public function performUpdate($query)
    {
        if ($this->usesTimestamps()) {
            $this->mergeFillable([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn()]);

            $this->fill(array_merge($this->getAttributes(), [
                $this->getUpdatedAtColumn() => Carbon::now()
            ]));
        }


        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            $this->setKeysForSaveQuery($query)->update($dirty);

            $this->syncChanges();
        }
    }

    /**
     * Perform the actual insertion of the model into the database.
     *
     * @param \Framework\Database\Query\Builder $query
     * @return void
     */
    protected function performInsert($query)
    {
        if ($this->usesTimestamps()) {
            $this->mergeFillable([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn()]);

            $this->fill(array_merge($this->getAttributes(), [
                $this->getCreatedAtColumn() => Carbon::now(),
                $this->getUpdatedAtColumn() => Carbon::now()
            ]));
        }


        $attributes = $this->getAttributesForInsert();

        if ($this->getIncrementing()) {
            $this->insertAndSetId($query, $attributes);
        }
        else {
            if (empty($attributes)) {
                return true;
            }

            $query->insert($attributes);
        }


        $this->exists = true;

        return true;
    }

    /**
     * Insert the given attributes and set the ID on the model.
     *
     * @param \Framework\Database\Query\Builder $query
     * @param array $attributes
     * @return void
     */
    protected function insertAndSetId(Builder $query, $attributes)
    {
        $query->insert($attributes);

        $keyName = $this->getKeyName();

        $id = $this->getConnection()->getLastInsertId();

        $this->setAttribute($keyName, $id);
    }

    /**
     * Set the keys for a save update query.
     *
     * @param \Framework\Database\Query\Builder $query
     * @return \Framework\Database\Query\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @return mixed
     */
    protected function getKeyForSaveQuery()
    {
        return $this->original[$this->getKeyName()] ?? $this->getKey();
    }

    /**
     * Determine whether the model uses auto-incrementing keys.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return $this->incrementing;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table ?? Str::snake(Str::plural(class_basename($this)));
    }

    /**
     * Get the database connection instance.
     *
     * @return \Framework\Database\Connection
     */
    public function getConnection()
    {
        return static::resolveConnection($this->getConnectionName());
    }

    /**
     * Resolve a connection instance.
     *
     * @param string|null $connection
     * @return \Framework\Database\Connection
     */
    public static function resolveConnection($connection = null)
    {
        return app('db')->connection($connection);
    }

    /**
     * Get the current connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        return $this->connection;
    }

    /**
     * Set the connection name associated with the model.
     *
     * @param  string|null  $name
     * @return $this
     */
    public function setConnectionName($name)
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Determine if the model uses timestamps.
     *
     * @return bool
     */
    public function usesTimestamps()
    {
        return $this->timestamps;
    }

    /**
     * Get the name of the "created at" column.
     *
     * @return string|null
     */
    public function getCreatedAtColumn()
    {
        return static::CREATED_AT;
    }

    /**
     * Get the name of the "updated at" column.
     *
     * @return string|null
     */
    public function getUpdatedAtColumn()
    {
        return static::UPDATED_AT;
    }

    /**
     * Get the model's attributes as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributesToArray();
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  mixed  $handler
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function forwardCallTo($handler, $method, $parameters)
    {
        return $handler->{$method}(...$parameters);
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->newQuery(), $method, $parameters);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * Handle dynamic property access.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Handle dynamic property assignment.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

}

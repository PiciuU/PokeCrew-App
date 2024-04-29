<?php

namespace Framework\Database;

use Framework\Database\Connections\MySqlConnection;
use Framework\Database\Connections\SQLiteConnection;

use Framework\Support\Arr;
use InvalidArgumentException;

/**
 * Class DatabaseManager
 *
 * The DatabaseManager class is responsible for managing database connections and facilitating interaction with
 * various database engines. It provides methods for changing the current connection, obtaining a database connection,
 * making new database connections, and disconnecting from specific connections.
 *
 * @package Framework\Database
 */
class DatabaseManager
{
    /**
     * The array of database connections.
     *
     * @var array
     */
    private $connections = [];

    /**
     * The name of the current database connection.
     *
     * @var string|null
     */
    private $currentConnectionName;

    /**
     * Change the current database connection.
     *
     * @param string $name The name of the database connection.
     * @return void
     */
    public function changeCurrentConnection($name)
    {
        $this->currentConnectionName = $name;
    }

    /**
     * Get the current database connection or create a new one.
     *
     * @return mixed The database connection instance.
     */
    public function connection()
    {
        $this->currentConnectionName = $this->currentConnectionName ?: config('database.default');

        if (!isset($this->connections[$this->currentConnectionName])) {
            $this->connections[$this->currentConnectionName] = $this->makeConnection($this->currentConnectionName);
        }

        return $this->connections[$this->currentConnectionName];
    }

    /**
     * Make a new database connection instance.
     *
     * @param string $name The name of the database connection.
     * @return mixed The database connection instance.
     */
    protected function makeConnection($name)
    {
        $config = $this->configuration($name);

        return match ($name) {
            'mysql' => new MySqlConnection($config),
            'sqlite' => new SQLiteConnection($config),
            default => throw new InvalidArgumentException("Unsupported database driver [{$name}]."),
        };
    }

    /**
     * Get the configuration for a given connection.
     *
     * @param string $name The name of the database connection.
     * @return array The database connection configuration.
     * @throws InvalidArgumentException If the connection configuration is not found.
     */
    protected function configuration($name)
    {
        $connections = config('database.connections');

        if (is_null($config = Arr::get($connections, $name))) {
            throw new InvalidArgumentException("Database connection [{$name}] not configured.");
        }

        return $config;
    }

    /**
     * Disconnect from the specified database connection(s).
     *
     * @param string|null $name The name of the database connection to disconnect from. If null, disconnect from all connections.
     * @return void
     */
    public function disconnect($name = null) {
        if ($name) {
            if (isset($this->connections[$name])) {
                $this->connections[$name]->disconnect();
                unset($this->connections[$name]);
            }
        } else {
            foreach ($this->connections as $name => $connection) {
                $connection->disconnect();
                unset($this->connections[$name]);
            }
        }
    }

    /**
     * Dynamically pass methods to the default database connection.
     *
     * @param string $method The method being called.
     * @param array $parameters The parameters passed to the method.
     * @return mixed The result of the method call.
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}
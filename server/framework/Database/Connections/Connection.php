<?php

namespace Framework\Database\Connections;

use Framework\Database\Query\Builder as QueryBuilder;
use Framework\Database\Grammars\Grammar as QueryGrammar;
use Framework\Database\Query\Expression;
use Framework\Exceptions\Database\QueryExecutionError;
use PDO;
use PDOException;

/**
 * Class Connection
 *
 * The Connection class represents a connection to a database using PHP PDO.
 * It encapsulates common database operations and provides a clean interface
 * for executing queries, inserts, updates, and deletes.
 *
 * @package Framework\Database\Connections
 */
class Connection
{
    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * The PDO instance for the database connection.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * The name of the connected database.
     *
     * @var string
     */
    protected $database;

    /**
     * The configuration array for the database connection.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The table prefix for the database connection.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * The query grammar instance for the database connection.
     *
     * @var QueryGrammar
     */
    protected $queryGrammar;

    /**
     * The fetch mode used by the database connection.
     *
     * @var int
     */
    protected $fetchMode = PDO::FETCH_OBJ;

    /**
     * The last ID inserted into the database.
     *
     * @var mixed|null
     */
    protected $lastInsertId;

    /**
     * Indicates whether records have been modified in the database connection.
     *
     * @var bool
     */
    protected $recordsModified = false;

    /**
     * Connection constructor.
     *
     * @param PDO   $pdo    The PDO instance representing the database connection.
     * @param array $config The database configuration.
     */
    public function __construct(PDO $pdo, $config)
    {
        $this->pdo = $pdo;

        $this->database = $config['database'];

        $this->tablePrefix = $config['prefix'];

        $this->useDefaultQueryGrammar();
    }

    /**
     * Set the default query grammar for the connection.
     */
    public function useDefaultQueryGrammar()
    {
        $this->queryGrammar = $this->getDefaultQueryGrammar();
    }

    /**
     * Get the default query grammar instance.
     *
     * @return QueryGrammar The default query grammar instance.
     */
    protected function getDefaultQueryGrammar()
    {
        ($grammar = new QueryGrammar)->setConnection($this);

        return $grammar;
    }

    /**
     * Get the query grammar used by the connection.
     *
     * @return QueryGrammar The query grammar instance.
     */
    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }

    /**
     * Get PDO connection options from the configuration.
     *
     * @param array $config Database connection configuration.
     * @return array PDO connection options.
     */
    protected function getOptions(array $config)
    {
        $options = $config['options'] ?? [];

        return array_diff_key($this->options, $options) + $options;
    }

    /**
     * Get a new query builder instance for the table.
     *
     * @param string      $table The table name.
     * @param string|null $as    The alias for the table.
     * @return QueryBuilder The query builder instance for the specified table.
     */
    public function table($table, $as = null)
    {
        return $this->query()->from($table, $as);
    }

    /**
     * Create a new query builder instance for the connection.
     *
     * @return QueryBuilder The query builder instance.
     */
    public function query()
    {
        return new QueryBuilder($this, $this->getQueryGrammar());
    }

    /**
     * Execute a raw SQL statement on the database.
     *
     * @param string $value The raw SQL statement.
     * @return Expression The expression instance representing the raw SQL.
     */
    public function raw($value)
    {
        return new Expression($value);
    }

    /**
     * Execute a SELECT statement and return the results.
     *
     * @param string $query    The SQL SELECT statement.
     * @param array  $bindings The parameter bindings for the statement.
     * @return array The result set of the query.
     */
    public function select($query, $bindings = [])
    {
        try {
            $statement = $this->pdo->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();
        } catch (\Exception $e) {
            throw new QueryExecutionError($e->getMessage(), ['statement' => $query, 'bindings' => implode(', ', $bindings)], 0, $e);
        }

        return $statement->fetchAll($this->fetchMode);
    }

    /**
     * Insert a new record into the database.
     *
     * @param string $query    The SQL INSERT statement.
     * @param array  $bindings The parameter bindings for the statement.
     * @return bool True on success, false on failure.
     */
    public function insert($query, $bindings = [])
    {
        try {
            $statement = $this->pdo->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $this->recordsHaveBeenModified();

            $result = $statement->execute();

            $this->lastInsertId = $this->pdo->lastInsertId();
        } catch (\Exception $e) {
            throw new QueryExecutionError($e->getMessage(), ['statement' => $query, 'bindings' => implode(', ', $bindings)], 0, $e);
        }

        return $result;
    }

    /**
     * Update records in the database.
     *
     * @param string $query    The SQL UPDATE statement.
     * @param array  $bindings The parameter bindings for the statement.
     * @return int The number of affected rows.
     */
    public function update($query, $bindings = [])
    {
        try {
            $statement = $this->pdo->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            $this->recordsHaveBeenModified(
                ($count = $statement->rowCount()) > 0
            );
        } catch (\Exception $e) {
            throw new QueryExecutionError($e->getMessage(), ['statement' => $query, 'bindings' => implode(', ', $bindings)], 0, $e);
        }

        return $count;
    }

    /**
     * Delete records from the database.
     *
     * @param string $query    The SQL DELETE statement.
     * @param array  $bindings The parameter bindings for the statement.
     * @return int The number of affected rows.
     */
    public function delete($query, $bindings = [])
    {
        try {
            $statement = $this->pdo->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            $this->recordsHaveBeenModified(
                ($count = $statement->rowCount()) > 0
            );
        } catch (\Exception $e) {
            throw new QueryExecutionError($e->getMessage(), ['statement' => $query, 'bindings' => implode(', ', $bindings)], 0, $e);
        }

        return $count;
    }

    /**
     * Bind values to their parameters in the statement.
     *
     * @param PDOStatement $statement The PDO statement.
     * @param array        $bindings  The parameter bindings.
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                match (true) {
                    is_int($value) => PDO::PARAM_INT,
                    is_resource($value) => PDO::PARAM_LOB,
                    default => PDO::PARAM_STR
                },
            );
        }
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param array $bindings The parameter bindings.
     * @return array The prepared bindings.
     */
    public function prepareBindings(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }

    /**
     * Indicate that the records have been modified.
     *
     * @param bool $value Whether records have been modified.
     */
    public function recordsHaveBeenModified($value = true)
    {
        if (!$this->recordsModified) {
            $this->recordsModified = $value;
        }
    }

    /**
     * Get the last inserted ID.
     *
     * @return mixed|null The last inserted ID or null if not available.
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId ?: null;
    }

    /**
     * Disconnect the PDO connection.
     */
    public function disconnect() {
        $this->pdo = null;
    }

    /**
     * Set the table prefix on the query grammar.
     *
     * @param QueryGrammar $grammar The query grammar instance.
     * @return QueryGrammar The updated query grammar instance.
     */
    public function withTablePrefix(QueryGrammar $grammar)
    {
        $grammar->setTablePrefix($this->tablePrefix);

        return $grammar;
    }
}
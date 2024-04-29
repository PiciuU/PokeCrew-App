<?php

namespace Framework\Database\Query;

use Framework\Database\Grammars\Grammar;
use Framework\Support\Arr;

use InvalidArgumentException;

/**
 * The Builder class, imported from Laravel's Framework\Database\Query\Builder, is responsible for constructing and executing database queries.
 * It serves as the main query builder for the database connection, allowing the
 * construction of SQL queries using a fluent interface. This class provides methods
 * for building various parts of a SQL query, including selecting columns, adding
 * conditions, joining tables, specifying order, and executing aggregate functions.
 *
 * @package Framework\Database\Query [Laravel@Illuminate\Database\Query\Grammars\Grammar]
 */
class Builder
{
    /**
     * The database connection instance.
     *
     * @var \Framework\Database\Connections\Connection
     */
    public $connection;

    /**
     * The database query grammar instance.
     *
     * @var \Framework\Database\Grammars\Grammar
     */
    public $grammar;

    /**
     * The current query value bindings.
     *
     * @var array
     */
    public $bindings = [
        'select' => [],
        'from' => [],
        'join' => [],
        'where' => [],
        'groupBy' => [],
        'having' => [],
        'order' => [],
        'union' => [],
        'unionOrder' => [],
    ];

    /**
     * An aggregate function and column to be run.
     *
     * @var array
     */
    public $aggregate;

    /**
     * The columns that should be returned.
     *
     * @var array
     */
    public $columns;

    /**
     * Indicates if the query returns distinct results.
     *
     * Occasionally contains the columns that should be distinct.
     *
     * @var bool|array
     */
    public $distinct = false;

    /**
     * The table which the query is targeting.
     *
     * @var string
     */
    public $from;

    /**
     * The table joins for the query.
     *
     * @var array
     */
    public $joins;

    /**
     * The where constraints for the query.
     *
     * @var array
     */
    public $wheres;

    /**
     * The groupings for the query.
     *
     * @var array
     */
    public $groups;

   /**
     * The having constraints for the query.
     *
     * @var array
     */
    public $havings;

    /**
     * The orderings for the query.
     *
     * @var array
     */
    public $orders;

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    public $limit;

    /**
     * The number of records to skip.
     *
     * @var int
     */
    public $offset;

    /**
     * The query union statements.
     *
     * @var array
     */
    public $unions;

    /**
     * The maximum number of union records to return.
     *
     * @var int
     */
    public $unionLimit;

   /**
     * The number of union records to skip.
     *
     * @var int
     */
    public $unionOffset;

    /**
     * The orderings for the union query.
     *
     * @var array
     */
    public $unionOrders;

    /**
     * The built statement prepared for query binding
     *
     * @var array
     */
    private $sql;

    /**
     * Create a new query builder instance.
     *
     * @param  \Framework\Database\Connections\Connection $connection
     * @param  \Framework\Databaase\Grammars\Grammar  $grammar
     * @return void
     */
    public function __construct($connection, $grammar)
    {
        $this->connection = $connection;
        $this->grammar = $grammar;
    }

    /**
     * Get the database connection instance.
     *
     * @return \Framework\Database\Connections\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set the columns to be selected.
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->columns = [];
        $this->bindings['select'] = [];

        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }

    /**
     * Add a new select column to the query.
     *
     * @param  array|mixed  $column
     * @return $this
     */
    public function addSelect($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }

    /**
     * Add a new "raw" select expression to the query.
     *
     * @param  string  $expression
     * @param  array  $bindings
     * @return $this
     */
    public function selectRaw($expression, array $bindings = [])
    {
        $this->addSelect(new Expression($expression));

        if ($bindings) {
            $this->addBinding($bindings, 'select');
        }

        return $this;
    }

    /**
     * Force the query to only return distinct results.
     *
     * @return $this
     */
    public function distinct($distinct = true)
    {
        $this->distinct = $distinct;

        return $this;
    }


    /**
     * Set the table which the query is targeting.
     *
     * @param  string  $table
     * @param  string|null  $as
     * @return $this
     */
    public function from($table, $as = null)
    {
        $this->from = $as ? "{$table} as {$as}" : $table;

        return $this;
    }

    /**
     * Add a raw from clause to the query.
     *
     * @param  string  $expression
     * @param  mixed  $bindings
     * @return $this
     */
    public function fromRaw($expression)
    {
        $this->from = new Expression($expression);

        $this->addBinding($bindings, 'from');

        return $this;
    }

    /**
     * Add a join clause to the query.
     *
     * @param  string  $table
     * @param  string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @param  string  $type
     * @param  bool  $where
     * @return $this
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'INNER', $where = false)
    {
        if (func_num_args() === 3) {
            $second = $operator;
            $operator = '=';
        }

        $method = $where ? 'WHERE' : 'ON';

        $this->joins[] = compact(
            'table', 'first', 'operator', 'second', 'type', 'method'
        );

        return $this;
    }

    /**
     * Add a left join to the query.
     *
     * @param  string  $table
     * @param  string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @return $this
     */
    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Add a right join to the query.
     *
     * @param  string  $table
     * @param  string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @return $this
     */
    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'AND')
    {
        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        [$value, $operator] = $this->grammar->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->grammar->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        // If the value is "null", we will just assume the developer wants to add a
        // where null clause to the query. So, we will allow a short-cut here to
        // that method for convenience so the developer doesn't have to check.
        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator !== '=');
        }

        // Make sure that boolean is correct.
        $boolean = strtoupper(in_array(strtolower($boolean), ['and', 'or', 'and not', 'or not']) ? $boolean : 'AND');

        // Now that we are working with just a simple query we can put the elements
        // in our array and add the query binding to our array of bindings that
        // will be bound to each SQL statements when it is finally executed.
        $type = 'Basic';

        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );

        $this->addBinding($this->grammar->flattenValue($value), 'where');

        return $this;
    }


    /**
     * Add an "or where" clause to the query.
     *
     * @param string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->grammar->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Add a basic "where not" clause to the query.
     *
     * @param string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function whereNot($column, $operator = null, $value = null, $boolean= 'AND')
    {
        return $this->where($column, $operator, $value, $boolean.' NOT');
    }

    /**
     * Add an "or where not" clause to the query.
     *
     * @param string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function orWhereNot($column, $operator = null, $value = null)
    {
        return $this->whereNot($column, $operator, $value, 'OR');
    }

    /**
     * Add a "where" clause comparing two columns to the query.
     *
     * @param  string|array  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @param  string|null  $boolean
     * @return $this
     */
    public function whereColumn($first, $operator = null, $second = null, $boolean = 'AND')
    {
        if ($this->grammar->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        $type = 'Column';

        $this->wheres[] = compact(
            'type', 'first', 'operator', 'second', 'boolean'
        );

        return $this;
    }

    /**
     * Add an "or where" clause comparing two columns to the query.
     *
     * @param  string|array  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @return $this
     */
    public function orWhereColumn($first, $operator = null, $second = null)
    {
        return $this->whereColumn($first, $operator, $second, 'OR');
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereIn($column, $values = [], $boolean= 'AND', $not = false)
    {
        $type = $not ? 'NotIn' : 'In';

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        $this->addBinding($this->cleanBindings($values), 'where');

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, 'OR');
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotIn($column, $values, $boolean = 'AND')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, 'OR');
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param  string|array  $columns
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereNull($columns, $boolean = 'AND', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        foreach (Arr::wrap($columns) as $column) {
            $this->wheres[] = compact('type', 'column', 'boolean');
        }

        return $this;
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param  string|array  $columns
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotNull($columns, $boolean = 'AND')
    {
        return $this->whereNull($columns, $boolean, true);
    }

    /**
     * Add an "or where null" clause to the query.
     *
     * @param  string|array  $column
     * @return $this
     */
    public function orWhereNull($column)
    {
        return $this->whereNull($column, 'OR');
    }

    /**
     * Add an "or where not null" clause to the query.
     *
     * @param  string  $column
     * @return $this
     */
    public function orWhereNotNull($column)
    {
        return $this->whereNotNull($column, 'OR');
    }

    /**
     * Add a raw where clause to the query.
     *
     * @param  string  $sql
     * @param  mixed  $bindings
     * @param  string  $boolean
     * @return $this
     */
    public function whereRaw($sql, $bindings = [], $boolean = 'AND')
    {
        $this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => $boolean];

        $this->addBinding((array) $bindings, 'where');

        return $this;
    }

    /**
     * Add a raw or where clause to the query.
     *
     * @param  string  $sql
     * @param  mixed  $bindings
     * @return $this
     */
    public function orWhereRaw($sql, $bindings = [])
    {
        return $this->whereRaw($sql, $bindings, 'OR');
    }

    /**
     * Add a "group by" clause to the query.
     *
     * @param  array|string  ...$groups
     * @return $this
     */
    public function groupBy(...$groups)
    {
        foreach ($groups as $group) {
            $this->groups = array_merge(
                (array) $this->groups,
                Arr::wrap($group)
            );
        }

        return $this;
    }

    /**
     * Add a raw groupBy clause to the query.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return $this
     */
    public function groupByRaw($sql, array $bindings = [])
    {
        $this->groups[] = new Expression($sql);

        $this->addBinding($bindings, 'groupBy');

        return $this;
    }


    /**
     * Add a "having" clause to the query.
     *
     * @param  string  $column
     * @param  string|int|float|null  $operator
     * @param  string|int|float|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $boolean = 'AND')
    {
        $type = 'Basic';

       [$value, $operator] = $this->grammar->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        if ($this->grammar->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        $boolean = strtoupper(in_array(strtolower($boolean), ['and', 'or', 'and not', 'or not']) ? $boolean : 'AND');

        $this->havings[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );

        return $this;
    }

    /**
     * Add an "or having" clause to the query.
     *
     * @param  string  $column
     * @param  string|int|float|null  $operator
     * @param  string|int|float|null  $value
     * @return $this
     */
    public function orHaving($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->grammar->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->having($column, $operator, $value, 'OR');
    }

    /**
     * Add a "having null" clause to the query.
     *
     * @param  string|array  $columns
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function havingNull($columns, $boolean = 'AND', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        foreach (Arr::wrap($columns) as $column) {
            $this->havings[] = compact('type', 'column', 'boolean');
        }

        return $this;
    }

    /**
     * Add a "having not null" clause to the query.
     *
     * @param  string|array  $columns
     * @param  string  $boolean
     * @return $this
     */
    public function havingNotNull($columns, $boolean = 'AND')
    {
        return $this->havingNull($columns, $boolean, true);
    }

    /**
     * Add an "or having null" clause to the query.
     *
     * @param  string  $column
     * @return $this
     */
    public function orHavingNull($column)
    {
        return $this->havingNull($column, 'OR');
    }

    /**
     * Add an "or having not null" clause to the query.
     *
     * @param  string  $column
     * @return $this
     */
    public function orHavingNotNull($column)
    {
        return $this->havingNotNull($column, 'OR');
    }

    /**
     * Add a raw having clause to the query.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @param  string  $boolean
     * @return $this
     */
    public function havingRaw($sql, array $bindings = [], $boolean = 'AND')
    {
        $type = 'Raw';

        $this->havings[] = compact('type', 'sql', 'boolean');

        $this->addBinding($bindings, 'having');

        return $this;
    }

    /**
     * Add a raw or having clause to the query.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return $this
     */
    public function orHavingRaw($sql, array $bindings = [])
    {
        return $this->havingRaw($sql, $bindings, 'OR');
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param  string  $column
     * @param  string  $direction
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function orderBy($column, $direction = 'ASC')
    {
        if (!in_array(strtolower($direction), ['asc', 'desc']))
        {
            throw new InvalidArgumentException('Order direction must be "ASC" or "DESC".');
        }

        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * Add a descending "order by" clause to the query.
     *
     * @param  string  $column
     * @return $this
     */
    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'DESC');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param  string $column
     * @return $this
     */
    public function latest($column = 'created_at')
    {
        return $this->orderBy($column, 'DESC');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param  string  $column
     * @return $this
     */
    public function oldest($column = 'created_at')
    {
        return $this->orderBy($column, 'ASC');
    }

    /**
     * Put the query's results in random order.
     *
     * @param  string|int  $seed
     * @return $this
     */
    public function inRandomOrder($seed = '')
    {
        return $this->orderByRaw($this->grammar->compileRandom($seed));
    }

    /**
     * Add a raw "order by" clause to the query.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return $this
     */
    public function orderByRaw($sql, $bindings = [])
    {
        $type = 'Raw';

        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = compact('type', 'sql');

        $this->addBinding($bindings, $this->unions ? 'unionOrder' : 'order');

        return $this;
    }

    /**
     * Set the "offset" value of the query.
     *
     * @param  int  $value
     * @return $this
     */
    public function offset($value)
    {
        $property = $this->unions ? 'unionOffset' : 'offset';

        $this->$property = max(0, (int) $value);

        return $this;
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param  int  $value
     * @return $this
     */
    public function limit($value)
    {
        $property = $this->unions ? 'unionLimit' : 'limit';

        if ($value >= 0) {
            $this->$property = !is_null($value) ? (int) $value : null;
        }

        return $this;
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param  int|string  $id
     * @param  array|string  $columns
     * @return mixed|static
     */
    public function find($id, $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }

    /**
     * Execute a query for a first record in table.
     *
     * @param  int|string  $id
     * @param  array|string  $columns
     * @return mixed|static
     */
    public function first($columns = ['*'])
    {
        return $this->limit(1)->get($columns)->first();
    }

    /**
     * Get a single column's value from the first result of a query.
     *
     * @param  string  $column
     * @return mixed
     */
    public function value($column)
    {
        $result = (array) $this->first([$column]);

        return count($result) > 0 ? reset($result) : null;
    }

    /**
     * Determine if any rows exist for the current query.
     *
     * @return bool
     */
    public function exists()
    {
        $this->sql =  $this->grammar->compileExists($this);
        $results = $this->connection->select(
            $this->sql, $this->getBindings()
        );

        // If the results have rows, we will get the row and see if the exists column is a
        // boolean true. If there are no results for this query we will return false as
        // there are no rows for this query at all, and we can return that info here.
        if (isset($results[0])) {
            $results = (array) $results[0];

            return (bool) $results['exists'];
        }

        return false;
    }

    /**
     * Determine if no rows exist for the current query.
     *
     * @return bool
     */
    public function doesntExist()
    {
        return !$this->exists();
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  string  $columns
     * @return int
     */
    public function count($columns = '*')
    {
        return (int) $this->aggregate(strtoupper(__FUNCTION__), Arr::wrap($columns));
    }

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param string  $column
     * @return mixed
     */
    public function max($column)
    {
        return (int) $this->aggregate(strtoupper(__FUNCTION__), [$column]);
    }

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param string  $column
     * @return mixed
     */
    public function min($column)
    {
        return (int) $this->aggregate(strtoupper(__FUNCTION__), [$column]);
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param string  $column
     * @return mixed
     */
    public function sum($column)
    {
        $result = $this->aggregate(strtoupper(__FUNCTION__), [$column]);

        return $result ?: 0;
    }

    /**
     * Retrieve the average of the values of a given column.
     *
     * @param string  $column
     * @return mixed
     */
    public function avg($column)
    {
        return (int) $this->aggregate(strtoupper(__FUNCTION__), [$column]);
    }

    /**
     * Execute an aggregate function on the database.
     *
     * @param  string  $function
     * @param  array  $columns
     * @return mixed
     */
    public function aggregate($func, $columns = ['*'])
    {
        $results = $this->setAggregate($func, $columns)->get($columns);

        if (!$results->isEmpty()) {
            return array_change_key_case((array) $results->get(0))['aggregate'];
        }
    }

    /**
     * Set the aggregate property without running the query.
     *
     * @param  string  $function
     * @param  array  $columns
     * @return $this
     */
    protected function setAggregate($function, $columns)
    {
        $this->aggregate = compact('function', 'columns');

        if (empty($this->groups)) {
            $this->orders = null;

            $this->bindings['order'] = [];
        }

        return $this;
    }

    /**
     * Add a union statement to the query.
     *
     * @param  \Closure|\Framework\Database\Query\Builder  $query
     * @param  bool  $all
     * @return $this
     */
    public function union($query, $all = false)
    {
        $this->unions[] = compact('query', 'all');

        $this->addBinding($query->getBindings(), 'union');

        return $this;
    }

    /**
     * Add a union all statement to the query.
     *
     * @param  \Closure|\Framework\Database\Query\Builder  $query
     * @return $this
     */
    public function unionAll($query)
    {
        return $this->union($query, true);
    }

    /**
     * Get the SQL representation of the query.
     *
     * @return string
     */
    public function toSql()
    {
        return $this->grammar->compileSelect($this);
    }

    /**
     * Create a raw database expression.
     *
     * @param  mixed  $value
     * @return \Framework\Database\Query\Expression
     */
    public function raw($value)
    {
        return $this->connection->raw($value);
    }

    /**
     * Add a binding to the query.
     *
     * @param  mixed  $value
     * @param  string  $type
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function addBinding($value, $type = 'where')
    {
        if (!array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        if (is_array($value)) {
            $this->bindings[$type] = array_values(array_map(
                [$this, 'castBinding'],
                array_merge($this->bindings[$type], $value),
            ));
        } else {
            $this->bindings[$type][] = $value;
        }

        return $this;
    }

    /**
     * Get the current query value bindings in a flattened array.
     *
     * @return array
     */
    public function getBindings()
    {
        return Arr::flatten($this->bindings);
    }

    /**
     * Remove all of the expressions (CURRENTLY NOT SUPPORTED) from a list of bindings.
     *
     * @param  array  $bindings
     * @return array
     */
    public function cleanBindings(array $bindings)
    {
        return collect($bindings)
                    ->map([$this, 'castBinding'])
                    ->values()
                    ->all();
    }

    /**
     * Cast the given binding value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function castBinding($value)
    {
        return $value;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array|string  $columns
     * @return \Framework\Support\Collections\Collection
     */
    public function get($columns = ['*'])
    {
        return collect($this->onceWithColumns(Arr::wrap($columns), function () {
           return $this->runSelect();
        }));
    }

    /**
     * Run the query as a "select" statement against the connection.
     *
     * @return array
     */
    protected function runSelect()
    {
        $this->sql = $this->toSql();

        return $this->connection->select(
            $this->sql, $this->getBindings()
        );
    }


    /**
     * Execute the given callback while selecting the given columns.
     *
     * After running the callback, the columns are reset to the original value.
     *
     * @param  array  $columns
     * @param  callable  $callback
     * @return mixed
     */
    protected function onceWithColumns($columns, $callback)
    {
        $original = $this->columns;

        if (is_null($original)) {
            $this->columns = $columns;
        }

        $result = $callback();

        $this->columns = $original;

        return $result;
    }

    /**
     * Update records in the database.
     *
     * @param  array  $values
     * @return int
     */
    public function update(array $values)
    {
        $this->sql = $this->grammar->compileUpdate($this, $values);

        return $this->connection->update($this->sql, $this->cleanBindings(
            $this->grammar->prepareBindingsForUpdate($this->bindings, $values)
        ));
    }

    /**
     * Insert new records into the database.
     *
     * @param  array  $values
     * @return bool
     */
    public function insert(array $values)
    {
        if (empty($values)) {
            return true;
        }

        if (!is_array(reset($values))) {
            $values = [$values];
        }
        else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        $this->sql = $this->grammar->compileInsert($this, $values);

        return $this->connection->insert($this->sql, $this->cleanBindings(
            Arr::flatten($values, 1))
        );
    }

    /**
     * Delete records from the database.
     *
     * @param  mixed  $id
     * @return int
     */
    public function delete($id = null)
    {
        if (!is_null($id)) {
            $this->where('id', '=', $id);
        }

        $this->sql = $this->grammar->compileDelete($this);

        return $this->connection->delete($this->sql, $this->cleanBindings(
            $this->grammar->prepareBindingsForDelete($this->bindings)
        ));
    }

    /**
     * Dump the current SQL and bindings.
     *
     * @return $this
     */
    public function debug($disabled = false)
    {
        if ($disabled) return;

        $sql = $this->sql ?: $this->toSql();
        $bindings = $this->getBindings();
        $rawSql = $sql;
        foreach ($bindings as $binding) {
            if (is_int($binding)) $rawSql = preg_replace('/\?/', "$binding", $rawSql, 1);
            else $rawSql = preg_replace('/\?/', "'$binding'", $rawSql, 1);
        }

        dump(['Prepared Statement' => $sql, 'Bindings' => $bindings, 'Raw Statement' => $rawSql]);

        return $this;
    }

}
<?php

namespace Framework\Database\Grammars;

use Framework\Database\Query\Builder;

/**
 * Class MySqlGrammar
 *
 * This class, extending the Grammar base class, partially imported from Laravel's Illuminate\Database\Query\Grammars\MySqlGrammar, represents the query grammar specifically tailored for MySQL when utilizing the Query Builder in a PHP application.
 * It provides methods to compile various components of SQL statements, addressing MySQL-specific syntax and features.
 *
 * @package Framework\Database\Grammars [Laravel@Illuminate\Database\Query\Grammars\MySqlGrammar]
 */
class MySqlGrammar extends Grammar
{
    /**
     * Special operators supported by this grammar.
     *
     * @var array
     */
    protected $special_operators = ['sounds like'];

    /**
     * Overridden method handling the WHERE condition for null values.
     *
     * @param Builder $query
     * @param mixed $where
     * @return mixed
     */
    protected function whereNull(Builder $query, $where)
    {
        $columnValue = (string) $this->getValue($where['column']);

        return parent::whereNull($query, $where);
    }

    /**
     * Overridden method handling the WHERE condition for non-null values.
     *
     * @param Builder $query
     * @param mixed $where
     * @return mixed
     */
    protected function whereNotNull(Builder $query, $where)
    {
        $columnValue = (string) $this->getValue($where['column']);

        return parent::whereNotNull($query, $where);
    }

    /**
     * Method compiling the WHERE condition for full-text search.
     *
     * @param Builder $query
     * @param mixed $where
     * @return string
     */
    public function whereFullText(Builder $query, $where)
    {
        $columns = $this->columnize($where['columns']);

        $value = $this->parameter($where['value']);

        $mode = ($where['options']['mode'] ?? []) === 'boolean'
            ? ' in boolean mode'
            : ' in natural language mode';

        $expanded = ($where['options']['expanded'] ?? []) && ($where['options']['mode'] ?? []) !== 'boolean'
            ? ' with query expansion'
            : '';

        return "match ({$columns}) against (".$value."{$mode}{$expanded})";
    }

    /**
     * Method compiling the expression for the RAND function in MySQL.
     *
     * @param mixed $seed
     * @return string
     */
    public function compileRandom($seed)
    {
        return 'RAND('.$seed.')';
    }

    /**
     * Overridden method compiling the INSERT query.
     *
     * @param Builder $query
     * @param array $values
     * @return mixed
     */
    public function compileInsert(Builder $query, array $values)
    {
        if (empty($values)) {
            $values = [[]];
        }

        return parent::compileInsert($query, $values);
    }

    /**
     * Overridden method compiling the UPDATE query without using JOIN.
     *
     * @param Builder $query
     * @param mixed $table
     * @param mixed $columns
     * @param mixed $where
     * @return string
     */
    protected function compileUpdateWithoutJoins(Builder $query, $table, $columns, $where)
    {
        $sql = parent::compileUpdateWithoutJoins($query, $table, $columns, $where);

        if (!empty($query->orders)) {
            $sql .= ' '.$this->compileOrders($query, $query->orders);
        }

        if (isset($query->limit)) {
            $sql .= ' '.$this->compileLimit($query, $query->limit);
        }

        return $sql;
    }

    /**
     * Method preparing values for the UPDATE query.
     *
     * @param array $bindings
     * @param array $values
     * @return mixed
     */
    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $values = collect($values)->map(function ($value) {
            return is_array($value) ? json_encode($value) : $value;
        })->all();

        return parent::prepareBindingsForUpdate($bindings, $values);
    }

    /**
     * Overridden method compiling the DELETE query without using JOIN.
     *
     * @param Builder $query
     * @param mixed $table
     * @param mixed $where
     * @return string
     */
    protected function compileDeleteWithoutJoins(Builder $query, $table, $where)
    {
        $sql = parent::compileDeleteWithoutJoins($query, $table, $where);

        // When using MySQL, delete statements may contain order by statements and limits
        // so we will compile both of those here. Once we have finished compiling this
        // we will return the completed SQL statement so it will be executed for us.
        if (! empty($query->orders)) {
            $sql .= ' '.$this->compileOrders($query, $query->orders);
        }

        if (isset($query->limit)) {
            $sql .= ' '.$this->compileLimit($query, $query->limit);
        }

        return $sql;
    }

    /**
     * Method for wrapping a value to be used in SQL statements.
     *
     * @param mixed $value
     * @return string
     */
    protected function wrapValue($value)
    {
        return $value === '*' ? $value : '`'.str_replace('`', '``', $value).'`';
    }
}

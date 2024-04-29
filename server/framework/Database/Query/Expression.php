<?php

namespace Framework\Database\Query;

use Framework\Database\Grammars\Grammar;

/**
 * Class Expression
 *
 * This class represents a raw query expression, allowing for the inclusion
 * of custom SQL expressions in queries. It holds the value of the expression
 * and provides a method to retrieve the expression value, applying any necessary
 * formatting using the specified grammar.
 *
 * @package Framework\Database\Query
 */
class Expression
{
    /**
     * The value of the expression.
     *
     * @var string|int|float
     */
    protected $value;

    /**
     * Create a new raw query expression.
     *
     * @param  string|int|float  $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the value of the expression.
     *
     * @param  \Framework\Database\Grammars\Grammar  $grammar
     * @return string|int|float
     */
    public function getValue(Grammar $grammar)
    {
        return $this->value;
    }
}

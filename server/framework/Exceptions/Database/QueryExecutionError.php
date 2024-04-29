<?php

namespace Framework\Exceptions\Database;

use RuntimeException;
use Throwable;

class QueryExecutionError extends RuntimeException
{
    private $context = [];

    public function __construct($message = "Query execution error",  $context = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function context() {
        return $this->context;
    }
}
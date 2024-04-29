<?php

namespace Framework\Exceptions\Filesystem;

use RuntimeException;
use Throwable;

class ResourceNotFound extends RuntimeException
{
    private $context = [];

    public function __construct($message = "Resource not found.", $context = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function context() {
        return $this->context;
    }
}
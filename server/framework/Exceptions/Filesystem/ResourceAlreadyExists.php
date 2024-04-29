<?php

namespace Framework\Exceptions\Filesystem;

use RuntimeException;
use Throwable;

class ResourceAlreadyExists extends RuntimeException
{
    private $context = [];

    public function __construct($message = "Resource already exists.", $context = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function context() {
        return $this->context;
    }
}
<?php

namespace Framework\Exceptions\Filesystem;

use RuntimeException;
use Throwable;

class ResourceOutsideScope extends RuntimeException
{
    private $context = [];

    public function __construct($message = "Resource is outside scope.", $context = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function context() {
        return $this->context;
    }
}
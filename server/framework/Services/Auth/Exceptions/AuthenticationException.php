<?php

namespace Framework\Services\Auth\Exceptions;

use RuntimeException;
use Throwable;

class AuthenticationException extends RuntimeException
{
    private $context = [];

    public function __construct($message = "Unauthenticated.", $context = [], $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function context() {
        return $this->context;
    }
}

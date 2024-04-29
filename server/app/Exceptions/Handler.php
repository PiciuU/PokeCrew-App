<?php

namespace App\Exceptions;

use Framework\Exceptions\ExceptionHandler;

/**
 * Class Handler
 *
 * The Handler class extends the base ExceptionHandler and provides custom error handling logic.
 * It overrides the register method to handle exceptions and report them as needed.
 *
 * @package App\Exceptions
 */
class Handler extends ExceptionHandler {

    /**
     * Register and handle the given exception.
     *
     * @param \Throwable $e The exception to handle.
     */
    public function register(\Throwable $e): void
    {
        // Call the reportable method to handle the exception
        $this->reportable($e);
    }
}
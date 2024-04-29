<?php

namespace Framework\Services\Validator\Exceptions;

use Exception;

/**
 * Class ValidationFailed
 *
 * The ValidationFailed exception is thrown when a validation process fails. It includes information about the failed
 * validation, such as error messages and the associated validator instance.
 *
 * @package Framework\Services\Validator\Exceptions
 */
class ValidationFailed extends Exception
{
    /**
     * The status code to use for the response.
     *
     * @var int
     */
    public $status = 422;

    /**
     * The recommended response to send to the client.
     *
     * @var \Framework\Http\Response|null
     */
    public $response;

    /**
     * The validator instance.
     *
     * @var \Framework\Services\Validator\Validator
     */
    public $validator;

    /**
     * ValidationFailed constructor.
     *
     * @param \Framework\Services\Validator\Validator $validator The validator instance.
     * @param \Framework\Http\Response|null $response The recommended response to send to the client.
     */
    public function __construct($validator, $response = null)
    {
        parent::__construct(static::summarize($validator));

        $this->response = $response;
        $this->validator = $validator;
    }

    /**
     * Summarize the validation errors into a human-readable message.
     *
     * @param \Framework\Services\Validator\Validator $validator The validator instance.
     * @return string The summarized validation error message.
     */
    protected static function summarize($validator)
    {
        $messages = array_reduce($validator->getMessages(), 'array_merge', []);

        if (!count($messages) || !is_string($messages[0])) {
            return 'The given data was invalid.';
        }

        $message = array_shift($messages);

        if ($count = count($messages)) {
            $pluralized = $count === 1 ? 'error' : 'errors';

            $message .= " (and {$count} more {$pluralized})";
        }

        return $message;
    }

    /**
     * Get the detailed validation error messages.
     *
     * @return array The detailed validation error messages.
     */
    public function errors()
    {
        return $this->validator->getMessages();
    }

    /**
     * Set the HTTP status code for the response.
     *
     * @param int $status The HTTP status code.
     * @return $this
     */
    public function status($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the recommended response to send to the client.
     *
     * @return \Framework\Http\Response|null The recommended response.
     */
    public function getResponse()
    {
        return $this->response;
    }
}
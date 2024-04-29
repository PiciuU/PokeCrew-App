<?php

namespace Framework\Services\Validator;

use Framework\Support\Arr;

/**
 * Class ValidatedInput
 *
 * The ValidatedInput class represents a set of input data that has been validated.
 * It provides methods for retrieving specific input values based on inclusion or exclusion of certain keys.
 *
 * @package Framework\Services\Validator
 */
class ValidatedInput
{
    /**
     * The input data that has been validated.
     *
     * @var array
     */
    protected $input;

    /**
     * Create a new ValidatedInput instance.
     *
     * @param array $input The input data to be validated.
     */
    public function __construct(array $input)
    {
        $this->input = $input;
    }

    /**
     * Get only the specified keys from the validated input.
     *
     * @param array|string $keys The keys to include in the result.
     * @return array The subset of input data containing only the specified keys.
     */
    public function only(array|string $keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return Arr::only($this->input, $keys);
    }

    /**
     * Get all input data except the specified keys.
     *
     * @param array|string $keys The keys to exclude from the result.
     * @return array The subset of input data excluding the specified keys.
     */
    public function except(array|string $keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return Arr::except($this->input, $keys);
    }

    /**
     * Dynamically access input data.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->input[$name];
    }

    /**
     * Dynamically set input data.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        $this->input[$name] = $value;
    }
}
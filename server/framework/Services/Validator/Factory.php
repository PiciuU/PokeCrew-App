<?php

namespace Framework\Services\Validator;

/**
 * Class Factory
 *
 * The Factory class is responsible for creating instances of the Validator class and providing a convenient interface
 * for validating data against specified rules.
 *
 * @package Framework\Services\Validator
 */
class Factory
{
    /**
     * Create a new Validator instance.
     *
     * @param array $data The data to be validated.
     * @param array $rules The rules to be applied for validation.
     * @param array $messages Custom error messages for validation failures.
     * @param array $attributes Custom attribute names for error messages.
     * @return \Framework\Services\Validator\Validator The created Validator instance.
     */
    public function make(array $data, array $rules, array $messages = [], array $attributes = [])
    {
        $validator = $this->resolve($data, $rules, $messages, $attributes);

        return $validator;
    }

    /**
     * Resolve a new Validator instance.
     *
     * @param array $data The data to be validated.
     * @param array $rules The rules to be applied for validation.
     * @param array $messages Custom error messages for validation failures.
     * @param array $attributes Custom attribute names for error messages.
     * @return \Framework\Services\Validator\Validator The created Validator instance.
     */
    public function resolve(array $data, array $rules, array $messages, array $attributes)
    {
        return new Validator($data, $rules, $messages, $attributes);
    }

    /**
     * Validate data against specified rules using a new Validator instance.
     *
     * @param array $data The data to be validated.
     * @param array $rules The rules to be applied for validation.
     * @param array $messages Custom error messages for validation failures.
     * @param array $attributes Custom attribute names for error messages.
     * @return bool Returns true if validation passes, false otherwise.
     */
    public function validate(array $data, array $rules, array $messages = [], array $attributes = [])
    {
        return $this->make($data, $rules, $messages, $attributes)->validate();
    }
}
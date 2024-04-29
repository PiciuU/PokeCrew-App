<?php

namespace Framework\Services\Validator;

/**
 * Class Rule
 *
 * The Rule class provides static methods for generating specific validation rules commonly used in the Validator class.
 *
 * @package Framework\Services\Validator
 */
class Rule
{
    /**
     * Generate an "in" validation rule.
     *
     * @param array|string ...$values The values to be included in the "in" rule.
     * @return string The generated "in" validation rule.
     */
    public static function in($values)
    {
        $values = array_map(fn ($value) =>  '"'.str_replace('"', '""', $value).'"', is_array($values) ? $values : func_get_args());

        return 'in:'.implode(',', $values);
    }

    /**
     * Generate a "not_in" validation rule.
     *
     * @param array|string ...$values The values to be excluded from the "not_in" rule.
     * @return string The generated "not_in" validation rule.
     */
    public static function notIn($values)
    {
        $values = array_map(fn ($value) =>  '"'.str_replace('"', '""', $value).'"', is_array($values) ? $values : func_get_args());

        return 'not_in:'.implode(',', $values);
    }
}
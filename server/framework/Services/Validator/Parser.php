<?php

namespace Framework\Services\Validator;

use Framework\Support\Str;
use Framework\Support\Arr;

/**
 * Class Parser
 *
 * The Parser class is responsible for parsing validation rules and converting them into a standardized format
 * for use by the Validator class.
 *
 * @package Framework\Services\Validator
 */
class Parser
{
    /**
     * Parse the given validation rule.
     *
     * @param mixed $rule The validation rule to be parsed.
     * @return array The parsed validation rule in a standardized format.
     */
    public static function parse($rule)
    {
        if (is_array($rule)) {
            $rule = static::parseArrayRule($rule);
        } else {
            $rule = static::parseStringRule($rule);
        }

        $rule[0] = static::normalizeRule($rule[0]);

        return $rule;
    }

    /**
     * Parse an array-based validation rule.
     *
     * @param array $rule The array-based validation rule.
     * @return array The parsed validation rule in a standardized format.
     */
    protected static function parseArrayRule(array $rule)
    {
        return [Str::studly(trim(Arr::get($rule, 0, ''))), array_slice($rule, 1)];
    }

    /**
     * Parse a string-based validation rule.
     *
     * @param string $rule The string-based validation rule.
     * @return array The parsed validation rule in a standardized format.
     */
    protected static function parseStringRule($rule)
    {
        $parameters = [];

        if (str_contains($rule, ':')) {
            [$rule, $parameter] = explode(':', $rule, 2);

            $parameters = static::parseParameters($rule, $parameter);
        }

        return [Str::studly(trim($rule)), $parameters];
    }

    /**
     * Normalize the given validation rule.
     *
     * @param string $rule The validation rule to be normalized.
     * @return string The normalized validation rule.
     */
    protected static function normalizeRule($rule)
    {
        return match ($rule) {
            'Int' => 'Integer',
            'Bool' => 'Boolean',
            default => $rule,
        };
    }

   /**
     * Parse the parameters for the given validation rule.
     *
     * @param string $rule The validation rule.
     * @param string $parameter The parameter string associated with the rule.
     * @return array The parsed parameters.
     */
    protected static function parseParameters($rule, $parameter)
    {
        return static::ruleIsRegex($rule) ? [$parameter] : str_getcsv($parameter);
    }

    /**
     * Check if the given validation rule is a regular expression rule.
     *
     * @param string $rule The validation rule to check.
     * @return bool Returns true if the rule is a regular expression rule, false otherwise.
     */
    protected static function ruleIsRegex($rule)
    {
        return in_array(strtolower($rule), ['regex', 'not_regex', 'notregex'], true);
    }
}
<?php

namespace Framework\Services\Validator\Traits;

use Framework\Support\Str;

/**
 * Trait FailureMessages
 *
 * The FailureMessages trait provides methods for generating failure messages for validation rules.
 * It allows customization of failure messages based on the provided validation rules, attributes, and parameters.
 *
 * @package Framework\Services\Validator\Traits
 */
trait FailureMessages
{
    /**
     * The array containing localized failure messages for various validation rules.
     *
     * @var array
     */
    protected $locale = [
        'array' => 'The :attribute field must be an array.',
        'boolean' => 'The :attribute field must be true or false.',
        'confirmed' => 'The :attribute field confirmation does not match.',
        'decimal' => 'The :attribute field must have :decimal decimal places.',
        'email' => 'The :attribute field must be a valid email address.',
        'ends_with' => 'The :attribute field must end with one of the following: :ends_with.',
        'file' => 'The :attribute field must be a file.',
        'gt' => 'The :attribute field must be greater than :gt.',
        'gte' => 'The :attribute field must be greater than or equal to :gte.',
        'integer' => 'The :attribute field must be an integer.',
        'in' => 'The selected :attribute is invalid.',
        'ip' => 'The :attribute field must be a valid IP address.',
        'lowercase' => 'The :attribute field must be lowercase.',
        'lt' => 'The :attribute field must be less than :lt.',
        'lte' => 'The :attribute field must be less than or equal to :lte.',
        'max' => [
            'array' => 'The :attribute field must not contain more than :max items.',
            'file' => 'The :attribute field must not exceed :max kilobytes.',
            'numeric' => 'The :attribute field must not exceed :max.',
            'string' => 'The :attribute field must not exceed :max characters.',
            'default' => 'The :attribute field must not exceed :max in size.'
        ],
        'min' => [
            'array' => 'The :attribute field must contain at least :min items.',
            'file' => 'The :attribute field must be at least :min kilobytes.',
            'numeric' => 'The :attribute field must be at least :min.',
            'string' => 'The :attribute field must be at least :min characters.',
            'default' => 'The :attribute field must be at least :min in size.'
        ],
        'not_in' => 'The selected :attribute is invalid.',
        'not_regex' => 'The :attribute field format is invalid.',
        'numeric' => 'The :attribute field must be a number.',
        'regex' => 'The :attribute field format is invalid.',
        'required' => 'The :attribute field is required.',
        'same' => 'The :attribute field must match :same.',
        'size' => [
            'array' => 'The :attribute field must contain :size items.',
            'file' => 'The :attribute field must be :size kilobytes.',
            'numeric' => 'The :attribute field must be :size.',
            'string' => 'The :attribute field must be :size characters.',
            'default' => 'The :attribute field must be :size in size.'
        ],
        'starts_with' => 'The :attribute field must start with one of the following: :starts_with.',
        'string' => 'The :attribute field must be a string.',
        'uppercase' => 'The :attribute field must be uppercase.',
    ];

    /**
     * Get the failure message for a specific attribute, rule, and parameters.
     *
     * @param string $attribute The attribute being validated.
     * @param string $rule The validation rule being applied.
     * @param array $parameters The parameters associated with the rule.
     * @return string The generated failure message.
     */
    public function getFailureMessage($attribute, $rule, $parameters = [])
    {
        $lowerRule = Str::snake($rule);

        $message = $this->getRootMessage($attribute, $lowerRule);
        $message = $this->replaceAttributePlaceholder($message, $attribute);
        if (!empty($parameters)) $message = $this->replaceParameterPlaceholder($message, $lowerRule, $parameters);

        return $message;
    }

    /**
     * Get the root failure message for a specific attribute and rule.
     *
     * @param string $attribute The attribute being validated.
     * @param string $rule The validation rule being applied.
     * @return string The root failure message.
     */
    protected function getRootMessage($attribute, $rule)
    {
        $message = $this->locale[$rule] ?? 'Failure message for "'.$rule.'" rule is not defined.';

        if (is_array($message)) {
            $keys = array_keys($message);
            $rules = $this->getRules()[$attribute];

            foreach($keys as &$key) {
                $key = Str::ucfirst($key);
            }

            $matchedRule = $this->hasRule($attribute, $keys) ? $this->getRule($attribute, $keys)[0] : 'Default';

            $message = $message[Str::snake($matchedRule)];
        }

        return $message;
    }

    /**
     * Replace the attribute placeholder in the failure message.
     *
     * @param string $message The failure message.
     * @param string $attribute The attribute being validated.
     * @return string The updated failure message.
     */
    protected function replaceAttributePlaceholder($message, $attribute)
    {
        return str_replace(
            [':attribute', ':ATTRIBUTE', ':Attribute'],
            [$attribute, Str::upper($attribute), Str::ucfirst($attribute)],
            $message
        );
    }

    /**
     * Replace the parameter placeholder in the failure message.
     *
     * @param string $message The failure message.
     * @param string $rule The validation rule being applied.
     * @param array $parameters The parameters associated with the rule.
     * @return string The updated failure message.
     */
    protected function replaceParameterPlaceholder($message, $rule, $parameters)
    {
        $parameter = $this->parseParameter($rule, $parameters);
        return str_replace(":$rule", $parameter, $message);
    }

    /**
     * Parse the parameter based on the validation rule.
     *
     * @param string $rule The validation rule being applied.
     * @param array $parameters The parameters associated with the rule.
     * @return mixed The parsed parameter.
     */
    protected function parseParameter($rule, $parameters)
    {
        if ($rule === 'decimal') return implode(' to ', $parameters);
        else if ($rule === 'ends_with' || $rule === 'starts_with') return implode(', ', $parameters);
        return $parameters[0];
    }
}
<?php

namespace Framework\Services\Validator;

use Framework\Services\Validator\Exceptions\ValidationFailed;
use Framework\Support\Arr;
use stdClass;

/**
 * Class Validator
 *
 * The Validator class is responsible for validating input data based on specified rules.
 * It uses a set of validation rules, custom messages, and attributes for the validation process.
 *
 * @package Framework\Services\Validator
 */
class Validator
{
    use Traits\ValidatesAttributes, Traits\FailureMessages;

    /**
     * The custom error messages for validation failures.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The input data to be validated.
     *
     * @var array
     */
    protected $data;

    /**
     * The initial set of validation rules.
     *
     * @var array
     */
    protected $initialRules;

    /**
     * The processed validation rules.
     *
     * @var array
     */
    protected $rules;

    /**
     * The array of custom error messages.
     *
     * @var array
     */
    public $customMessages = [];

    /**
     * The array of custom attribute names.
     *
     * @var array
     */
    public $customAttributes = [];

    /**
     * The failed validation rules.
     *
     * @var array
     */
    protected $failedRules = [];

    /**
     * The exception to throw upon failure.
     *
     * @var string
     */
    protected $exception = ValidationFailed::class;

    /**
     * Create a new Validator instance.
     *
     * @param array $data The input data to be validated.
     * @param array $rules The validation rules to be applied.
     * @param array $messages Custom error messages for validation failures.
     * @param array $attributes Custom attribute names for validation fields.
     */
    public function __construct(array $data, array $rules, array $messages = [], array $attributes = [])
    {
        $this->data = $this->parseData($data);
        $this->initialRules = $rules;
        $this->customMessages = $messages;
        $this->customAttributes = $attributes;

        $this->setRules($rules);
    }

    /**
     * Parse input data, converting empty strings to null.
     *
     * @param array $data The input data to be parsed.
     * @return array The parsed input data.
     */
    public function parseData(array $data)
    {
        $newData = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->parseData($value);
            }

            if (is_string($value) && $value == '') {
                $value = null;
            }

            $newData[$key] = $value;
        }

        return $newData;
    }

    /**
     * Set the validation rules for the Validator instance.
     *
     * @param array $rules The validation rules to be set.
     * @return $this
     */
    public function setRules(array $rules)
    {
        $rules = collect($rules)->toArray();

        $this->initialRules = $rules;

        $this->rules = [];

        $this->addRules($rules);

        return $this;
    }

    /**
     * Add additional validation rules to the existing set of rules.
     *
     * @param array $rules The additional validation rules to be added.
     */
    public function addRules($rules)
    {
        foreach ($rules as $field => $rule) {
            if (is_array($rule)) {
                // Fix for regex
                $rule = implode('|', str_replace('|', '__PIPE__', $rule));
            }

            $rulesArray = explode('|', $rule);

            $rulesArray = array_map(function ($item) {
                return str_replace('__PIPE__', '|', $item);
            }, $rulesArray);

            $this->rules[$field] = $rulesArray;

        }
    }

    /**
     * Check if a given attribute has a specific validation rule.
     *
     * @param string $attribute The attribute to check for the rule.
     * @param string|array $rules The rule or rules to check.
     * @return bool Whether the attribute has the specified rule.
     */
    public function hasRule($attribute, $rules)
    {
        return !is_null($this->getRule($attribute, $rules));
    }

    /**
     * Get the validation rule for a given attribute among a set of rules.
     *
     * @param string $attribute The attribute to check for the rule.
     * @param string|array $rules The rule or rules to check.
     * @return array|null The matched rule and its parameters, or null if not found.
     */
    protected function getRule($attribute, $rules)
    {
        if (!array_key_exists($attribute, $this->rules)) {
            return;
        }

        $rules = (array) $rules;

        foreach ($this->rules[$attribute] as $rule) {
            [$rule, $parameters] = Parser::parse($rule);

            if (in_array($rule, $rules)) {
                return [$rule, $parameters];
            }
        }
    }

    /**
     * Validate the input data against the defined rules. If validation fails, throw a ValidationFailed exception
     * containing detailed error messages.
     *
     * @return \Framework\Services\Validator\ValidatedInput|array The validated input data, or throw a ValidationFailed exception on failure.
     * @throws \Framework\Services\Validator\Exceptions\ValidationFailed Thrown when validation fails, containing detailed error messages.
     */
    public function validate()
    {
        throw_if($this->fails(), $this->exception, $this);

        return $this->validated();
    }

    /**
     * Throw a ValidationFailed exception containing detailed error messages based on the failed validation.
     *
     * @throws \Framework\Services\Validator\Exceptions\ValidationFailed Thrown when validation fails, containing detailed error messages.
     */
    public function throw()
    {
        throw new ValidationFailed($this);
    }

    /**
     * Check if validation failed.
     *
     * @return bool Whether validation failed.
     */
    public function fails()
    {
        return !$this->passes();
    }

    /**
     * Check if validation passes.
     *
     * @return bool Whether validation passes.
     */
    public function passes()
    {
        $this->messages = [];

        foreach ($this->rules as $attribute => $rules) {
            foreach ($rules as $rule) {
                $this->validateAttribute($attribute, $rule);

                if ($this->shouldStopValidating($attribute)) {
                    break;
                }
            }
        }

        return empty($this->messages);
    }


    /**
     * Check if should stop further validations on a given attribute.
     *
     * @param  string  $attribute The attribute to check for the failed rules.
     * @return bool Whether validation should stop.
     */
    protected function shouldStopValidating($attribute)
    {
        return isset($this->failedRules[$attribute]);
    }

    /**
     * Validate a specific attribute based on the specified rule.
     *
     * @param string $attribute The attribute to validate.
     * @param string $rule The rule to apply.
     */
    protected function validateAttribute($attribute, $rule)
    {
        [$rule, $parameters] = Parser::parse($rule);

        if ($rule === '') {
            return;
        }

        $value = $this->getValue($attribute);

        $validatable = $this->isValidatable($rule, $attribute, $value);

        $method = "validate{$rule}";

        if ($validatable && !$this->$method($attribute, $value, $parameters, $this)) {
            $this->addFailure($attribute, $rule, $parameters);
        }
    }

    /**
     * Check if an attribute is validatable.
     *
     * @param string $rule The rule to check.
     * @param string $attribute The attribute to validate.
     * @param mixed $value The value of the attribute.
     * @return bool Whether the attribute is validatable.
     */
    protected function isValidatable($rule, $attribute, $value)
    {
        return $this->passesOptionalCheck($attribute) && $this->isNotNullIfMarkedAsNullable($rule, $attribute);
    }

    /**
     * Check if a rule should not be applied if the attribute is marked as nullable.
     *
     * @param string $rule The rule to check.
     * @param string $attribute The attribute to validate.
     * @return bool Whether the rule should not be applied if the attribute is nullable.
     */
    protected function isNotNullIfMarkedAsNullable($rule, $attribute)
    {
        if (!$this->hasRule($attribute, ['Nullable'])) {
            return true;
        }

        return !is_null(Arr::get($this->data, $attribute, 0));
    }

    /**
     * Check if an attribute passes the optional check.
     *
     * @param string $attribute The attribute to check.
     * @return bool Whether the optional check passes.
     */
    protected function passesOptionalCheck($attribute)
    {
        if (!$this->hasRule($attribute, ['Sometimes'])) {
            return true;
        }

        return array_key_exists($attribute, $this->data);
    }

    /**
     * Add a failure message for a specific attribute and rule.
     *
     * @param string $attribute The attribute that failed validation.
     * @param string $rule The rule that caused the failure.
     * @param array $parameters The parameters for the rule.
     */
    public function addFailure($attribute, $rule, $parameters = [])
    {
        $this->failedRules[$attribute][] = $rule;
        if ($customMessage = $this->customMessages[strtolower($attribute).".".strtolower($rule)] ?? false) {
            $this->messages[$attribute][] = $customMessage;
        }
        else if ($customAttribute = $this->customAttributes[$attribute] ?? false) {
            $this->messages[$attribute][] = str_replace($attribute, $customAttribute, $this->getFailureMessage($attribute, $rule, $parameters));
        }
        else $this->messages[$attribute][] = $this->getFailureMessage($attribute, $rule, $parameters);
    }

    /**
     * Get the validated input data.
     *
     * @return array The validated input data.
     */
    public function validated()
    {
        $results = [];

        $missingValue = new stdClass;

        foreach($this->getRules() as $key => $rules) {
            $value = data_get($this->getData(), $key, $missingValue);

            if ($value !== $missingValue) {
                Arr::set($results, $key, $value);
            }
        }

        return $results;
    }

    /**
     * Get a subset of the validated input data containing only the specified keys.
     *
     * @param array $keys The keys to include in the result.
     * @return ValidatedInput|array The subset of input data containing only the specified keys.
     */
    public function safe(array $keys = null)
    {
        return is_array($keys) ? (new ValidatedInput($this->validated()))->only($keys) : new ValidatedInput($this->validated());
    }

    /**
     * Get the original input data.
     *
     * @return array The original input data.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the validation failure messages.
     *
     * @return array The validation failure messages.
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get the validation rules.
     *
     * @return array The validation rules.
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Get the value of a specific attribute from the input data.
     *
     * @param string $attribute The attribute to retrieve.
     * @return mixed The value of the attribute.
     */
    protected function getValue($attribute)
    {
        return Arr::get($this->data, $attribute);
    }

}
<?php

namespace Framework\Services\Validator\Traits;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Framework\Support\Arr;
use Framework\Support\Str;

/**
 * Trait ValidatesAttributes
 *
 * The ValidatesAttributes trait provides methods for validating various types of attributes.
 *
 * @package Framework\Services\Validator\Traits
 */
trait ValidatesAttributes
{
    /**
     * Validate that the attribute is present and not empty.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @return bool Whether the validation passes.
     */
    public function validateRequired($attribute, $value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif (is_countable($value) && count($value) < 1) {
            return false;
        } elseif ($value instanceof File) {
            return (string) $value->getPath() !== '';
        }

        return true;
    }

    /**
     * Validate that the attribute is a string.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @return bool Whether the validation passes.
     */
    public function validateString($attribute, $value)
    {
        return is_string($value);
    }

    /**
     * Validate that the attribute is a numeric value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @return bool Whether the validation passes.
     */
    public function validateNumeric($attribute, $value)
    {
        return is_numeric($value);
    }

    /**
     * Validate that the attribute is a boolean value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @return bool Whether the validation passes.
     */
    public function validateBoolean($attribute, $value)
    {
        $acceptable = [true, false, 0, 1, '0', '1', 'true', 'false'];

        return in_array($value, $acceptable, true);
    }

    /**
     * Validate that the attribute is an integer value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @return bool Whether the validation passes.
     */
    public function validateInteger($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate that the attribute is greater than a specified value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateGt($attribute, $value, $parameters)
    {
        return $this->validateNumeric($attribute, $value) && (float) $value > (float) ($this->getValue($parameters[0]) ?? $parameters[0]);
    }

    /**
     * Validate that the attribute is greater than or equal to a specified value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateGte($attribute, $value, $parameters)
    {
        return $this->validateNumeric($attribute, $value) && (float) $value >= (float) ($this->getValue($parameters[0]) ?? $parameters[0]);
    }

    /**
     * Validate that the attribute is less than a specified value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateLt($attribute, $value, $parameters)
    {
        return $this->validateNumeric($attribute, $value) && (float) $value < (float) ($this->getValue($parameters[0]) ?? $parameters[0]);
    }

    /**
     * Validate that the attribute is less than or equal to a specified value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateLte($attribute, $value, $parameters)
    {
        return $this->validateNumeric($attribute, $value) && (float) $value <= (float) ($this->getValue($parameters[0]) ?? $parameters[0]);
    }

    /**
     * Validate that the attribute is less than or equal to a specified value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateMax($attribute, $value, $parameters)
    {
        $value = $this->getSize($attribute, $value);
        return $this->validateNumeric($attribute, $value) && (float) $value <= (float) ($this->getValue($parameters[0]) ?? $parameters[0]);
    }

    /**
     * Validate that the attribute is greater than or equal to a specified value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateMin($attribute, $value, $parameters)
    {
        $value = $this->getSize($attribute, $value);
        return $this->validateNumeric($attribute, $value) && (float) $value >= (float) ($this->getValue($parameters[0]) ?? $parameters[0]);
    }

    /**
     * Validate that the attribute is a valid IP address.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @return bool Whether the validation passes.
     */
    public function validateIp($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate that the attribute consists only of uppercase letters.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateUppercase($attribute, $value, $parameters)
    {
        return Str::upper($value) === $value;
    }

    /**
     * Validate that the attribute consists only of lowercase letters.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateLowercase($attribute, $value, $parameters)
    {
        return Str::lower($value) === $value;
    }

    /**
     * Validate that the attribute is an array.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateArray($attribute, $value, $parameters = [])
    {
        if (!is_array($value)) {
            return false;
        }

        if (empty($parameters)) {
            return true;
        }

        return empty(array_diff_key($value, array_fill_keys($parameters, '')));
    }

    /**
     * Validate that the attribute is a valid email address.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @return bool Whether the validation passes.
     */
    public function validateEmail($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validation rule that always passes for sometimes.
     *
     * @return bool Always returns true.
     */
    public function validateSometimes()
    {
        return true;
    }

    /**
     * Validation rule that always passes for nullable.
     *
     * @return bool Always returns true.
     */
    public function validateNullable()
    {
        return true;
    }

    /**
     * Validate that the attribute size matches a given value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateSize($attribute, $value, $parameters)
    {
        return $this->isEqual($this->getSize($attribute, $value), trim($parameters[0]));
    }

    /**
     * Validate that the attribute value is confirmed by another attribute.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @return bool Whether the validation passes.
     */
    public function validateConfirmed($attribute, $value)
    {
        return $this->validateSame($attribute, $value, [$attribute.'_confirmation']);
    }

    /**
     * Validate that the attribute value is the same as another attribute.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateSame($attribute, $value, $parameters)
    {
        $other = Arr::get($this->data, $parameters[0]);

        return $value === $other;
    }

    /**
     * Validate that the attribute ends with a specified value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateEndsWith($attribute, $value, $parameters)
    {
        return Str::endsWith($value, $parameters);
    }

    /**
     * Validate that the attribute starts with a specified value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateStartsWith($attribute, $value, $parameters)
    {
        return Str::startsWith($value, $parameters);
    }

    /**
     * Validate that the attribute starts with a specified value.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateDecimal($attribute, $value, $parameters)
    {
        if (!$this->validateNumeric($attribute, $value)) {
            return false;
        }

        $matches = [];

        if (preg_match('/^[+-]?\d*\.?(\d*)$/', $value, $matches) !== 1) {
            return false;
        }

        $decimals = strlen(end($matches));

        if (! isset($parameters[1])) {
            return $decimals == $parameters[0];
        }

        return $decimals >= $parameters[0] &&
               $decimals <= $parameters[1];
    }

    /**
     * Validate that the attribute has a specific decimal places.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateFile($attribute, $value)
    {
        return $this->isValidFileInstance($value);
    }

    /**
     * Validate that the attribute is a file instance.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @return bool Whether the validation passes.
     */
    public function isValidFileInstance($value)
    {
        if ($value instanceof UploadedFile && ! $value->isValid()) {
            return false;
        }

        return $value instanceof File;
    }

    /**
     * Validate that the attribute matches a regular expression.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateRegex($attribute, $value, $parameters)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match($parameters[0], $value) > 0;
    }

    /**
     * Validate that the attribute does not match a regular expression.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateNotRegex($attribute, $value, $parameters)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match($parameters[0], $value) < 1;
    }

    /**
     * Validate that the attribute value is in a given set.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateIn($attribute, $value, $parameters)
    {
        if (is_array($value) && $this->hasRule($attribute, 'Array')) {
            foreach ($value as $element) {
                if (is_array($element)) {
                    return false;
                }
            }

            return count(array_diff($value, $parameters)) === 0;
        }

        return !is_array($value) && in_array((string) $value, $parameters);
    }

    /**
     * Validate that the attribute value is not in a given set.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @param array $parameters The validation parameters.
     * @return bool Whether the validation passes.
     */
    public function validateNotIn($attribute, $value, $parameters)
    {
        return !$this->validateIn($attribute, $value, $parameters);
    }

    /**
     * Get the size of the attribute.
     *
     * @param string $attribute The attribute name.
     * @param mixed $value The attribute value.
     * @return int The size of the attribute.
     */
    protected function getSize($attribute, $value)
    {
        if (is_numeric($value) && $hasNumeric) {
            return int ($value);
        } elseif (is_array($value)) {
            return count($value);
        } elseif ($value instanceof File) {
            return $value->getSize() / 1024;
        }

        return mb_strlen($value ?? '');
    }

    /**
     * Check if two values are equal.
     *
     * @param mixed $value The first value.
     * @param mixed $parameter The second value to compare.
     * @return bool Whether the values are equal.
     */
    protected function isEqual($value, $parameter)
    {
        return $value == $parameter;
    }

}
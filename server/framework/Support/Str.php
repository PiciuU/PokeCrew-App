<?php

namespace Framework\Support;

/**
 * Class Str
 *
 * The Str class provides string manipulation methods.
 *
 * @package Framework\Support
 */
class Str
{
    /**
     * Convert a string to snake case.
     *
     * @param  string  $value  The input string.
     * @param  string  $delimiter  The delimiter used between words (optional, default is '_').
     * @return string  The snake case formatted string.
     */
    public static function snake($value, $delimiter = '_')
    {
        $value = preg_replace('/\s+/u', '', ucwords($value));

        $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));

        return $value;
    }

    /**
     * Pluralize a string.
     *
     * @param  string  $value  The input string.
     * @return string  The pluralized string.
     */
    public static function plural($value)
    {
        if (substr($value, -1) !== 's') {
            $value .= 's';
        }

        return $value;
    }

    /**
     * Get the portion of a string after a given value.
     *
     * @param  string  $subject  The input string.
     * @param  string  $search  The value to search for.
     * @return string  The portion of the string after the given value.
     */
    public static function after($subject, $search)
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string  $value  The input string.
     * @param  string  $cap  The value to cap the string with.
     * @return string  The capped string.
     */
    public static function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:'.$quoted.')+$/u', '', $value).$cap;
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(fn ($word) => static::ucfirst($word), $words);

        return implode($studlyWords);
    }

    /**
     * Make a string's first character uppercase.
     *
     * @param  string  $string
     * @return string
     */
    public static function ucfirst($string)
    {
        return static::upper(static::substr($string, 0, 1)).static::substr($string, 1);
    }

    /**
     * Convert the given string to upper-case.
     *
     * @param  string  $value
     * @return string
     */
    public static function upper($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Returns the portion of the string specified by the start and length parameters.
     *
     * @param  string  $string
     * @param  int  $start
     * @param  int|null  $length
     * @param  string  $encoding
     * @return string
     */
    public static function substr($string, $start, $length = null, $encoding = 'UTF-8')
    {
        return mb_substr($string, $start, $length, $encoding);
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        if (! is_iterable($needles)) {
            $needles = [$needles];
        }

        foreach ($needles as $needle) {
            if ($haystack === null) return false;
            if ((string) $needle !== '' && str_starts_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        if (!is_iterable($needles)) {
            $needles = (array) $needles;
        }

        foreach ($needles as $needle) {
            if ($haystack === null) return false;
            if ((string) $needle !== '' && str_ends_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param  string  $value
     * @return string
     */
    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * @param  bool  $ignoreCase
     * @return bool
     */
    public static function contains($haystack, $needles, $ignoreCase = false)
    {
        if ($ignoreCase) {
            $haystack = mb_strtolower($haystack);
        }

        if (! is_iterable($needles)) {
            $needles = (array) $needles;
        }

        foreach ($needles as $needle) {
            if ($ignoreCase) {
                $needle = mb_strtolower($needle);
            }

            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int  $length
     * @return string
     */
    public static function random($length = 16)
    {
        return (function ($length) {
            $string = '';

            while (($len = strlen($string)) < $length) {
                $size = $length - $len;

                $bytesSize = (int) ceil($size / 3) * 3;

                $bytes = random_bytes($bytesSize);

                $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
            }

            return $string;
        })($length);
    }
}
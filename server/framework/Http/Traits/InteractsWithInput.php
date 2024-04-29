<?php

namespace Framework\Http\Traits;

use Framework\Support\Arr;

/**
 * Trait InteractsWithInput
 *
 * The InteractsWithInput trait provides methods for interacting with request input data.
 * It includes functionality to retrieve all input data, specific input keys, or the entire input array.
 *
 * @package Framework\Http\Traits
 */
trait InteractsWithInput
{
    /**
     * Get all input data or specific keys from the request.
     *
     * @param array|string|null $keys The keys to retrieve, or null to get all input data.
     * @return array The input data.
     */
    public function all($keys = null)
    {
        $input = array_replace_recursive($this->input(), $this->files->all());

        if (!$keys) {
            return $input;
        }

        $results = [];

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            Arr::set($results, $key, Arr::get($input, $key));
        }

        return $results;
    }

    /**
     * Get a specific input value from the request.
     *
     * @param string|null $key The key to retrieve, or null to get all input data.
     * @param mixed $default The default value if the key is not present.
     * @return mixed The input value.
     */
    public function input($key = null, $default = null)
    {
        return data_get(
            $this->getInputSource()->all() + $this->query->all(), $key, $default
        );
    }

    /**
     * Get the input source based on the request method.
     *
     * @return mixed The input source.
     */
    protected function getInputSource()
    {
        return in_array($this->getRealMethod(), ['GET', 'HEAD']) ? $this->query : $this->request;
    }

    /**
     * Retrieve a header from the request.
     *
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    public function header($key = null, $default = null)
    {
        return $this->retrieveItem('headers', $key, $default);
    }

    /**
     * Retrieve a parameter item from a given source.
     *
     * @param  string  $source
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    protected function retrieveItem($source, $key, $default)
    {
        if (is_null($key)) {
            return $this->$source->all();
        }

        return $this->$source->get($key, $default);
    }

    /**
     * Get the bearer token from the request headers.
     *
     * @return string|null
     */
    public function bearerToken()
    {
        $header = $this->header('Authorization', '');

        $position = strrpos($header, 'Bearer ');

        if ($position !== false) {
            $header = substr($header, $position + 7);

            return str_contains($header, ',') ? strstr($header, ',', true) : $header;
        }
    }
}
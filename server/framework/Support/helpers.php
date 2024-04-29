<?php

use Framework\Http\Container;

use Framework\Support\Env;

/**
 * Helper Function: env
 *
 * Retrieve the value of an environment variable.
 *
 * @param string $key     The name of the environment variable.
 * @param mixed  $default The default value to return if the environment variable is not set.
 *
 * @return mixed The value of the environment variable or the default value if not set.
 */
if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return Env::get($key, $default);
    }
}

/**
 * Helper Function: app
 *
 * Get the application instance (Singleton).
 *
 * @return Framework\Http\Application The application instance.
 */
if (!function_exists('app')) {
    function app($abstract = null, $parameters = []) {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract, $parameters);
    }
}

/**
 * Helper Function: router
 *
 * Get the router instance from the application.
 *
 * @return Framework\Http\Router The router instance.
 */
if (!function_exists('router')) {
    function router() {
        return app('route');
    }
}

/**
 * Helper Function: kernel
 *
 * Get the kernel instance from the application.
 *
 * @return Framework\Http\Kernel The kernel instance.
 */
if (!function_exists('kernel')) {
    function kernel() {
        return app('kernel');
    }
}

/**
 * Helper Function: request
 *
 * Get the current HTTP request instance from the application.
 *
 * @return Framework\Http\Request The current HTTP request instance.
 */
if (!function_exists('request')) {
    function request() {
        return kernel()->getRequest();
    }
}

/**
 * Helper Function: base_path
 *
 * Get the current HTTP request instance from the application.
 *
 * @return Framework\Http\Request The current HTTP request instance.
 */
if (!function_exists('base_path')) {
    function base_path($path = '') {
        return app()->basePath($path);
    }
}

/**
 * Helper Function: load
 *
 * Load a PHP file if it exists.
 *
 * @param string $filename The name of the PHP file to load.
 *
 * @throws Exception If the file does not exist.
 */
if (!function_exists('load')) {
    function load($filename) {
        if (!file_exists($filename))
            throw new Exception($filename.' does not exist.');
        else
            require_once($filename);
    }
}

/**
 * Helper Function: logger
 *
 * Get the exception handler logger instance from the application.
 *
 * @return Logger The exception handler logger instance.
 */
if (!function_exists('logger')) {
    function logger() {
        return app('handler');
    }
}

/**
 * Helper Function: config
 *
 * Get or set configuration values.
 *
 * @param string|array|null $key     The configuration key or an array of key-value pairs to set.
 * @param mixed            $default The default value to return if the key is not found (for get operations).
 *
 * @return mixed The configuration value or the config repository instance (for set operations).
 */
if (!function_exists('config')) {
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

/**
 * Helper Function: storage_path
 *
 * Get the path to the storage folder.
 *
 * @param  string  $path The additional path within the storage folder.
 * @return string The full path to the storage folder or a file within it.
 */
if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return app()->storagePath($path);
    }
}

/**
 * Helper Function: resource_path
 *
 * Get the path to the resources folder.
 *
 * @param  string  $path The additional path within the resources folder.
 * @return string The full path to the resources folder or a file within it.
 */
if (! function_exists('resource_path')) {
    function resource_path($path = '')
    {
        return app()->resourcePath($path);
    }
}

/**
 * Helper Function: throw_if
 *
 * Throw the given exception if the given condition is true.
 *
 * @template TException of \Throwable
 *
 * @param  mixed  $condition The condition to check.
 * @param  TException|class-string<TException>|string  $exception The exception to throw if the condition is true.
 * @param  mixed  ...$parameters Additional parameters for the exception constructor.
 * @return mixed The condition value.
 *
 * @throws TException
 */
if (!function_exists('throw_if')) {
    function throw_if($condition, $exception = 'RuntimeException', ...$parameters)
    {
        if ($condition) {
            if (is_string($exception) && class_exists($exception)) {
                $exception = new $exception(...$parameters);
            }

            throw is_string($exception) ? new RuntimeException($exception) : $exception;
        }

        return $condition;
    }
}

/**
 * Helper Function: class_basename
 *
 * Get the class "basename" of the given object or class name.
 *
 * @param  string|object  $class The class name or an object.
 * @return string The class basename.
 */
if (!function_exists('class_basename')) {
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

/**
 * Helper Function: view
 *
 * Get the evaluated view contents for the given view.
 *
 * @param  string|null  $view The name of the view.
 * @param  array  $data The data to pass to the view.
 * @param  array  $mergeData Additional data to merge with the view data.
 * @return \Framework\View\Factory|\Framework\View\View The evaluated view contents or the view factory instance.
 */
if (!function_exists('view')) {
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app('view');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

/**
 * Helper Function: e
 *
 * Encode HTML special characters in a string.
 *
 * @param string|null  $value The string to encode.
 * @param  bool  $doubleEncode Whether to double-encode existing entities.
 * @return string The encoded string.
 */
if (!function_exists('e')) {
    function e($value, $doubleEncode = true)
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }
}

/**
 * Helper Function: asset
 *
 * Generate an asset path for the application.
 *
 * @param  string  $path The path to the asset.
 * @param  bool|null  $secure Whether the asset should be secure (HTTPS).
 * @return string The generated asset path.
 */
if (!function_exists('asset')) {
    function asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
    }
}

/**
 * Helper Function: url
 *
 * Generate a url for the application.
 *
 * @param  string|null  $path
 * @param  mixed  $parameters
 * @param  bool|null  $secure
 * @return \Framework\Services\URL\UrlGenerator|string
 */
if (!function_exists('url')) {
    function url($path = null, $secure = null)
    {
        if (is_null($path)) {
            return app('url');
        }

        return app('url')->to($path, $secure);
    }
}

/**
 * Helper Function: validator
 *
 * Create a new Validator instance.
 *
 * @param  array  $data
 * @param  array  $rules
 * @param  array  $messages
 * @param  array  $attributes
 * @return \Framework\Services\Validator\Validator
 */
if (!function_exists('validator')) {
    function validator(array $data = [], array $rules = [], array $messages = [], array $attributes = [])
    {
        $factory = app('validator');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($data, $rules, $messages, $attributes);
    }
}

/**
 * Return a new response from the application.
 *
 * @param  \Framework\View\View|string|array|null  $content
 * @param  int  $status
 * @param  array  $headers
 * @return \Framework\Http\Routing\ResponseFactory|Framework\Http\Response|Framework\Http\JsonResponse;
 */
if (!function_exists('response')) {
    function response($content = '', $status = 200, array $headers = [])
    {
        $factory = app('response');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($content, $status, $headers);
    }
}

/**
 * Convert a given path to its absolute form.
 *
 * @param string $path The path to convert.
 * @return string The absolute path after conversion.
 */
if (!function_exists('absolute_path')) {
    function absolute_path($path)
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = explode(DIRECTORY_SEPARATOR, $path);

        $absolutes = [];
        foreach ($parts as $part) {
            if ($part === '.' || $part === '') {
                continue;
            }

            if ($part === '..') {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        $absolutePath = implode(DIRECTORY_SEPARATOR, $absolutes);

        if (PHP_OS_FAMILY !== "Windows") {
            $absolutePath = DIRECTORY_SEPARATOR . $absolutePath;
        }

        return $absolutePath;
    }
}
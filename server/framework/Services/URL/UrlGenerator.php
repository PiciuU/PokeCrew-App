<?php

namespace Framework\Services\URL;

use Framework\Support\Str;

/**
 * Class UrlGenerator
 *
 * The UrlGenerator class provides methods for generating URLs within the application.
 * It helps in building links in templates, API responses, or when generating redirect responses.
 *
 * @package Framework\Services\URL
 */
class UrlGenerator
{
    /**
     * The request instance.
     *
     * @var \Framework\Http\Request
     */
    protected $request;

    /**
     * The base URL for the application.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * UrlGenerator constructor.
     */
    public function __construct()
    {
        $this->request = request();
        $this->baseUrl = config('app.url');
    }

    /**
     * Get the URL for the current request.
     *
     * @return string  The formatted URL.
     */
    public function current()
    {
        return $this->to($this->request->getPathInfo());
    }

    /**
     * Generate a fully qualified URL to the given path.
     *
     * @param  string  $path  The path to be appended to the root URL.
     * @param  bool|null  $secure  The secure flag indicating whether the URL should use HTTPS (optional).
     * @return string  The formatted URL.
     */
    public function to($path, $secure = null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $root = $this->formatRoot($this->formatScheme($secure));

        [$path, $query] = $this->extractQueryString($path);

        return $this->format(
            $root, '/'.trim($path.'/', '/')
        ).$query;
    }

    /**
     * Generate a URL for an asset using the appropriate scheme.
     *
     * @param  string  $path  The path to the asset.
     * @param  string|null  $assetRoot  The root URL for assets (optional).
     * @param  bool|null  $secure  The secure flag indicating whether the URL should use HTTPS (optional).
     * @return string  The formatted URL for the asset.
     */
    public function asset($path, $assetRoot = null, $secure = null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $root = $assetRoot ?: $this->formatRoot($this->formatScheme($secure));

        return Str::finish($this->removeIndex($root), '/').trim($path, '/');
    }

    /**
     * Check if the given path is a valid URL.
     *
     * @param  string  $path  The path to be checked.
     * @return bool  True if the path is a valid URL, false otherwise.
     */
    public function isValidUrl($path)
    {
        if (! preg_match('~^(#|//|https?://|(mailto|tel|sms):)~', $path)) {
            return filter_var($path, FILTER_VALIDATE_URL) !== false;
        }

        return true;
    }

    /**
     * Format the URL by combining the root and path, ensuring correct formatting.
     *
     * @param  string  $root  The root URL.
     * @param  string  $path  The path to be appended to the root URL.
     * @param  string|null  $route  Additional route information (optional).
     * @return string  The formatted URL.
     */
    public function format($root, $path, $route = null)
    {
        $path = '/'.trim($path, '/');

        return trim($root.$path, '/');
    }

    /**
     * Format the root URL with the given scheme.
     *
     * @param  bool|null  $secure  The secure flag indicating whether the URL should use HTTPS (optional).
     * @param  string|null  $root  The root URL for the application (optional).
     * @return string  The formatted root URL.
     */
    public function formatScheme($secure = null)
    {
        if (!is_null($secure)) {
            return $secure ? 'https://' : 'http://';
        }

        return request()->getScheme().'://';
    }

    /**
     * Format the root URL with the given scheme and root.
     *
     * @param  string  $scheme  The URL scheme.
     * @param  string|null  $root  The root URL for the application (optional).
     * @return string  The formatted root URL.
     */
    public function formatRoot($scheme, $root = null)
    {
        if (is_null($root)) {
            $root = $this->baseUrl;
        }

        $start = str_starts_with($root, 'http://') ? 'http://' : 'https://';

        return preg_replace('~'.$start.'~', $scheme, $root, 1);
    }

    /**
     * Remove the "index.php" segment from the root URL.
     *
     * @param  string  $root  The root URL.
     * @return string  The root URL with the "index.php" segment removed.
     */
    protected function removeIndex($root)
    {
        $i = 'index.php';

        return str_contains($root, $i) ? str_replace('/'.$i, '', $root) : $root;
    }

    /**
     * Extract the query string from the given path.
     *
     * @param  string  $path  The path containing the query string.
     * @return array  An array containing the path without the query string and the extracted query string.
     */
    protected function extractQueryString($path)
    {
        if (($queryPosition = strpos($path, '?')) !== false) {
            return [
                substr($path, 0, $queryPosition),
                substr($path, $queryPosition),
            ];
        }

        return [$path, ''];
    }

}
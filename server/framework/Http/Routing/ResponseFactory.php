<?php

namespace Framework\Http\Routing;

use Framework\Http\JsonResponse;
use Framework\Http\Response;

use Framework\Support\Facades\View;

/**
 * Class ResponseFactory
 *
 * The ResponseFactory class provides methods for creating various HTTP responses.
 *
 * @package Framework\Http\Routing
 */
class ResponseFactory
{
    /**
     * Create a new response instance.
     *
     * @param string $content The content of the response.
     * @param int $status The HTTP status code.
     * @param array $headers The HTTP headers.
     * @return Response The created response instance.
     */
    public function make($content = '', $status = 200, array $headers = [])
    {
        return new Response($content, $status, $headers);
    }

    /**
     * Create a new "no content" response instance.
     *
     * @param int $status The HTTP status code.
     * @param array $headers The HTTP headers.
     * @return Response The created response instance.
     */
    public function noContent($status = 204, array $headers = [])
    {
        return $this->make('', $status, $headers);
    }

    /**
     * Create a new view response instance.
     *
     * @param string $view The name of the view.
     * @param array $data The data to pass to the view.
     * @param int $status The HTTP status code.
     * @param array $headers The HTTP headers.
     * @return Response The created response instance.
     */
    public function view($view, $data = [], $status = 200, array $headers = [])
    {
        return $this->make(View::make($view, $data), $status, $headers);
    }

    /**
     * Create a new JSON response instance.
     *
     * @param array $data The JSON data.
     * @param int $status The HTTP status code.
     * @param array $headers The HTTP headers.
     * @param int $options The JSON encoding options.
     * @return JsonResponse The created JSON response instance.
     */
    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        return new JsonResponse($data, $status, $headers, $options);
    }

    /**
     * Create a new JSONP response instance.
     *
     * @param string $callback The JSONP callback function name.
     * @param array $data The JSON data.
     * @param int $status The HTTP status code.
     * @param array $headers The HTTP headers.
     * @param int $options The JSON encoding options.
     * @return JsonResponse The created JSONP response instance.
     */
    public function jsonp($callback, $data = [], $status = 200, array $headers = [], $options = 0)
    {
        return $this->json($data, $status, $headers, $options)->setCallback($callback);
    }
}

<?php

namespace Framework\Exceptions;

use Throwable;

/**
 * Class ExceptionRenderer
 *
 * The ExceptionRenderer class is responsible for rendering error pages in response to exceptions and HTTP status codes.
 * It provides methods for rendering both simple error pages with basic status messages and more detailed error pages
 * with information about exceptions, request details, and application context.
 *
 * @package Framework\Exceptions
 */
class ExceptionRenderer
{
    /**
     * Render a simple error page with a basic status message.
     *
     * @param int $code The HTTP status code.
     * @return \Framework\View\View The rendered view.
     */
    public function render($code = 500)
    {
        $data = [
            'message' => $this->getHttpStatusMessage($code),
            'code' => $code
        ];

        return view('exceptions/exception_basic', $data);
    }

    /**
     * Render a detailed error page with information about the exception, request details, and application context.
     *
     * @param array $context The context information for the exception.
     * @return \Framework\View\View The rendered view.
     */
    public function renderDebug(array $context)
    {
        $data = [
            'exception' => $context['exception'],
            'exception_context' => array_merge($context['exception_context'], $context['additional_context']),
            'request' => request(),
            'context' => [
                'php_version' => phpversion(),
                'framework_version' => app()->version(),
                'app_debug' => config('app.debug'),
                'app_env' => config('app.env'),
            ],
            'trace' => $this->debug_backtrace_string($context['exception']->getTrace())
        ];

        return view('exceptions/exception_debug', $data);
    }

    /**
     * Generate a stack trace as an array from the provided trace data.
     *
     * @param array $trace The trace data to be converted to a string.
     * @return array The stack trace as an array.
     */
    private function debug_backtrace_string($trace) {
        $stack = [];
        $i = 1;

        foreach($trace as $node) {
            $entry = "#" . $i . (isset($node['file']) && isset($node['line']) ? " " . $node['file'] . "(" . $node['line'] . "): " : " ");
            if(isset($node['class'])) {
                $entry .= $node['class']."->";
            }
            $entry .= $node['function']."()";
            array_push($stack, $entry);
            $i += 1;
        }
        return $stack;
    }

    /**
     * Get the HTTP status message for the given HTTP status code.
     *
     * @param int $statusCode The HTTP status code.
     * @return string The HTTP status message.
     */
    private function getHttpStatusMessage($statusCode) {
        $statusMessages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'Im a Teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        ];

        return isset($statusMessages[$statusCode]) ? $statusMessages[$statusCode] : 'Internal Server Error';
    }

}
<?php

namespace Framework\Exceptions;

use Framework\Log\Logger;
use Framework\Log\LogLevel;
use Framework\Services\Validator\Exceptions\ValidationFailed;
use Framework\Http\Response;
use Framework\Http\JsonResponse;

use Framework\Support\Arr;
use Framework\Support\Facades\Auth;

use Throwable;

/**
 * Class ExceptionHandler
 *
 * The ExceptionHandler class is responsible for handling exceptions in the application.
 * It reports exceptions based on their types and log levels, and provides context information.
 *
 * @package Framework\Exceptions
 */
class ExceptionHandler extends Logger
{
    /**
     * Mapping of exception types to log levels.
     *
     * @var array
     */
    protected $levels = [];

    /**
     * List of exceptions that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * List of exceptions for which flash messages should not be displayed when debug mode is disabled.
     *
     * @var array
     */
    protected $dontFlash = [
        Database\QueryExecutionError::class
    ];

    /**
     * Create a new ExceptionHandler instance.
     */
    public function __construct() {
        set_exception_handler([$this, 'register']);
    }

    /**
     * Report the given exception.
     *
     * @param Throwable $e The exception to report.
     */
    public function reportable($e)
    {
        $this->report($e);
    }

    /**
     * Log a Throwable exception.
     *
     * @param Throwable $e The exception to log.
     */
    public function loggable($e)
    {
        $this->logThrowable($e);
    }

    /**
     * Report or log an exception.
     *
     * @param Throwable $e The exception to report or log.
     */
    public function report(Throwable $e) {
        if ($this->shouldntReport($e)) return;

        $this->reportThrowable($e);
    }

    /**
     * Determine if the exception should not be reported.
     *
     * @param Throwable $e The exception to check.
     * @return bool True if the exception should not be reported, otherwise false.
     */
    protected function shouldntReport(Throwable $e) {
        $isExceptionInDontReport = false;

        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                $isExceptionInDontReport = true;
                break;
            }
        }

        if ($isExceptionInDontReport) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the exception should not be flashed.
     *
     * @param Throwable $e The exception to check.
     * @return bool True if the exception should not be flashed, otherwise false.
     */
    protected function shouldntFlash(Throwable $e) {
        $isExceptionInDontFlash = false;

        foreach ($this->dontFlash as $type) {
            if ($e instanceof $type) {
                $isExceptionInDontFlash = true;
                break;
            }
        }

        if ($isExceptionInDontFlash) {
            return true;
        }

        return false;
    }

    /**
     * Report and log a Throwable exception.
     *
     * This method is responsible for handling the given Throwable exception.
     * It logs it using logThrowable method, sends an HTTP response with
     * the appropriate status code and headers, and renders the exception for display.
     *
     * @param Throwable $e The exception to report or log.
     */
    protected function reportThrowable(Throwable $e)
    {
        $this->logThrowable($e);

        $response = match (true) {
            $e instanceof ValidationFailed => $this->convertValidationExceptionToResponse($e),
            default => $this->renderExceptionResponse($e),
        };

        $response->send();

        kernel()->terminate(request(), $response);
    }

    /**
     * Render an exception response.
     *
     * @param Throwable $e The exception to render.
     * @return Response The HTTP response.
     */
    protected function renderExceptionResponse(Throwable $e)
    {
        return $this->shouldReturnJson($e)
            ? $this->prepareJsonResponse($e)
            : $this->prepareResponse($e);
    }

    /**
     * Prepare a JSON response for the exception.
     *
     * @param Throwable $e The exception to prepare JSON response for.
     * @return JsonResponse The JSON response.
     */
    protected function prepareJsonResponse(Throwable $e)
    {
        return new JsonResponse(
            $this->convertExceptionToArray($e),
            $this->getStatusCode($e),
            $this->getHeaders($e),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Convert the exception to an array for JSON response.
     *
     * @param Throwable $e The exception to convert.
     * @return array The array representation of the exception.
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        return config('app.debug') ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
        ] : [
            'message' => $this->buildSafeExceptionMessage($e),
        ];
    }

    /**
     * Build a safe exception message to be displayed.
     *
     * @param Throwable $e The exception for which to build a safe message.
     * @return string The safe exception message.
     */
    protected function buildSafeExceptionMessage($e)
    {
        $message = $e->getMessage() ?? 'Server Error';
        return $this->shouldntFlash($e) ? 'Server Error' : $message;
    }

    /**
     * Prepare an HTML response for the exception.
     *
     * @param Throwable $e The exception to prepare HTML response for.
     * @return Response The HTML response.
     */
    protected function prepareResponse(Throwable $e)
    {
        return new Response($this->renderException($this->buildExceptionContext($e)), $this->getStatusCode($e), $this->getHeaders($e));
    }

    /**
     * Convert a validation exception to a response.
     *
     * @param ValidationFailed $e The validation exception to convert.
     * @return JsonResponse|Response The converted response.
     */
    protected function convertValidationExceptionToResponse(ValidationFailed $e)
    {
        if ($e->response) {
            return $e->response;
        }

        return $this->shouldReturnJson($e)
                    ? $this->invalidJson($e)
                    : $this->invalid($e);
    }

    /**
     * Prepare a JSON response for a validation exception.
     *
     * @param ValidationFailed $exception The validation exception to prepare JSON response for.
     * @return JsonResponse The JSON response.
     */
    protected function invalidJson(ValidationFailed $exception)
    {
        return new JsonResponse([
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Prepare a non-JSON response for a validation exception.
     *
     * @param ValidationFailed $exception The validation exception to prepare non-JSON response for.
     * @return void
     */
    protected function invalid(ValidationFailed $exception)
    {
        /*
         * Note: This section needs improvement in the future by implementing a comprehensive system
         * for redirects with detailed error information. Presently, the validation process redirects
         * to the default front page instead of redirecting back to the last visited page with an attached
         * error bag for a more user-friendly experience.
        */
        $redirect = request()->headers->get('referer') ?? url()->to('/');
        return header("Location: ".$redirect);
    }

    /**
     * Check if the response should be JSON.
     *
     * @param Throwable $e The exception to check.
     * @return bool True if the response should be JSON, otherwise false.
     */
    protected function shouldReturnJson(Throwable $e)
    {
        return request()->expectsJson();
    }

    /**
     * Log a Throwable exception.
     *
     * This method is responsible for determining the log level for the given Throwable exception,
     * building the context for logging, and then logging the exception message with the associated
     * log level. If the log level method does not exist in the class, it falls back to the generic
     * log method.
     *
     * @param Throwable $e The exception to log.
     */
    protected function logThrowable(Throwable $e)
    {
        $level = null;

        foreach ($this->levels as $type => $logLevel) {
            if ($e instanceof $type) {
                $level = $logLevel;
                break;
            }
        }

        if (is_null($level)) {
            $level = LogLevel::ERROR;
        }

        $context = $this->buildExceptionContext($e);

        method_exists($this, $level)
        ? $this->{$level}($e->getMessage(), $context)
        : $this->log($level, $e->getMessage(), $context);
    }

    /**
     * Render an exception.
     *
     * @param array $context The exception to render.
     */
    protected function renderException(array $context)
    {
        try {
            return config('app.debug')
                        ? $this->renderExceptionWithDebugRenderer($context)
                        : $this->renderExceptionWithSimpleRenderer($context['exception']);
        } catch (Throwable $e) {
            return $this->renderExceptionWithSimpleRenderer($e);
        }
    }

    /**
     * Render an exception using the debug renderer.
     *
     * @param array $context The exception to render.
     */
    protected function renderExceptionWithDebugRenderer(array $context)
    {
        return app(ExceptionRenderer::class)->renderDebug($context);
    }

    /**
     * Render an exception using a simple renderer.
     *
     * @param Throwable $e The exception to render.
     */
    protected function renderExceptionWithSimpleRenderer(Throwable $e)
    {
        return app(ExceptionRenderer::class)->render($this->getStatusCode($e));
    }

    /**
     * Get the HTTP status code for an exception.
     *
     * @param Throwable $e The exception to get the status code for.
     * @return int The HTTP status code.
     */
    protected function getStatusCode(Throwable $e)
    {
        $code = null;

        if (method_exists($e, 'getStatusCode')) $code = $e->getStatusCode();
        elseif (method_exists($e, 'getCode')) $code = $e->getCode();

        return $code !== null && $code != '0' ? $code : 500;
    }

    /**
     * Get the HTTP headers for an exception.
     *
     * @param Throwable $e The exception to get the headers for.
     * @return array The HTTP headers.
     */
    protected function getHeaders(Throwable $e) {
        return method_exists($e, 'getHeaders') ? $e->getHeaders() : [];
    }

    /**
     * Build the context for the given exception.
     *
     * @param Throwable $e The exception for which to build context.
     * @return array The context information.
     */
    protected function buildExceptionContext(Throwable $e) {
        return array_merge(
            ['exception' => $e],
            ['exception_context' => $this->exceptionContext($e)],
            ['additional_context' => $this->context()]
        );
    }

    /**
     * Get the context for the given exception.
     *
     * @param Throwable $e The exception for which to get context.
     * @return array The context information.
     */
    protected function exceptionContext(Throwable $e) {
        if (method_exists($e, 'context')) {
            return $e->context();
        }

        return [];
    }

    /**
     * Get the additional context information.
     *
     * @return array The additional context information.
     */
    protected function context()
    {
        try {
            return array_filter([
                'userId' => Auth::id(),
            ]);
        } catch (Throwable) {
            return [];
        }
    }
}
<?php

namespace Framework\Http;

use Carbon\Carbon;

/**
 * Class Kernel
 *
 * The kernel of the framework, responsible for handling incoming HTTP requests and managing application flow.
 * This class acts as the central coordinator, processing requests and sending responses.
 *
 * @package Framework\Http
 */
class Kernel
{
    /**
     * The application implementation.
     *
     * @var \Framework\Http\Application
     */
    protected $app;

    /**
     * The router instance.
     *
     * @var \Framework\Http\Router
     */
    protected $router;

    /**
     * The timestamp when the current request started.
     *
     * @var Carbon\Carbon|null
     */
    private $requestStartedAt;

    /**
     * The current HTTP request instance.
     *
     * @var Framework\Http\Request|null
     */
    private $request;

    /**
     * Kernel constructor.
     *
     * Initializes the Kernel instance and sets up the router.
     */
    public function __construct() {
        $this->app = app();

        $this->router = $this->app->make(Router::class);
    }

    /**
     * Get the current HTTP request instance.
     *
     * @return Framework\Http\Request|null The current HTTP request instance.
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Handle the incoming HTTP request.
     *
     * @param Framework\Http\Request $request The incoming HTTP request.
     * @return Framework\Http\Response The HTTP response.
     */
    public function handle(Request $request)
    {
        $this->requestStartedAt = Carbon::now();
        $this->request = $request;

        $this->bootstrap();

        try {
            $response = $this->dispatchToRouter($this->request);
        }
        catch (Throwable $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * Bootstrap the application if it hasn't been bootstrapped yet.
     *
     */
    public function bootstrap()
    {
        if (!$this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapApplication();
        }
    }

    /**
     * Dispatch the HTTP request to the router for processing.
     *
     * @param Framework\Http\Request $request The incoming HTTP request.
     * @return mixed The response returned by the router.
     */
    protected function dispatchToRouter($request) {
        return $this->router->dispatch($request);
    }

    /**
     * Terminate the request and send the HTTP response.
     *
     * @param Framework\Http\Request $request The incoming HTTP request.
     * @param Framework\Http\Response|Framework\Http\JsonResponse $response The HTTP response.
     */
    public function terminate(Request $request, Response|JsonResponse $response)
    {
        if ($this->app->isResolved('db')) app('db')->disconnect();

        $requestEndedAt = Carbon::now();

        $executionTimeInMilliseconds = $this->requestStartedAt->diffInMilliseconds($requestEndedAt);

        exit;
    }
}
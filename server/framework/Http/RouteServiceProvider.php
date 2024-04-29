<?php

namespace Framework\Http;

use Framework\Support\Facades\Route;

/**
 * Class RouteServiceProvider
 *
 * This class is responsible for managing the available interfaces and loading routes for the application.
 * It filters enabled interfaces, registers them in the route collection, and loads corresponding route files.
 *
 * @package Framework\Http
 */
class RouteServiceProvider
{
    /**
     * An array containing information about available interfaces.
     *
     * @var array
     */
    private array $availableInterfaces;

    /**
     * An array to store loaded routes.
     *
     * @var array
     */
    // private array $loadedRoutes;

    /**
     * Constructor for the RouteServiceProvider class.
     *
     * @param array $availableInterfaces An array containing information about available interfaces.
     */
    public function __construct(Array $availableInterfaces)
    {
        $this->availableInterfaces = $availableInterfaces;

        // Filter enabled interfaces
        $enabledInterfaces = array_filter($availableInterfaces, function ($interfaceData) {
            return $interfaceData['enabled'] === true;
        });

        foreach ($enabledInterfaces as $interfaceName => $interfaceData) {
            Route::updateGroupStack($interfaceData);
            load(base_path("routes/$interfaceName.php"));
        }
    }

    /**
     * Get the requested interface based on the incoming request.
     *
     * @param mixed $request The incoming request object.
     * @return array An array containing the matching interface name and prefix.
     */
    public function getRequestedInterface($request): array {

        $pathInfo = $request->getPathInfo();

        $matchingInterface = [];

        foreach ($this->availableInterfaces as $interfaceName => $interfaceData) {
            if ($interfaceData['enabled'] && strpos($pathInfo, $interfaceData['prefix']) === 0) {
                $matchingInterface['name'] = $interfaceName;
                $matchingInterface['prefix'] = $interfaceData['prefix'];
                $matchingInterface['request-headers'] = $interfaceData['request-headers'];
                $matchingInterface['response-headers'] = $interfaceData['response-headers'];
                break;
            }
        }

        return $matchingInterface;
    }

    /**
     * Get the request headers for the requested interface.
     *
     * @param mixed $request The incoming request object.
     * @return array The request headers for the requested interface.
     */
    public function getRequestHeaders($request): array
    {
        return $this->getRequestedInterface($request)['request-headers'];
    }

    /**
     * Get the response headers for the requested interface.
     *
     * @param mixed $response The incoming response object.
     * @return array The response headers for the requested interface.
     */
    public function getResponseHeaders($response): array
    {
        return $this->getRequestedInterface($response)['response-headers'];
    }
}
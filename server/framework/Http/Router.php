<?php

namespace Framework\Http;

use App\Providers\RouteServiceProvider;

use Framework\Http\Routing\RouteCollection;

use Framework\Services\Auth\Middleware\Authenticate;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use Framework\Support\Collections\Collection;
use Framework\Database\ORM\Model;

use ArrayObject;
use JsonSerializable;
use stdClass;
use Exception;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\NoConfigurationException;

/**
 * Class Router
 *
 * The Router class handles the routing logic for incoming HTTP requests. It interacts with the RouteServiceProvider to
 * determine the active interface, fetches the appropriate routes, and dispatches the request to the corresponding
 * controller or closure.
 *
 * @package Framework\Http
 */
class Router
{
    use Routing\AddRoutes;

    /**
     * The provider responsible for managing routes and interfaces.
     *
     * @var RouteServiceProvider
     */
    private $provider;

    /**
     * An array containing the routes for various interfaces.
     *
     * @var array
     */
    private $routes;

    /**
     * Information about the requested interface.
     *
     * @var array
     */
    private $requestedInterface;

    /**
     * Constructor initializing the route provider and route collection.
     */
    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    /**
     * Handles incoming requests and invokes the appropriate route.
     *
     * @param Request $request HTTP request.
     * @return \Framework\Http\RequestResponse|\Framework\Http\JsonResponse HTTP response.
     */
    public function dispatch(Request $request): Response|JsonResponse
    {
        if (!$this->provider) {
            $this->provider = new RouteServiceProvider();
        }

        $this->requestedInterface = $this->provider->getRequestedInterface($request);
        $response = $this->runRoute($request, $this->routes);
        return $this->prepareResponse($request, $response);
    }

    /**
     * Invokes the route and handles exceptions, especially setting the request headers for used interface, generating the appropriate HTTP responses,.
     *
     * @param Request $request HTTP request.
     * @param RouteCollection $routes Collection of routes for the interface.
     * @return mixed Generated response based on content of requested route.
     */
    private function runRoute(Request $request, $routes): mixed
    {
        foreach ($this->requestedInterface['request-headers'] ?? [] as $key => $value) {
            $request->headers->set($key, $value);
        }

        $context = new RequestContext();
        $context->fromRequest($request);

        $matcher = new UrlMatcher($routes->toSymfonyRouteCollection(), $context);

        try {
            $matcher = $matcher->match($request->getPathInfo());

            if ($routes->getRouteByName($matcher['_route'])->isGuarded()) {
                try {
                    Authenticate::authenticate();
                } catch(\Exception $e) {
                    return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
                }
            }

            array_walk($matcher, function(&$param) {
                if(is_numeric($param)) {
                    $param = (int) $param;
                }
            });

            if (isset($matcher['_controller']) && $matcher['_controller'] instanceof \Closure) {
                $availableParams = array_merge(array_slice($matcher, 1, -1), array('request' => $request, 'routes' => $routes));

                $reflection = new \ReflectionFunction($matcher['_controller']);

                $params = $this->prepareParameterList($reflection, $availableParams);

                $response = call_user_func_array($matcher['_controller'], $params);
            }
            elseif (isset($matcher['controller']) && isset($matcher['method'])) {
                $availableParams = array_merge(array_slice($matcher, 2, -1), array('request' => $request, 'routes' => $routes));

                $className = '\\App\\Controllers\\'.$matcher['controller'];
                $classInstance = new $className();

                if (!method_exists($classInstance, $matcher['method'])) {
                    throw new Exception("Method ".get_class($classInstance)."::{$matcher['method']} does not exist.", Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                $reflection = new \ReflectionMethod($classInstance, $matcher['method']);

                $params = $this->prepareParameterList($reflection, $availableParams);

                $response = call_user_func_array([$classInstance, $matcher['method']], $params);
            }
            else {
                throw new Exception('Invalid route handler', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $response;

        } catch (MethodNotAllowedException $e) {
            throw new Exception("The {$request->getMethod()} method is not supported for route {$request->getPathInfo()}.", Response::HTTP_METHOD_NOT_ALLOWED);
        } catch (ResourceNotFoundException $e) {
            throw new Exception("The route {$request->getPathInfo()} could not be found.", Response::HTTP_NOT_FOUND);
        } catch (NoConfigurationException $e) {
            throw new Exception("Configuration does not exist.", Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Prepares the HTTP response by adding headers, especially setting the response headers for used interface.
     *
     * @param mixed $response Route response.
     * @return \Framework\Http\Response|\Framework\Http\JsonResponse HTTP response.
     */
    private function prepareResponse($request, $response): Response|JsonResponse
    {
        if ($response instanceof Model || $response instanceof Collection) {
            $response = new JsonResponse($response->toArray());
        }
        else if (is_array($response) || $response instanceof stdClass || $response instanceof JsonSerializable || $response instanceof ArrayObject ) {
            $response = new JsonResponse($response);
        }
        else if (!$response instanceof SymfonyResponse) {
            $response = new Response($response, 200, ['Content-Type' => 'text/html']);
        }

        foreach($this->requestedInterface['response-headers'] as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response->prepare($request);
    }

    /**
     * Prepare the parameter list for a route handler method or closure based on its reflection and available parameters.
     *
     * This method analyzes the reflection of the route handler (either a method or closure) to determine its required parameters.
     * It then selects the matching parameters from the available parameters list. If no matching parameters are found,
     * it defaults to passing the 'request' parameter. This ensures that the route handler receives only the parameters it needs.
     *
     * @param \ReflectionMethod|\ReflectionFunction $reflection Reflection of the route handler.
     * @param array $availableParams Available parameters for the route handler.
     * @return array List of parameters to be passed to the route handler.
     */
    private function prepareParameterList(\ReflectionMethod|\ReflectionFunction $reflection, array $availableParams) : array
    {
        $reflectionParams = array_map(fn($param) => $param->getName(), $reflection->getParameters());

        $params = array_intersect_key($availableParams, array_flip($reflectionParams));

        return $params ?: [$availableParams['request']];
    }
}
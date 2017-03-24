<?php

namespace Resilient;

use \Resilient\Route;
use \Resilient\Design\RouteableInterface;
use \Resilient\Traits\Routeable;
use \Resilient\Exception\MethodNotAllowedException;
use \Resilient\Exception\NotFoundException;
use \FastRoute\Dispatcher;
use \FastRoute\RouteCollector;
use \FastRoute\RouteParser;
use \FastRoute\RouteParser\Std as StdParser;
use \Psr\Http\Message\UriInterface;
use \Psr\SimpleCache\CacheInterface;

class Router implements RouteableInterface
{

    use Routeable;

    protected $routerName;
    protected $dispatch_result;

    protected $routes;


    protected $routeCount = 0;
    protected $routeGroup;

    protected $parser;
    protected $dispatcher;

    protected $uri;

    protected $cacheEngine;
    protected $cacheKey = "{Resilient\Router}/router.cache";
    protected $cacheTtl = 86400;

    protected $apiHandler;

    public function __construct($parser, $routefor = 'App')
    {
        $this->parser = $parser ?: new StdParser;
        $this->routefor = $routefor;
    }

    public function setApiHandler(callable $apiHandler)
    {
        $this->apiHandler = $apiHandler;

        return $this;
    }

    public function setRouterName(string $name)
    {
        $this->routerName = $name;
        return $this;
    }

    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    public function setCacheEngine(CacheInterface $cacheEngine)
    {
        $this->cacheEngine = $cacheEngine;

        return $this;
    }

    public function setCacheTtl(int $cacheTtl)
    {
        $this->cacheTtl = $cacheTtl;

        return $this;
    }

    public function setCacheKey(string $cacheKey)
    {
        $this->cacheKey = !empty($this->routefor) ? $this->routefor . $cacheKey : $cacheKey;
        return $this;
    }

    public function setUri(UriInterface $uri)
    {
        $this->uri = $uri;

        return $this;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getRoute(string $identifier)
    {
        return !empty($this->routes[$identifier]) ? $this->routes[$identifier] : null;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function setRoutes(array $method, array $routes)
    {
        array_map(function ($pattern, $handler) use ($method) {
            return $this->map($method, $pattern, $handler);
        }, array_keys($routes), $routes);

        return $this;
    }

    public function getResult()
    {
        return $this->dispatch_result;
    }

    public function map($method, string $pattern, $handler)
    {
        $method = is_array($method) ? $method : [$method];

        foreach ($method as $m) {
            $route = $this->createRoute($m, $pattern, $handler);

            $this->routeCount++;

            $this->routes[$route->getIdentifier()] = $route;
        }

        return $this;
    }

    protected function createRoute(string $method, string $pattern, $handler)
    {
        return new Route($method, $pattern, $handler, $this->routeGroup, 'route_' . $this->routeCount);
    }

    protected function handleException(Exception $e, UriInterface $uri)
    {
        if ($e instanceof MethodNotAllowedException)
        {

        }
    }

    protected function routeDispatcher(callable $routeDefinitionCallback, array $options = [])
    {
        $options += [
            'routeParser' => 'FastRoute\\RouteParser\\Std',
            'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
            'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
            'routeCollector' => 'FastRoute\\RouteCollector'
        ];

        if (!empty($this->cacheEngine) && !empty($this->cacheKey)) {
            $dispatchData = $this->cacheEngine->get($this->cacheKey);
            if (!is_array($dispatchData)) {
                return new $options['dispatcher']($dispatchData);
            }
        }

        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'], new $options['dataGenerator']
        );
        $routeDefinitionCallback($routeCollector);

        $dispatchData = $routeCollector->getData();
        if (!empty($this->cacheEngine) && !empty($this->cacheKey)) {
            $dispatchData = $this->cacheEngine->set($this->cacheKey, $dispatchData, $this->cacheTtl);
        }

        return new $options['dispatcher']($dispatchData);
    }

    protected function createDispatcher()
    {
        if ($this->dispatcher) {
            return $this->dispatcher;
        }

        $routeDefinitionCallback = function (RouteCollector $r) {
            foreach ($this->getRoutes() as $route) {
                $r->addRoute($route->getMethod(), $route->getPattern(), $route->getIdentifier());
            }
        };

        $this->dispatcher = $this->routeDispatcher($routeDefinitionCallback, [
            'routeParser' => $this->parser,
        ]);

        return $this->dispatcher;
    }

    public function dispatch(UriInterface $uri, $method = 'GET')
    {
        $this->dispatch_result = $this->createDispatcher()->dispatch(
            $method,
            $uri->getPath()
        );

        switch ($this->dispatch_result[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                // ... call $handler with $vars
                break;
        }


    }
}

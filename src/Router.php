<?php

namespace Resilient;

use RunTimeException;
use \Resilient\Route;
use \Resilient\Design\RouteableInterface;
use \Resilient\Traits\Routeable;
use \Resilient\Traits\Bindable;
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

    use Routeable, Bindable;

    protected $routerName;
    protected $dispatch_result;

    protected $routes;


    protected $routeCount = 0;
    protected $routeGroup;

    protected $parser;
    protected $dispatcher;

    protected $cacheEngine;
    protected $cacheKey = "{Resilient\Router}/router.cache";
    protected $cacheTtl = 86400;

    protected $apiHandler;

    protected $notFoundHandler;
    protected $methodNotAllowedHandler;

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

            if (is_callable($handler)) {
                $route->bind('run', $handler);
            }
        }

        return $this;
    }

    protected function createRoute(string $method, string $pattern, $handler)
    {
        return new Route($method, $pattern, $handler, $this->routeGroup, 'route_' . $this->routeCount);
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

        $functionHandler = function ($arg) use ($uri) {
            if (method_exists($this, $arg['methodName'])) {
                return $this->{$arg['methodName']}(...$arg['args']);
            } else {
                if (!empty($arg['exceptionName'])) {
                    throw new $arg['exceptionName'](...$arg['args']);
                } else {
                    throw new RunTimeException('There is no method or exception to handle this request ' . ( (string) $uri ));
                }
            }
        };

        $handlerMapper = [
            Dispatcher::NOT_FOUND => [
                'methodName' => 'notFoundHandler',
                'args' => [$uri],
                'exceptionName' => 'NotFoundException'
            ],
            Dispatcher::METHOD_NOT_ALLOWED => [
                'methodName' => 'forbidenMethodHandler',
                'args' => [$uri],
                'exceptionName' => 'MethodNotAllowedException'
            ],
            Dispatcher::FOUND => [
                'methodName' => 'routerRoutine',
                'args' => $this->dispatch_result
            ]
        ];

        return $functionHandler($handlerMapper[$this->dispatch_result[0]]);

    }

    protected function routerRoutine($code, $identifier, $args)
    {
        $route = $this->getRoute($identifier);

        array_walk_recursive($args, function (&$v){
            $v = urldecode($v);
        });

        if ($route->hasMethod('run')) {
            return $route->run($args);
        } else {

            return $route->setArgs($args);
        }
    }
}

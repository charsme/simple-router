<?php

namespace Resilient;

use BadMethodCallException;
use \Resilient\Route;
use \Resilient\Design\RouteableInterface;
use \Resilient\Traits\Routeable;
use \Resilient\Traits\Bindable;
use \FastRoute\Dispatcher;
use \FastRoute\RouteCollector;
use \FastRoute\RouteParser;
use \FastRoute\RouteParser\Std as StdParser;
use \Psr\Http\Message\UriInterface;
use \Psr\SimpleCache\CacheInterface;

/**
 * Router class.
 *
 * @implements RouteableInterface
 * @method callable run(array $args)
 */
class Router implements RouteableInterface
{
    use Routeable, Bindable;

    /**
     * notFoundFuncName
     *
     * (default value: 'notFoundHandler')
     *
     * @var string
     * @access protected
     */
    protected $notFoundFuncName = 'notFoundHandler';

    /**
     * forbiddenFuncName
     *
     * (default value: 'forbidenMethodHandler')
     *
     * @var string
     * @access protected
     */
    protected $forbiddenFuncName = 'forbidenMethodHandler';

    /**
     * dispatch_result
     *
     * @var mixed
     * @access protected
     */
    protected $dispatch_result;

    /**
     * routes
     *
     * (default value: [])
     *
     * @var mixed
     * @access protected
     */
    protected $routes = [];

    /**
     * routeCount
     *
     * (default value: 0)
     *
     * @var int
     * @access protected
     */
    protected $routeCount = 0;

    /**
     * routeGroup
     *
     * @var mixed
     * @access protected
     */
    protected $routeGroup;

    /**
     * parser
     *
     * @var mixed
     * @access protected
     */
    protected $parser;

    /**
     * dispatcher
     *
     * @var mixed
     * @access protected
     */
    protected $dispatcher;

    /**
     * cacheEngine
     *
     * @var mixed
     * @access protected
     */
    protected $cacheEngine;

    /**
     * cacheKey
     *
     * (default value: "{Resilient\Router}/router.cache")
     *
     * @var string
     * @access protected
     */
    protected $cacheKey = "{Resilient\Router}/router.cache";

    /**
     * cacheTtl
     *
     * (default value: 86400)
     *
     * @var int
     * @access protected
     */
    protected $cacheTtl = 86400;

    /**
     * apiHandler
     *
     * @var mixed
     * @access protected
     */
    protected $apiHandler;

    /**
     * notFoundHandler
     *
     * @var mixed
     * @access protected
     */
    protected $notFoundHandler;

    /**
     * methodNotAllowedHandler
     *
     * @var mixed
     * @access protected
     */
    protected $methodNotAllowedHandler;

    /**
     * __construct function.
     *
     * @access public
     * @param mixed $parser
     */
    public function __construct($parser)
    {
        $this->parser = $parser ?: new StdParser;
    }

    /**
     * setDispatcher function.
     *
     * @access public
     * @param Dispatcher $dispatcher
     * @return Router
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * setCacheEngine function.
     *
     * @access public
     * @param CacheInterface $cacheEngine
     * @return Router
     */
    public function setCacheEngine(CacheInterface $cacheEngine)
    {
        $this->cacheEngine = $cacheEngine;

        return $this;
    }

    /**
     * setCacheTtl function.
     *
     * @access public
     * @param int $cacheTtl
     * @return Router
     */
    public function setCacheTtl(int $cacheTtl)
    {
        $this->cacheTtl = $cacheTtl;

        return $this;
    }

    /**
     * setCacheKey function.
     *
     * @access public
     * @param string $cacheKey
     * @return Router
     */
    public function setCacheKey(string $cacheKey)
    {
        $this->cacheKey = $cacheKey;
        return $this;
    }

    /**
     * getRoute function.
     *
     * @access public
     * @param string $identifier
     * @return null|Route
     */
    public function getRoute(string $identifier)
    {
        return !empty($this->routes[$identifier]) ? $this->routes[$identifier] : null;
    }

    /**
     * getRoutes function.
     *
     * @access public
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * setRoutes function.
     *
     * @access public
     * @param array $method
     * @param array $routes
     * @return Router
     */
    public function setRoutes(array $method, array $routes)
    {
        foreach ($routes as $pattern => $handler) {
            $this->map($method, $pattern, $handler);
        }

        return $this;
    }

    /**
     * getResult function.
     *
     * @access public
     * @return void
     */
    public function getResult()
    {
        return $this->dispatch_result;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * createRoute function.
     *
     * @access protected
     * @param string $method
     * @param string $pattern
     * @param mixed $handler
     * @return Route Route
     */
    protected function createRoute(string $method, string $pattern, $handler)
    {
        return new Route($method, $pattern, $handler, $this->routeGroup, 'route_' . $this->routeCount);
    }

    /**
     * routeDispatcher function.
     *
     * @access protected
     * @param callable $routeDefinitionCallback
     * @param array $options (default: [])
     * @return Dispatcher
     */
    protected function routeDispatcher(callable $routeDefinitionCallback, array $options = [])
    {
        $options += [
            'routeParser' => 'FastRoute\\RouteParser\\Std',
            'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
            'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
            'routeCollector' => 'FastRoute\\RouteCollector'
        ];
        
        $dispatchDataRunner = function () use ($routeDefinitionCallback, $options) {
            $routeCollector = new $options['routeCollector'](
                new $options['routeParser'], new $options['dataGenerator']
            );
            $routeDefinitionCallback($routeCollector);
    
            return $routeCollector->getData();
        };
        
        if (!empty($this->cacheEngine) && !empty($this->cacheKey)) {
            if ($this->cacheEngine->has($this->cacheKey)) {
                $dispatchData = $this->cacheEngine->get($this->cacheKey);
                
                return new $options['dispatcher']($dispatchData);
            } else {
                $dispatchData = $dispatchDataRunner();
                $this->cacheEngine->set($this->cacheKey, $dispatchData, $this->cacheTtl);
                
                return $dispatchData;
            }
        } else {
            return new $options['dispatcher']($dispatchDataRunner());
        }
        
    }

    /**
     * createDispatcher function.
     *
     * @access protected
     * @return Dispatcher
     */
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

    /**
     * dispatch function.
     *
     * @access public
     * @param UriInterface $uri
     * @param string $method (default: 'GET')
     * @return Route Handling Method
     */
    public function dispatch(UriInterface $uri, $method = 'GET')
    {
        $this->dispatch_result = $this->createDispatcher()->dispatch(
            $method,
            $uri->getPath()
        );

        $functionHandler = function ($arg) use ($uri, $method) {
            if (method_exists($this, $arg['methodName']) || $this->hasMethod($arg['methodName'])) {
                return $this->{$arg['methodName']}(...$arg['args']);
            } else {
                return $this->handleException($arg['methodName'], $uri, $method);
            }
        };

        $code = array_shift($this->dispatch_result);
        
        $handlerMapper = [
            Dispatcher::NOT_FOUND => [
                'methodName' => $this->notFoundFuncName,
                'args' => [$uri, $method]
            ],
            Dispatcher::METHOD_NOT_ALLOWED => [
                'methodName' => $this->forbiddenFuncName,
                'args' => [$uri, $method]
            ],
            Dispatcher::FOUND => [
                'methodName' => 'routerRoutine',
                'args' => $this->dispatch_result
            ]
        ];

        return $functionHandler($handlerMapper[$code]);
    }
    
    protected function handleException($exception, $uri, $method)
    {
        if ($exception === $this->notFoundFuncName) {
            throw new BadMethodCallException('Method : ' . ((string) $method) . ' ON uri : ' . ((string) $uri) . ' Not Allowed');
        } elseif ($exception === $this->forbiddenFuncName) {
            throw new BadMethodCallException(((string) $uri) . ' Not Available');
        } else {
            throw new BadMethodCallException('There is no method or exception to handle this request ' . ((string) $uri));
        }
        
        return null;
    }

    /**
     * whenNotFound function.
     *
     * @access public
     * @param callable $callable
     * @return Router
     */
    public function whenNotFound(callable $callable)
    {
        $this->bind($this->notFoundFuncName, $callable);

        return $this;
    }

    /**
     * whenForbidden function.
     *
     * @access public
     * @param callable $callable
     * @return Router
     */
    public function whenForbidden(callable $callable)
    {
        $this->bind($this->forbiddenFuncName, $callable);

        return $this;
    }

    /**
     * routerRoutine function.
     *
     * @access protected
     * @param mixed $identifier
     * @param mixed $args
     * @return void
     */
    protected function routerRoutine($identifier, $args)
    {
        $route = $this->getRoute($identifier);

        if (!empty($args)) {
            foreach ($args as &$v) {
                $v = urldecode($v);
            }
        }

        if ($route->hasMethod('run')) {
            return $route->run($args);
        } else {
            return $route->setArgs($args);
        }
    }
}

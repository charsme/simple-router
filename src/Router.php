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
use \Psr\SimpleCache\CacheItemPoolInterface;

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
     * cachePool
     *
     * @var mixed
     * @access protected
     */
    protected $cachePool;

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
     * setcachePool function.
     *
     * @access public
     * @param CacheInterface $cachePool
     * @return Router
     */
    public function setCachePool(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;

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
     * cacheAble function.
     *
     * @access protected
     * @return void
     */
    protected function cacheAble()
    {
        return !empty($this->cachePool) && !empty($this->cacheKey);
    }

    /**
     * getCacheItem function.
     *
     * @access protected
     * @return CacheItemInterface
     */
    protected function getCacheItem()
    {
        return $this->cacheAble() ? $this->cachePool->getItem($this->cacheKey)  : new \Resilient\Dummy\CacheItem();
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

        $cacheItem = $this->getCacheItem();

        if ($cacheItem->isHit()) {
            return new $options['dispatcher']($cacheItem->get($this->cacheKey));
        }

        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'], new $options['dataGenerator']
        );
        $routeDefinitionCallback ($routeCollector);

        $dispatchData = $routeCollector->getData();

        $cacheItem->set($dispatchData);
        $cacheItem->expiresAfter($this->cacheTtl);

        return new $options['dispatcher']($dispatchData);
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

        $this->dispatcher = $this->routeDispatcher(function (RouteCollector $r) {
            foreach ($this->getRoutes() as $route) {
                $r->addRoute($route->getMethod(), $route->getPattern(), $route->getIdentifier());
            }
        }, [
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

        $code = array_shift($this->dispatch_result);

        if ($code == Dispatcher::FOUND) {
            return $this->routerRoutine($this->dispatch_result[0], $this->dispatch_result[1]);
        }

        $exceptionMapper = [
            Dispatcher::NOT_FOUND => [
                $this->notFoundFuncName,
                [$uri, $method],
                ((string) $uri) . ' Not Available'
            ],
            Dispatcher::METHOD_NOT_ALLOWED => [
                $this->forbiddenFuncName,
                [$uri, $method],
                'Method : ' . ((string) $method) . ' ON uri : ' . ((string) $uri) . ' Not Allowed'
            ]
        ];

        $handling = $exceptionMapper[$code];

        return $this->handleException($handling[0], $handling[1], $handling[2]);
    }

    protected function handleException(string $handler, array $args, string $message)
    {
        if ($this->hasMethod($handler)) {
            return $this->$handler(...$args);
        }

        throw new BadMethodCallException($message);
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
        }

        return $route->setArgs($args);
    }
}

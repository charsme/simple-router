<?php

namespace Resilient;

use \Resilient\Route;
use \Resilient\Http\Uri;
use \Resilient\Factory\Uri as UriFactory;
use BadMethodCallException;

class RouterTest extends \PHPUnit\Framework\TestCase
{
    protected $routes;
    protected $router;

    /**
     * @covers Router::__construct
     * @covers Router::setRoutes
     * @covers Router::createRoute
     * @covers Router::map
     */
    public function __construct()
    {
        $this->routes = require 'routes.php';
        $this->router = new Router(null);

        foreach ($this->routes as $method => $routes) {
            $this->router->setRoutes([$method], $routes);
        }
    }

    /**
     * @covers Router::get
     * @covers Router::post
     * @covers Router::put
     * @covers Router::map
     * @covers Router::getRoutes
     */
    public function testRoutable()
    {
        $router = new Router(null);

        foreach ($this->routes as $method => $routes) {
            $meth = strtolower($method);
            foreach ($routes as $pattern => $handler) {
                $router->$meth($pattern, $handler);
            }
        }

        $this->assertEquals($this->router->getRoutes(), $router->getRoutes());
    }

    /**
     * @covers Router::dispatch
     * @covers UriFactory::createFromString
     * $covers Router::createDispatcher
     * $covers Router::routeDispatcher
     */
    public function testGET()
    {
        $uri = UriFactory::createFromString('http://www.example.com/test');
        $result = $this->router->dispatch($uri);

        $this->assertEquals('routed', $result);

        $uri = UriFactory::createFromString('http://www.example.com/');
        $result = $this->router->dispatch($uri);

        $this->assertInstanceOf('Resilient\Route', $result);
    }

    /**
     * @covers Route::getArgs
     * @covers Route::__construct
     */
    public function testArgumentCheck()
    {
        $uri = UriFactory::createFromString('http://www.example.com/test/156');
        $result = $this->router->dispatch($uri);

        $this->assertEquals(2, count($result->getArgs()));
        $this->assertEquals('156', $result->getArgs()['id']);
    }

    /**
     * @covers Router::handleException
     */
    public function testNotFound()
    {
        $this->expectException(BadMethodCallException::class);

        $uri = UriFactory::createFromString('http://www.example.com/not/exist/path');
        $result = $this->router->dispatch($uri);
    }

    /**
     * @covers Router::whenNotFound
     * @covers Router::bind
     * @covers Router::run
     */
    public function testNotFoundHandler()
    {
        $uri = UriFactory::createFromString('http://www.example.com/not/exist/path');

        $this->router->whenNotFound(function ($arg) {
            return $arg;
        });

        $result = $this->router->dispatch($uri);

        $this->assertEquals($uri, $result);
    }

    /**
     * @covers Router::handleException
     */
    public function testForbiddenMethod()
    {
        $this->expectException(BadMethodCallException::class);

        $uri = UriFactory::createFromString('http://www.example.com/');
        $result = $this->router->dispatch($uri, 'DELETE');
    }

    /**
     * @covers Router::whenForbidden
     * @covers Router::run
     */
    public function testForbiddenHandler()
    {
        $uri = UriFactory::createFromString('http://www.example.com/');

        $this->router->whenForbidden(function ($uri, $method) {
            return $uri;
        });

        $result = $this->router->dispatch($uri, 'PUT');

        $this->assertEquals($uri, $result);
    }

    /**
     * @covers Router::routerRoutine
     * @covers Router::run
     */
    public function testPOST()
    {
        $_POST = ['vars' => 'come', 'input' => ['test' => 'text']];

        $uri = UriFactory::createFromString('http://www.example.com/api');
        $result = $this->router->dispatch($uri, 'POST');

        $this->assertEquals($_POST, $result);
    }

    /**
     * @covers Route::getHandler
     */
    public function testClassInvokeable()
    {
        $uri = UriFactory::createFromString('http://www.example.com/kerap/254');
        $result = $this->router->dispatch($uri, 'POST');

        $this->assertEquals(DummyController::class, $result->getHandler());

        $classname = $result->getHandler();
        $invoke = new $classname();

        $this->assertInstanceOf(DummyController::class, $invoke($result->getArgs()));
    }

    /**
     * @covers Route::getHandler
     */
    public function testClassInvokeable2()
    {
        $uri = UriFactory::createFromString('http://www.example.com/jeng');
        $result = $this->router->dispatch($uri, 'POST');

        $this->assertEquals(DummyController::class, $result->getHandler());

        $classname = $result->getHandler();
        $invoke = new $classname();

        $this->assertInstanceOf(DummyController::class, $invoke($result->getArgs()));
    }
}

class DummyController
{
    protected $name;

    public function __construct()
    {
        $this->name = 'Dummy';
    }

    public function __invoke($args)
    {
        $args += [
            'name' => 'default',
            'point' => 0
        ];

        $message = $this->name .' to '. $args['name'] . ' got :'. $args['point'];

        return $this;
    }
}

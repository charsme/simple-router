<?php

namespace Resilient;

use \Resilient\Http\Uri;
use BadMethodCallException;

class RouterTest extends \PHPUnit\Framework\TestCase
{
    protected $routes;
    protected $router;

    public function __construct()
    {
        $this->routes = require 'routes.php';
        $this->router = new Router(null);

        foreach ($this->routes as $method => $routes) {
            $this->router->setRoutes([$method], $routes);
        }
    }

    public function testGET()
    {
        $uri = Uri::createFromString('http://www.example.com/test');
        $result = $this->router->dispatch($uri);

        $this->assertEquals('routed', $result);

        $uri = Uri::createFromString('http://www.example.com/');
        $result = $this->router->dispatch($uri);

        $this->assertInstanceOf('Resilient\Route', $result);
    }

    public function testArgumentCheck()
    {
        $uri = Uri::createFromString('http://www.example.com/test/156');
        $result = $this->router->dispatch($uri);

        $this->assertEquals(2, count($result->getArgs()));
        $this->assertEquals('156', $result->getArgs()['id']);
    }

    public function testNotFound()
    {
        $this->expectException(BadMethodCallException::class);

        $uri = Uri::createFromString('http://www.example.com/not/exist/path');
        $result = $this->router->dispatch($uri);
    }

    public function testNotFoundHandler()
    {
        $uri = Uri::createFromString('http://www.example.com/not/exist/path');

        $this->router->whenNotFound(function ($arg) {
            return $arg;
        });

        $result = $this->router->dispatch($uri);

        $this->assertEquals($uri, $result);
    }

    public function testForbiddenMethod()
    {
        $this->expectException(BadMethodCallException::class);

        $uri = Uri::createFromString('http://www.example.com/');
        $result = $this->router->dispatch($uri, 'PUT');
    }

    public function testForbiddenHandler()
    {
        $uri = Uri::createFromString('http://www.example.com/');

        $this->router->whenForbidden(function ($method, $uri) {
            return $uri;
        });

        $result = $this->router->dispatch($uri, 'PUT');

        $this->assertEquals($uri, $result);
    }

    public function testPOST()
    {
        $_POST = ['vars' => 'come', 'input' => ['test' => 'text']];

        $uri = Uri::createFromString('http://www.example.com/api');
        $result = $this->router->dispatch($uri, 'POST');

        $this->assertEquals($_POST, $result);
    }

    public function testClassInvokeable()
    {
        $uri = Uri::createFromString('http://www.example.com/kerap/254');
        $result = $this->router->dispatch($uri, 'POST');

        $this->assertEquals(DummyController::class, $result->getHandler());

        $classname = $result->getHandler();
        $invoke = new $classname();

        $this->assertInstanceOf(DummyController::class, $invoke($result->getArgs()));
    }

    public function testClassInvokeable2()
    {
        $uri = Uri::createFromString('http://www.example.com/jeng');
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
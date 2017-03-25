<?php

use \Resilient\Http\Uri;

include '../vendor/autoload.php';

$routes = require 'routes.php';

$router = new \Resilient\Router(null);

$router->setRoutes(['GET'], $routes);

$uri = Uri::createFromString('http://www.example.com/test');

echo '<br /><br /><pre>';

var_export($uri);

$result = $router->dispatch($uri);

echo '<br /><br /><pre>';

var_export($result);



echo '<br /><br /><pre>';

var_dump(Uri::class);
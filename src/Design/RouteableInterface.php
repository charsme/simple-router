<?php

namespace Resilient\Design;

interface RouteableInterface
{
    public function get (string $pattern, $handler);

    public function post (string $pattern, $handler);

    public function put (string $pattern, $handler);

    public function patch (string $pattern, $handler);

    public function delete (string $pattern, $handler);

    public function options (string $pattern, $handler);

    public function any (string $pattern, $handler);

    public function map ($methods, string $pattern, $handler);
}
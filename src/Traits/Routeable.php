<?php

namespace Resilient\Traits;

trait Routeable
{
    public function get (string $pattern, $handler)
    {
        return $this->map('GET', $pattern, $handler);
    }

    public function post (string $pattern, $handler)
    {
        return $this->map('POST', $pattern, $handler);
    }

    public function put (string $pattern, $handler)
    {
        return $this->map('PUT', $pattern, $handler);
    }

    public function patch (string $pattern, $handler)
    {
        return $this->map('PATCH', $pattern, $handler);
    }

    public function delete (string $pattern, $handler)
    {
        return $this->map('DELETE', $pattern, $handler);
    }

    public function options (string $pattern, $handler)
    {
        return $this->map('OPTIONS', $pattern, $handler);
    }

    public function any (string $pattern, $handler)
    {
        return $this->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern, $handler);
    }
}
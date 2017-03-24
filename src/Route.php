<?php

namespace Resilient;

use \Resilient\Design\RouteableInterface;
use \Resilient\Traits\Routeable;

class Route implements RouteableInterface
{
    use Routeable;

    protected $method;
    protected $pattern;
    protected $handler;
    protected $group;
    protected $identifier;

    protected $callable;

    public function __construct(string $method, string $pattern, string $handler, $group = '', $identifier = '')
    {
        $this->method = $method;
        $this->pattern = $pattern;
        $this->handler = $handler;
        $this->group = $group;
        $this->identifier = $identifier;
    }

    public function map($methods, string $pattern, $handler)
    {
        return new self($methods, $pattern, $handler);
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function setHandler(string $handler)
    {
        $this->handler = $handler;

        return $this;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(string $group)
    {
        $this->group = $group;

        return $this;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function run()
    {
        return $this->callable;
    }

    public function bind(callable $callable)
    {
        $this->callable = $callable->bindto($this);

        return $this;
    }
}
<?php

namespace Resilient\Traits;

use Closure;
use InvalidArgumentException;
use RunTimeException;

trait Bindable
{
    protected $binded;

    public function bind(string $name, callable $callable)
    {
        if (!is_callable($callable)) {
            throw new InvalidArgumentException('Second param must be callable');
        }

        $this->binded[$name] = Closure::bind($callable, $this, get_class());

        return $this;
    }

    public function hasMethod(string $name)
    {
        return isset($this->binded[$name]);
    }

    public function __call($name, array $args)
    {
        if ($this->hasMethod($name)) {
            return $this->getBind($name, $args);
        }

        throw new RunTimeException('There is no method with the given name to call');
    }

    public function getBind($name, $args)
    {
        return $this->binded[$name](...$args);
    }
}
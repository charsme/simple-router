<?php

namespace Resilient\Traits;

use Closure;

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
        if (isset($this->binded[$name])) {
            return call_user_func_array($this->binded[$name], $args);
        }

        throw RunTimeException('There is no method with the given name to call');
    }
}
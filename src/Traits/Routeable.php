<?php

namespace Resilient\Traits;

/**
 * Routeable trait.
 */
trait Routeable
{
    /**
     * get function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function get (string $pattern, $handler)
    {
        return $this->map('GET', $pattern, $handler);
    }

    /**
     * post function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function post (string $pattern, $handler)
    {
        return $this->map('POST', $pattern, $handler);
    }

    /**
     * put function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function put (string $pattern, $handler)
    {
        return $this->map('PUT', $pattern, $handler);
    }

    /**
     * patch function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function patch (string $pattern, $handler)
    {
        return $this->map('PATCH', $pattern, $handler);
    }

    /**
     * delete function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function delete (string $pattern, $handler)
    {
        return $this->map('DELETE', $pattern, $handler);
    }

    /**
     * options function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function options (string $pattern, $handler)
    {
        return $this->map('OPTIONS', $pattern, $handler);
    }

    /**
     * any function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function any (string $pattern, $handler)
    {
        return $this->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern, $handler);
    }
    
    /**
     * map function.
     *
     * @access public
     * @abstract
     * @param mixed $methods
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    abstract public function map ($methods, string $pattern, $handler);
}

<?php

namespace Resilient\Design;

/**
 * RouteableInterface interface.
 */
interface RouteableInterface
{
    /**
     * get function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function get(string $pattern, $handler);

    /**
     * post function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function post(string $pattern, $handler);

    /**
     * put function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function put(string $pattern, $handler);

    /**
     * patch function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function patch(string $pattern, $handler);

    /**
     * delete function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function delete(string $pattern, $handler);

    /**
     * options function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function options(string $pattern, $handler);

    /**
     * any function.
     *
     * @access public
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function any(string $pattern, $handler);

    /**
     * map function.
     *
     * @access public
     * @param mixed $methods
     * @param string $pattern
     * @param mixed $handler
     * @return mixed
     */
    public function map($methods, string $pattern, $handler);
}

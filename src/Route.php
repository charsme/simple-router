<?php

namespace Resilient;

use \Resilient\Traits\Bindable;

class Route
{
    use Bindable;
    
    /**
     * method
     *
     * @var mixed
     * @access protected
     */
    protected $method;
    
    /**
     * pattern
     *
     * @var mixed
     * @access protected
     */
    protected $pattern;
    
    /**
     * handler
     *
     * @var mixed
     * @access protected
     */
    protected $handler;
    
    /**
     * group
     *
     * @var mixed
     * @access protected
     */
    protected $group;
    
    /**
     * identifier
     *
     * @var mixed
     * @access protected
     */
    protected $identifier;

    /**
     * args
     *
     * @var mixed
     * @access protected
     */
    protected $args;

    /**
     * __construct function.
     *
     * @access public
     * @param string $method
     * @param string $pattern
     * @param mixed $handler
     * @param string $group (default: '')
     * @param string $identifier (default: '')
     */
    public function __construct(string $method, string $pattern, $handler, $group = '', $identifier = '')
    {
        $this->method = $method;
        $this->pattern = $pattern;
        $this->handler = $handler;
        $this->group = $group;
        $this->identifier = $identifier;
    }

    /**
     * getMethod function.
     *
     * @access public
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * setMethod function.
     *
     * @access public
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * getPattern function.
     *
     * @access public
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * setPattern function.
     *
     * @access public
     * @param string $pattern
     * @return $this
     */
    public function setPattern(string $pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * getHandler function.
     *
     * @access public
     * @return callable|string
     */
    public function getHandler()
    {
        return $this->handler;
    }


    /**
     * setHandler function.
     *
     * @access public
     * @param string $handler
     * @return $this
     */
    public function setHandler(string $handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * getGroup function.
     *
     * @access public
     * @return array
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * setGroup function.
     *
     * @access public
     * @param string $group
     * @return $this
     */
    public function setGroup(string $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * getIdentifier function.
     *
     * @access public
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * setIdentifier function.
     *
     * @access public
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * setArgs function.
     *
     * @access public
     * @param mixed $args
     * @return $this
     */
    public function setArgs($args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * getArgs function.
     *
     * @access public
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }
}

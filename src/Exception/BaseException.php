<?php

namespace Resilient\Exception;

use Exception;
use \Psr\Http\Message\UriInterface;

class BaseException extends Exception
{
    protected $uri;

    public function __construct(UriInterface $uri)
    {
        parent::__construct();
        $this->uri = $uri;
    }

    public function getUri()
    {
        return $this->uri;
    }
}
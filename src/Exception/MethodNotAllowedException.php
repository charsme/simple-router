<?php

namespace Resilient\Exception\BaseException;

use \Psr\Http\Message\UriInterface;

class MethodNotAllowedException extends BaseException
{
    protected $message = ' Forbidden Request';

    public function __construct(UriInterface $uri)
    {
        parent::__construct($uri);
    }
}
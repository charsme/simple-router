<?php

namespace Resilient\Exception\BaseException;

use \Psr\Http\Message\UriInterface;

class NotFoundException extends BaseException
{
    protected $message = ' Not Found';

    public function __construct(UriInterface $uri, $apiHandler = null)
    {
        parent::__construct($uri);

        if (is_callable($apiHandler))
        {
            $data = [
                'status' => 404,
                'message' => $this->getMessage()
            ];

            $apiHandler->report($apiHandler->payload($data));
        }
    }

}
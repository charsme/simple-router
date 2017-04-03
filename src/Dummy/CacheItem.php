<?php

namespace Resilient\Dummy;

use Psr\Cache\CacheItemInterface;

class Cacheitem implements CacheItemInterface
{

    /**
     * {@inheritdoc}
     */
    public function getKey ()
    {
        return 'no-key';
    }

    /**
     * {@inheritdoc}
     */
    public function get ()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit ()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function set ($value)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt ($expiration)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter ($time)
    {
        return $this;
    }
}

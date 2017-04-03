<?php

namespace Resilient;

use \Resilient\Http\Uri;
use \Resilient\Factory\Uri as FactoryUri;
use BadMethodCallException;

class UriTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers FactoryUri::createFromString
     * @covers Uri::getScheme
     * @covers Uri::getUserInfo
     * @covers Uri::getHost
     * @covers Uri::getPort
     * @covers Uri::getPath
     * @covers Uri::getQuery
     * @covers Uri::getFragment
     * @covers Uri::getBaseUrl
     * @covers Uri::withHost
     * @covers Uri::withUserInfo
     * @covers Uri::withPort
     * @covers Uri::withScheme
     * @covers Uri::withPath
     * @covers Uri::withQuery
     * @covers Uri::withString
     * @covers Uri::withFragment
     * @covers Uri::filterScheme
     * @covers Uri::filterPort
     * @covers Uri::filterPath
     * @covers Uri::filterQuery
     * @covers Uri::hasStandardPort
     */

    public function testMethod()
    {
        $string = 'https://username:password@www.example.com:85/kerap/254?page=1#idkomentar';

        $uri = FactoryUri::createFromString($string);

        $this->assertInstanceOf(Uri::class, $uri);

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('username:password', $uri->getUserInfo());
        $this->assertEquals('www.example.com', $uri->getHost());
        $this->assertEquals('85', $uri->getPort());
        $this->assertEquals('/kerap/254', $uri->getPath());
        $this->assertEquals('page=1', $uri->getQuery());
        $this->assertEquals('idkomentar', $uri->getFragment());
        $this->assertEquals('https://username:password@www.example.com:85', $uri->getBaseUrl());
        $this->assertEquals($string, (string) $uri);

        $uri2 = $uri->withHost('www.phpunit.de')
                    ->withUserInfo('user2:pass2')
                    ->withPort(80)
                    ->withScheme('http')
                    ->withPath('/path/kerap/258')
                    ->withQuery('content=false&delimiter=default')
                    ->withFragment('bodynya');

        $this->assertNotEquals($uri, $uri2);

        $this->assertEquals('http://user2:pass2@www.phpunit.de/path/kerap/258?content=false&delimiter=default#bodynya', (string) $uri2);
    }
}

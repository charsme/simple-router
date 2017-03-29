<?php

namespace Resilient;

use \Resilient\Http\Uri;
use \Resilient\Factory\Uri as UriFactory;
use BadMethodCallException;

class UriTest extends \PHPUnit\Framework\TestCase
{
    public function testMethod()
    {
        $string = 'https://username:password@www.example.com:85/kerap/254?page=1#idkomentar';
        
        $uri = UriFactory::createFromString($string);
        
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

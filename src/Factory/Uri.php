<?php

namespace Resilient\Factory;

use InvalidArgumentException;
use \Psr\Http\Message\UriInterface;

class Uri
{
    /**
     * Create uri Instance from header.
     *
     * @access public
     * @static
     * @return Resilient\Http\Uri
     */
    public static function createFromServer ($serv)
    {
        $scheme = isset($serv['HTTPS']) ? 'https://' : 'http://';
        $host = !empty($serv['HTTP_HOST']) ? $serv['HTTP_HOST'] : $serv['SERVER_NAME'];
        $port = empty($serv['SERVER_PORT']) ? $serv['SERVER_PORT'] : null;

        $path = (string) parse_url ('http://www.example.com/' . $serv['REQUEST_URI'], PHP_URL_PATH);

        $query = empty($serv['QUERY_STRING']) ? parse_url ('http://example.com' . $serv['REQUEST_URI'], PHP_URL_QUERY) : $serv['QUERY_STRING'];

        $fragment = '';

        $user = !empty($serv['PHP_AUTH_USER']) ? $serv['PHP_AUTH_USER'] : '';
        $password = !empty($serv['PHP_AUTH_PW']) ? $serv['PHP_AUTH_PW'] : '';

        if (empty($user) && empty($password) && !empty($serv['HTTP_AUTHORIZATION'])) {
            list($user, $password) = explode (':', base64_decode (substr ($serv['HTTP_AUTHORIZATION'], 6)));
        }

        $uri = new \Resilient\Http\Uri ($scheme, $host, $port, $path, $query, $fragment, $user, $password);

        return $uri;
    }


    /**
     * Create Uri Instance from string http://www.example.com/url/path.html
     *
     * @access public
     * @static
     * @param string $uri
     * @return Resilient\Http\Uri
     */
    public static function createFromString (string $uri)
    {
        $parts = parse_url ($uri);
        $scheme = isset($parts['scheme']) ? $parts['scheme'] : '';
        $user = isset($parts['user']) ? $parts['user'] : '';
        $pass = isset($parts['pass']) ? $parts['pass'] : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $port = isset($parts['port']) ? $parts['port'] : null;
        $path = isset($parts['path']) ? $parts['path'] : '';
        $query = isset($parts['query']) ? $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? $parts['fragment'] : '';

        return new \Resilient\Http\Uri ($scheme, $host, $port, $path, $query, $fragment, $user, $pass);
    }
}

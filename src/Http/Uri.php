<?php

namespace Resilient\Http;

use InvalidArgumentException;
use \Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{


    /**
     * Uri scheme (without "://" suffix)
     *
     * @var string
     */
    protected $scheme = '';

    /**
     * Uri user
     *
     * @var string
     */
    protected $user = '';

    /**
     * Uri password
     *
     * @var string
     */
    protected $password = '';

    /**
     * Uri host
     *
     * @var string
     */
    protected $host = '';

    /**
     * Uri port number
     *
     * @var null|int
     */
    protected $port;

    /**
     * Uri base path
     *
     * @var string
     */
    protected $basePath = '';

    /**
     * Uri path
     *
     * @var string
     */
    protected $path = '';

    /**
     * Uri query string (without "?" prefix)
     *
     * @var string
     */
    protected $query = '';

    /**
     * Uri fragment string (without "#" prefix)
     *
     * @var string
     */
    protected $fragment = '';

    /**
     * Instance new Uri.
     *
     * @param string $scheme   Uri scheme.
     * @param string $host     Uri host.
     * @param int    $port     Uri port number.
     * @param string $path     Uri path.
     * @param string $query    Uri query string.
     * @param string $fragment Uri fragment.
     * @param string $user     Uri user.
     * @param string $password Uri password.
     */
    public function __construct(
        $scheme,
        $host,
        $port = null,
        $path = '/',
        $query = '',
        $fragment = '',
        $user = '',
        $password = ''
    ) {
        $this->scheme = $this->filterScheme($scheme);
        $this->host = $host;
        $this->port = $this->filterPort($port);
        $this->path = empty($path) ? '/' : $this->filterPath($path);
        $this->query = $this->filterQuery($query);
        $this->fragment = $this->filterQuery($fragment);
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        $userInfo = $this->getUserInfo();
        $host = $this->getHost();
        $port = $this->getPort();

        return ($userInfo ? $userInfo . '@' : '') . $host . ($port !== null ? ':' . $port : '');
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        return $this->user . ($this->password ? ':' . $this->password : '');
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->port !== null && !$this->hasStandardPort() ? $this->port : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);
        $clone = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->user = $user;
        $clone->password = $password ? $password : '';

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = $host;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        $port = $this->filterPort($port);
        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        $clone = clone $this;
        $clone->path = $this->filterPath($path);

        // if the path is absolute, then clear basePath
        if (substr($path, 0, 1) == '/') {
            $clone->basePath = '';
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        return $this->withString($query);
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        return $this->withString($fragment, 'fragment');
    }
    
    /**
     * withString function.
     * 
     * @access protected
     * @param string $string
     * @param string $name (default: 'query')
     * @return Uri
     */
    protected function withString($string, $name = 'query')
    {
        if (!is_string($string) && !method_exists($string, '__toString')) {
            throw new InvalidArgumentException('Uri fragment must be a string');
        }
        $string = ltrim((string) $string, '#');
        $clone = clone $this;
        $clone->$name = $this->filterQuery($string);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $basePath = $this->getBasePath();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        $path = $basePath . '/' . ltrim($path, '/');

        return ($scheme ? $scheme . ':' : '')
            . ($authority ? '//' . $authority : '')
            . $path
            . ($query ? '?' . $query : '')
            . ($fragment ? '#' . $fragment : '');
    }

    /*
        END OF UriInterface Implementation
    */

    /**
     * filter scheme given to only allow certain scheme, no file:// or ftp:// or other scheme because its http message uri interface
     *
     * @access protected
     * @param string $scheme
     * @return string $scheme
     * @throws InvalidArgumentException if not corret scheme is present
     */
    protected function filterScheme(string $scheme)
    {
        static $valid = [
            '' => true,
            'https' => true,
            'http' => true,
        ];

        $scheme = str_replace('://', '', strtolower($scheme));
        if (!isset($valid[$scheme])) {
            throw new InvalidArgumentException('Uri scheme must be one of: "", "https", "http"');
        }

        return $scheme;
    }


    /**
     * Filter allowable port to minimize risk
     *
     * @access protected
     * @param integer|null $port
     * @return null|integer $port
     * @throws InvalidArgumentException for incorrect port assigned
     */
    protected function filterPort($port)
    {
        if (is_null($port) || (is_integer($port) && ($port >= 1 && $port <= 65535))) {
            return $port;
        }

        throw new InvalidArgumentException('Uri port must be null or an integer between 1 and 65535 (inclusive)');
    }

    /**
     * Path allowed chars filter, no weird path on uri yes?.
     *
     * @access protected
     * @param string $path
     * @return string of cleared path
     */
    protected function filterPath($path)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function($match) {
                return rawurlencode($match[0]);
            },
            $path
        );
    }

    /**
     * replace query to clear not allowed chars
     *
     * @access protected
     * @param string $query
     * @return string of replaced query
     */
    protected function filterQuery($query)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function($match) {
                return rawurlencode($match[0]);
            },
            $query
        );
    }

    /**
     * cek if current uri scheme use standard port
     *
     * @access protected
     * @return boolean
     */
    protected function hasStandardPort()
    {
        return ($this->scheme === 'http' && $this->port === 80) || ($this->scheme === 'https' && $this->port === 443);
    }


    /**
     * get BasePath property.
     *
     * @access public
     * @return string basePath
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Set BasePath Function to rewrite request.
     *
     * @access public
     * @param string $basePath
     * @return void
     */
    public function withBasePath(string $basePath)
    {
        $this->basePath = $basePath;
    }


    /**
     * get Base Url
     *
     * @access public
     * @return string
     */
    public function getBaseUrl()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $basePath = $this->getBasePath();

        if ($authority && substr($basePath, 0, 1) !== '/') {
            $basePath = $basePath . '/' . $basePath;
        }

        return ($scheme ? $scheme . ':' : '')
            . ($authority ? '//' . $authority : '')
            . rtrim($basePath, '/');
    }

    /**
     * Create uri Instance from header $_SERVER.
     *
     * @access public
     * @static
     * @return Uri
     */
    public static function createFromServer($serv)
    {
        $scheme = isset($serv['HTTPS']) ? 'https://' : 'http://';
        $host = empty($serv['HTTP_HOST']) ? $serv['HTTP_HOST'] : $serv['SERVER_NAME'];
        $port = empty($serv['SERVER_PORT']) ? $serv['SERVER_PORT'] : null;

        //Path
        $scriptName = parse_url($serv['SCRIPT_NAME'], PHP_URL_PATH);
        $scriptPath = dirname($scriptName);

        $requestUri = (string) parse_url('http://www.example.com/' . $_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (stripos($requestUri, $scriptName) === 0) {
            $basePath = $scriptName;
        } elseif ($scriptPath !== '/' && stripos($requestUri, $scriptPath) === 0) {
            $basePath = $scriptPath;
        }

        if (empty($basePath)) {
            $path = $requestUri;
            $basePath = '';
        } else {
            $path = ltrim(substr($requestUri, strlen($basePath)), '/');
        }

        $query = empty($serv['QUERY_STRING']) ? parse_url('http://example.com' . $serv['REQUEST_URI'], PHP_URL_QUERY) : $serv['QUERY_STRING'];

        $fragment = '';

        $user = !empty($serv['PHP_AUTH_USER']) ? $serv['PHP_AUTH_USER'] : '';
        $password = !empty($serv['PHP_AUTH_PW']) ? $serv['PHP_AUTH_PW'] : '';

        if (empty($user) && empty($password) && !empty($serv['HTTP_AUTHORIZATION'])) {
            list($user, $password) = explode(':', base64_decode(substr($serv['HTTP_AUTHORIZATION'], 6)));
        }

        $uri = new static($scheme, $host, $port, $path, $query, $fragment, $user, $password);

        if ($basePath) {
            $uri->withBasePath($basePath);
        }

        return $uri;
    }


    /**
     * Create Uri Instance from string http://www.example.com/url/path.html
     *
     * @access public
     * @static
     * @param string $uri
     * @return Uri
     */
    public static function createFromString(string $uri)
    {
        $parts = parse_url($uri);
        $scheme = isset($parts['scheme']) ? $parts['scheme'] : '';
        $user = isset($parts['user']) ? $parts['user'] : '';
        $pass = isset($parts['pass']) ? $parts['pass'] : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $port = isset($parts['port']) ? $parts['port'] : null;
        $path = isset($parts['path']) ? $parts['path'] : '';
        $query = isset($parts['query']) ? $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? $parts['fragment'] : '';

        return new static($scheme, $host, $port, $path, $query, $fragment, $user, $pass);
    }
}

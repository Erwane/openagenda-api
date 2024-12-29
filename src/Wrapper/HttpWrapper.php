<?php
declare(strict_types=1);

namespace OpenAgenda\Wrapper;

use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;
use Psr\Http\Client\ClientInterface;

abstract class HttpWrapper implements HttpWrapperInterface
{
    /**
     * Http client
     *
     * @var \Psr\Http\Client\ClientInterface
     */
    protected $http;

    /**
     * Create the wrapper for this $http client.
     *
     * @param \Psr\Http\Client\ClientInterface $http PSR-18 http client.
     */
    public function __construct(ClientInterface $http)
    {
        $this->http = $http;
    }

    /**
     * Build Uri from string or uri.
     *
     * @param \League\Uri\Contracts\UriInterface|\Psr\Http\Message\UriInterface|string $uri Base uri to build.
     * @return \League\Uri\Contracts\UriInterface
     */
    public function buildUri($uri): UriInterface
    {
        if (is_string($uri)) {
            $uri = Uri::createFromString($uri);
        } else {
            $uri = Uri::createFromUri($uri);
        }

        return $uri;
    }
}

<?php
declare(strict_types=1);

namespace OpenAgenda\Wrapper;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpWrapperInterface extends ClientInterface
{
    /**
     * Do a HEAD request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $params Request options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function head($uri, array $params = []): ResponseInterface;

    /**
     * Do a GET request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $params Request options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($uri, array $params = []): ResponseInterface;

    /**
     * Do a POST request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $data Request data
     * @param array $params Request options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function post($uri, array $data, array $params = []): ResponseInterface;

    /**
     * Do a PATCH request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $data Request data
     * @param array $params Request options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function patch($uri, array $data, array $params = []): ResponseInterface;

    /**
     * Do a DELETE request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $params Request options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete($uri, array $params = []): ResponseInterface;
}

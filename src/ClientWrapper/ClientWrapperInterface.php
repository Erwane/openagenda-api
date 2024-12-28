<?php
declare(strict_types=1);

namespace OpenAgenda\ClientWrapper;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

interface ClientWrapperInterface extends ClientInterface
{
    /**
     * Do a HEAD request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $options Request options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function head($uri, array $options = []): ResponseInterface;

    /**
     * Do a GET request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $data Request data
     * @param array $options Request options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($uri, array $data = [], array $options = []): ResponseInterface;

    /**
     * Do a POST request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $data Request data
     * @param array $options Request options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function post($uri, array $data, array $options = []): ResponseInterface;

    /**
     * Do a PATCH request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $data Request data
     * @param array $options Request options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function patch($uri, array $data, array $options = []): ResponseInterface;

    /**
     * Do a DELETE request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $data Request data
     * @param array $options Request options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete($uri, array $data, array $options = []): ResponseInterface;
}

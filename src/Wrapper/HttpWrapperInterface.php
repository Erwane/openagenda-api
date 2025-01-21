<?php
declare(strict_types=1);

/**
 * OpenAgenda API client.
 * Copyright (c) Erwane BRETON
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Erwane BRETON
 * @see         https://github.com/Erwane/openagenda-api
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace OpenAgenda\Wrapper;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Psr-18 Http client wrapper interface.
 */
interface HttpWrapperInterface
{
    public const USER_AGENT = 'OpenAgenda-ESdk/3.x';

    /**
     * Do a HEAD request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $params Request options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Wrapper\HttpWrapperException
     */
    public function head(UriInterface|string $uri, array $params = []): ResponseInterface;

    /**
     * Do a GET request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $params Request options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Wrapper\HttpWrapperException
     */
    public function get(UriInterface|string $uri, array $params = []): ResponseInterface;

    /**
     * Do a POST request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $data Request data
     * @param array $params Request options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Wrapper\HttpWrapperException
     */
    public function post(UriInterface|string $uri, array $data, array $params = []): ResponseInterface;

    /**
     * Do a PATCH request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $data Request data
     * @param array $params Request options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Wrapper\HttpWrapperException
     */
    public function patch(UriInterface|string $uri, array $data, array $params = []): ResponseInterface;

    /**
     * Do a DELETE request and return ResponseInterface.
     *
     * @param \Psr\Http\Message\UriInterface|string $uri Endpoint URI.
     * @param array $params Request options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Wrapper\HttpWrapperException
     */
    public function delete(UriInterface|string $uri, array $params = []): ResponseInterface;
}

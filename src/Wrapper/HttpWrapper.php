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

use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;

/**
 * Abstract HttpWrapper
 */
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
     * The HttpWrapper SHOULD set PSR-18 Http client in $this->http
     *
     * @param array $params The PSR-18 Http client params.
     */
    public function __construct(array $params = [])
    {
        // Create PSR-18 Http client and store it.
        // $this->http = new Psr18Client(params)
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

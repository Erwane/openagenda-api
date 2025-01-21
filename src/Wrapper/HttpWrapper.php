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

use Psr\Http\Client\ClientInterface;

/**
 * Abstract HttpWrapper
 */
abstract class HttpWrapper implements HttpWrapperInterface
{
    /**
     * PSR-18 Http client
     *
     * @var \Psr\Http\Client\ClientInterface
     */
    protected ClientInterface $http;

    /**
     * Create the wrapper for this $http client.
     * The HttpWrapper SHOULD set PSR-18 Http client in $this->http
     *
     * @param array $params The PSR-18 Http client params.
     * @codeCoverageIgnore
     */
    public function __construct(array $params = [])
    {
        // Create PSR-18 Http client and store it.
        // $this->http = new Psr18Client(params)
    }
}

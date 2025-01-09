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
namespace OpenAgenda\Test;

use OpenAgenda\Client;
use OpenAgenda\OpenAgenda;
use OpenAgenda\Wrapper\HttpWrapper;
use PHPUnit\Framework\TestCase;

class OpenAgendaTestCase extends TestCase
{
    /**
     * @return array{0: \OpenAgenda\OpenAgenda, 1: \OpenAgenda\Wrapper\HttpWrapper|\PHPUnit\Framework\MockObject\MockObject}
     */
    protected function oa(array $params = []): array
    {
        $wrapper = $this->getMockBuilder(HttpWrapper::class)->getMock();

        $params += [
            'public_key' => 'publicKey',
            'secret_key' => 'secretKey',
            'wrapper' => $wrapper,
        ];

        return [
            new OpenAgenda($params),
            $wrapper,
        ];
    }

    /**
     * @return \OpenAgenda\Wrapper\HttpWrapper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function clientWrapper(array $params = [])
    {
        $wrapper = $this->getMockBuilder(HttpWrapper::class)->getMock();

        $params += [
            'public_key' => 'publicKey',
            'secret_key' => 'secretKey',
            'wrapper' => $wrapper,
        ];

        $client = new Client($params);
        OpenAgenda::setClient($client);

        return $wrapper;
    }
}

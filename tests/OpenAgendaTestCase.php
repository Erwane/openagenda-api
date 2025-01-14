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
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @coversNothing
 */
class OpenAgendaTestCase extends TestCase
{
    /**
     * @return array{0: \OpenAgenda\OpenAgenda, 1: \OpenAgenda\Client|\PHPUnit\Framework\MockObject\MockObject}
     */
    protected function oa(array $params = []): array
    {
        $wrapper = $this->getMockBuilder(HttpWrapper::class)->getMock();
        $params += [
            'public_key' => 'publicKey',
            'secret_key' => 'secretKey',
            'wrapper' => $wrapper,
        ];
        $oa = new OpenAgenda($params);

        // Overwrite Client
        $client = $this->getMockBuilder(Client::class)
            ->setConstructorArgs([$params])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods([
                'head', 'get', 'post', 'patch', 'delete',
            ])
            ->getMock();

        OpenAgenda::setClient($client);

        return [$oa, $client, $wrapper];
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
        if (isset($params['auth'])) {
            $cache = $this->createMock(CacheInterface::class);
            $cache->expects($this->any())
                ->method('get')
                ->willReturn('my authorization cache');
            $params['cache'] = $cache;
        }

        $client = new Client($params);
        OpenAgenda::setClient($client);

        return $wrapper;
    }

    /**
     * @param \OpenAgenda\Client|\PHPUnit\Framework\MockObject\MockObject $client
     * @param \PHPUnit\Framework\MockObject\Rule\InvokedCount $count
     * @param string $method
     * @param array|int $payload
     * @param array $urlExpect
     * @param array $requestData
     */
    public function assertClientCall(
        Client $client,
        InvokedCount $count,
        string $method,
        $payload,
        array $urlExpect = [],
        array $requestData = []
    ) {
        if (!is_array($payload) && !is_int($payload)) {
            $this->fail('fix payload');
        }
        $client->expects($count)
            ->method($method)
            ->with($this->callback(function ($url) use ($urlExpect) {
                parse_str((string)parse_url($url, PHP_URL_QUERY), $q);
                $this->assertSame($urlExpect['path'] ?? '/v2', parse_url($url, PHP_URL_PATH));
                $this->assertSame($urlExpect['query'] ?? [], $q);

                return true;
            }), $requestData)
            ->willReturn($payload);
    }
}

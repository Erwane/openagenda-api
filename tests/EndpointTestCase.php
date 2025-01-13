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

use GuzzleHttp\Psr7\Response;
use OpenAgenda\Client;
use OpenAgenda\OpenAgenda;
use OpenAgenda\Wrapper\HttpWrapper;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class EndpointTestCase extends TestCase
{
    /**
     * @var (\object&\PHPUnit\Framework\MockObject\MockObject)|\OpenAgenda\Wrapper\HttpWrapper|(\OpenAgenda\Wrapper\HttpWrapper&\object&\PHPUnit\Framework\MockObject\MockObject)|(\OpenAgenda\Wrapper\HttpWrapper&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $wrapper;

    /**
     * @var (\object&\PHPUnit\Framework\MockObject\MockObject)|\OpenAgenda\Client|(\OpenAgenda\Client&\object&\PHPUnit\Framework\MockObject\MockObject)|(\OpenAgenda\Client&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wrapper = $this->getMockForAbstractClass(
            HttpWrapper::class,
            [],
            '',
            false,
            true,
            true,
            ['head', 'get', 'post', 'patch', 'delete']
        );

        $this->client = $this->getMockBuilder(Client::class)
            ->setConstructorArgs([
                [
                    'public_key' => 'publicKey',
                    'wrapper' => $this->wrapper,
                ],
            ])
            ->onlyMethods(['getAccessToken'])
            ->getMock();

        OpenAgenda::setClient($this->client);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        OpenAgenda::resetClient();
    }

    /**
     * Mock request.
     *
     * @param bool $auth getAccessToken return a fake token
     * @param string $method Method to mock
     * @param array $args Request `with` arguments
     * @param array $response Response in array [code, 'body']
     * @return void
     */
    protected function mockRequest(bool $auth, string $method, array $args, array $response)
    {
        if ($auth) {
            $this->client->expects($this->once())
                ->method('getAccessToken')
                ->willReturn('authorization-key');
        }

        $this->wrapper->expects($this->once())
            ->method($method)
            ->with(...$args)
            ->willReturn(new Response($response[0], ['Content-Type' => 'application/json'], $response[1]));
    }
}

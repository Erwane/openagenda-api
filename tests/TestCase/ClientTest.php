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
namespace OpenAgenda\Test\TestCase;

use Exception;
use GuzzleHttp\Psr7\Response;
use OpenAgenda\Client;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\Utility\FileResource;
use OpenAgenda\Wrapper\HttpWrapper;
use OpenAgenda\Wrapper\HttpWrapperException;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * Client tests
 *
 * @uses   \OpenAgenda\Client
 * @covers \OpenAgenda\Client
 */
class ClientTest extends TestCase
{
    /**
     * @var (\object&\PHPUnit\Framework\MockObject\MockObject)|\OpenAgenda\Wrapper\HttpWrapper|(\OpenAgenda\Wrapper\HttpWrapper&\object&\PHPUnit\Framework\MockObject\MockObject)|(\OpenAgenda\Wrapper\HttpWrapper&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $wrapper;

    /**
     * @var \OpenAgenda\Client
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

        $this->client = new Client([
            'public_key' => 'publicKey',
            'secret_key' => 'secretKey',
            'wrapper' => $this->wrapper,
        ]);
    }

    public function testConstructPublicKeyMissing()
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Missing `public_key`.');
        new Client();
    }

    public function testConstructWrapperMissing()
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Invalid or missing `wrapper`.');
        new Client(['public_key' => 'publicKey']);
    }

    public function testGetWrapper(): void
    {
        $this->assertSame($this->wrapper, $this->client->getWrapper());
    }

    public static function dataWrapperException()
    {
        return [
            ['head'],
            ['get'],
            ['post'],
            ['patch'],
            ['delete'],
        ];
    }

    /**
     * @dataProvider dataWrapperException
     * @covers       \OpenAgenda\Client::_doRequest
     */
    public function testWrapperException($method): void
    {
        $exception = new HttpWrapperException("Wrapper $method request failed. previous exception", 500);

        $this->wrapper->expects($this->once())
            ->method($method)
            ->willThrowException($exception);

        // For PATCH and DELETE getAccessToken()
        $this->wrapper->expects($this->any())
            ->method('post')
            ->willReturn(new Response(200, [], json_encode([
                'access_token' => 'my authorization token',
                'expires_in' => 3600,
            ])));

        try {
            $this->client->$method('https://example.com');
        } catch (OpenAgendaException $e) {
            $this->assertInstanceOf(OpenAgendaException::class, $e);
            $this->assertEquals(500, $e->getCode());
            $this->assertEquals("Wrapper $method request failed. previous exception", $e->getMessage());
            $this->assertInstanceOf(HttpWrapperException::class, $e->getPrevious());
        }
    }

    /**
     * @covers \OpenAgenda\Client::payload
     * @covers \OpenAgenda\OpenAgendaException
     */
    public function testResponseFailed(): void
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/delete-not-found.json');
        $this->wrapper->expects($this->once())
            ->method('get')
            ->willReturn(new Response(404, ['content-type' => 'application/json'], $payload));

        try {
            $this->client->get('https://api.openagenda.com/v2/agendas');
        } catch (Exception $e) {
            $this->assertInstanceOf(OpenAgendaException::class, $e);
            $this->assertEquals(404, $e->getCode());
            $this->assertEquals('location not found', $e->getMessage());
            $this->assertInstanceOf(Response::class, $e->getResponse());
            $newPayload = json_decode($payload, true) + ['_status' => 404, '_success' => false];
            $this->assertEquals($newPayload, $e->getPayload());
        }
    }

    public function testHead(): void
    {
        $this->wrapper->expects($this->once())
            ->method('head')
            ->with(
                'https://api.openagenda.com/v2/agendas/123/locations/456',
                ['headers' => ['key' => 'publicKey']]
            )
            ->willReturn(new Response(201, ['content-type' => 'application/json'], ''));

        $response = $this->client->head(
            'https://api.openagenda.com/v2/agendas/123/locations/456'
        );
        $this->assertEquals(201, $response);
    }

    public function testGet(): void
    {
        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas',
                ['headers' => ['key' => 'publicKey', 'X-Custom' => 'testing']]
            )
            ->willReturn(new Response(201, ['content-type' => 'application/json'], '{"json":"object"}'));

        $response = $this->client->get(
            'https://api.openagenda.com/v2/agendas',
            ['headers' => ['X-Custom' => 'testing']]
        );

        $this->assertEquals([
            '_status' => 201,
            '_success' => true,
            'json' => 'object',
        ], $response);
    }

    public static function dataHasAuthenticationHeader()
    {
        return [
            [
                'post',
                [
                    'https://example.com',
                    ['uid' => 123],
                    ['headers' => ['nonce' => 1234567890, 'access-token' => 'testing']],
                ],
            ],
            [
                'patch',
                [
                    'https://example.com',
                    ['uid' => 123],
                    ['headers' => ['nonce' => 1234567890, 'access-token' => 'testing']],
                ],
            ],
            [
                'delete',
                [
                    'https://example.com',
                    ['headers' => ['nonce' => 1234567890, 'access-token' => 'testing']],
                ],
            ],
        ];
    }

    /** @dataProvider dataHasAuthenticationHeader */
    public function testPostHasAuthenticationHeader($method, $expected): void
    {
        $client = $this->createPartialMock(Client::class, ['_addAuthenticationHeaders', '_doRequest']);
        $client->expects($this->once())
            ->method('_addAuthenticationHeaders')
            ->willReturn(['headers' => ['nonce' => 1234567890, 'access-token' => 'testing']]);

        $client->expects($this->once())
            ->method('_doRequest')
            ->with($method, $expected)
            ->willReturn(new Response(200, [], ''));

        $client->$method(
            'https://example.com',
            ['uid' => 123]
        );
    }

    public function testPost(): void
    {
        $this->wrapper->expects($this->exactly(2))
            ->method('post')
            ->withConsecutive(
                [
                    'https://api.openagenda.com/v2/requestAccessToken',
                    ['grant_type' => 'authorization_code', 'code' => 'secretKey'],
                ],
                [
                    'https://api.openagenda.com/v2/agendas',
                    ['uid' => 123],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                new Response(200, [], '{"access_token": "my authorization token"}'),
                new Response(200, ['content-type' => 'application/json'], '{"json":"object"}')
            );

        $response = $this->client->post(
            'https://api.openagenda.com/v2/agendas',
            ['uid' => 123]
        );

        $this->assertEquals([
            '_status' => 200,
            '_success' => true,
            'json' => 'object',
        ], $response);
    }

    public function testPatch(): void
    {
        $this->wrapper->expects($this->once())
            ->method('post')
            ->with(
                'https://api.openagenda.com/v2/requestAccessToken',
                ['grant_type' => 'authorization_code', 'code' => 'secretKey']
            )
            ->willReturn(new Response(200, [], '{"access_token": "my authorization token"}'));
        $this->wrapper->expects($this->once())
            ->method('patch')
            ->with(
                'https://api.openagenda.com/v2/agendas/123/locations/456',
                ['uid' => 123, 'name' => 'My agenda']
            )
            ->willReturn(new Response(200, ['content-type' => 'application/json'], '{"json":"object"}'));

        $response = $this->client->patch(
            'https://api.openagenda.com/v2/agendas/123/locations/456',
            ['uid' => 123, 'name' => 'My agenda']
        );

        $this->assertEquals([
            '_status' => 200,
            '_success' => true,
            'json' => 'object',
        ], $response);
    }

    public function testDelete(): void
    {
        $this->wrapper->expects($this->once())
            ->method('post')
            ->with(
                'https://api.openagenda.com/v2/requestAccessToken',
                ['grant_type' => 'authorization_code', 'code' => 'secretKey']
            )
            ->willReturn(new Response(200, [], '{"access_token": "my authorization token"}'));
        $this->wrapper->expects($this->once())
            ->method('delete')
            ->with(
                'https://api.openagenda.com/v2/agendas/123/locations/456'
            )
            ->willReturn(new Response(200, ['content-type' => 'application/json'], '{"json":"object"}'));

        $response = $this->client->delete(
            'https://api.openagenda.com/v2/agendas/123/locations/456'
        );

        $this->assertEquals([
            '_status' => 200,
            '_success' => true,
            'json' => 'object',
        ], $response);
    }

    public function testGetAccessTokenNoSecret(): void
    {
        $client = new Client(['public_key' => 'publicKey', 'wrapper' => $this->wrapper]);
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Missing secret_key');
        $client->getAccessToken();
    }

    public function testGetAccessTokenFailed(): void
    {
        $this->wrapper->expects($this->once())
            ->method('post')
            ->with(
                'https://api.openagenda.com/v2/requestAccessToken',
                [
                    'grant_type' => 'authorization_code',
                    'code' => 'secretKey',
                ]
            )
            ->willReturn(new Response(401, [], json_encode([
                'message' => 'Invalid key',
            ])));

        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionCode(401);
        $this->client->getAccessToken();
    }

    public function testGetAccessTokenNoCache(): void
    {
        $this->wrapper->expects($this->once())
            ->method('post')
            ->with(
                'https://api.openagenda.com/v2/requestAccessToken',
                [
                    'grant_type' => 'authorization_code',
                    'code' => 'secretKey',
                ]
            )->willReturn(new Response(200, [], json_encode([
                'access_token' => 'my authorization token',
                'expires_in' => 3600,
            ])));

        $token = $this->client->getAccessToken();

        $this->assertEquals('my authorization token', $token);
    }

    public function testGetAccessTokenWriteCache(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $client = new Client([
            'public_key' => 'publicKey',
            'secret_key' => 'secretKey',
            'wrapper' => $this->wrapper,
            'cache' => $cache,
        ]);

        $this->wrapper->expects($this->once())
            ->method('post')
            ->willReturn(new Response(200, [], json_encode([
                'access_token' => 'my authorization token',
                'expires_in' => 3600,
            ])));

        $cache->expects($this->once())
            ->method('set')
            ->with(
                'openagenda_api_access_token',
                'my authorization token',
                3600
            );
        $client->getAccessToken();
    }

    public function testGetAccessTokenFromCache(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $client = new Client([
            'public_key' => 'publicKey',
            'secret_key' => 'secretKey',
            'wrapper' => $this->wrapper,
            'cache' => $cache,
        ]);

        $this->wrapper->expects($this->never())
            ->method('post');

        $cache->expects($this->once())
            ->method('get')
            ->with(
                'openagenda_api_access_token',
                null
            )->willReturn('my authorization cache');

        $cache->expects($this->never())
            ->method('set');

        $token = $client->getAccessToken();
        $this->assertEquals('my authorization cache', $token);
    }
}

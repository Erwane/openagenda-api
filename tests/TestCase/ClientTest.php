<?php
declare(strict_types=1);

namespace OpenAgenda\Test\TestCase;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use OpenAgenda\Client;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\Utility\FileResource;
use OpenAgenda\Wrapper\HttpWrapper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
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

    public function testResponseFailed(): void
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/delete-not-found.json');
        $this->wrapper->expects($this->once())
            ->method('get')
            ->willReturn(new Response(404, ['content-type' => 'application/json'], $payload));

        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('location not found');

        $this->client->get('https://api.openagenda.com/v2/agendas');
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
                    ['id' => 123],
                    ['headers' => ['access-token' => 'my authorization token', 'nonce' => 1734957296123456]],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                new Response(200, [], '{"access_token": "my authorization token"}'),
                new Response(200, ['content-type' => 'application/json'], '{"json":"object"}')
            );

        $response = $this->client->post(
            'https://api.openagenda.com/v2/agendas',
            ['id' => 123]
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
                ['id' => 123, 'title' => 'My agenda'],
                ['headers' => ['access-token' => 'my authorization token', 'nonce' => 1734957296123456]]
            )
            ->willReturn(new Response(200, ['content-type' => 'application/json'], '{"json":"object"}'));

        $response = $this->client->patch(
            'https://api.openagenda.com/v2/agendas/123/locations/456',
            ['id' => 123, 'title' => 'My agenda']
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
                'https://api.openagenda.com/v2/agendas/123/locations/456',
                ['headers' => ['access-token' => 'my authorization token', 'nonce' => 1734957296123456]]
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

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
            'public_key' => 'testing',
            'secret_key' => 'secret',
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
        new Client(['public_key' => 'testing']);
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

    public function testGet(): void
    {
        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas',
                [
                    'headers' => [
                        'key' => 'testing',
                        'X-Custom' => 'testing',
                    ],
                ]
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

    public function testGetAccessTokenFailed(): void
    {
        $this->wrapper->expects($this->once())
            ->method('post')
            ->with(
                'https://api.openagenda.com/v2/requestAccessToken',
                [
                    'grant_type' => 'authorization_code',
                    'code' => 'secret',
                ],
                ['headers' => ['key' => 'testing']]
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
                    'code' => 'secret',
                ],
                ['headers' => ['key' => 'testing']]
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
            'public_key' => 'testing',
            'secret_key' => 'secret',
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
            'public_key' => 'testing',
            'secret_key' => 'secret',
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

    public function testPostNoToken(): void
    {
        $this->markTestSkipped();
        $client = $this->createPartialMock(Client::class, ['request']);

        $client->expects(self::once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openagenda.com/v2/agendas/1/events'
            )
            ->willReturn(new Response(200, [], '{"json":"object"}'));

        $response = $client->post('/agendas/1/events');

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testPostWithToken(): void
    {
        $this->markTestSkipped();
        $client = $this->createPartialMock(Client::class, ['request', 'nonce']);

        $client->expects(self::once())
            ->method('nonce')
            ->willReturn(1234567);

        $client->expects(self::once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openagenda.com/v2/agendas/1/events',
                [
                    'multipart' => [
                        ['name' => 'data', 'contents' => json_encode(['key' => 'value', 'array' => ['input']])],
                        ['name' => 'access_token', 'contents' => 'testing'],
                        ['name' => 'nonce', 'contents' => 1234567],
                    ],
                    'headers' => ['User-Agent' => 'Openagenda-api/2.1.0'],
                ]
            )
            ->willReturn(new Response(200, [], '{"json":"object"}'));

        $client->setAccessToken('testing');
        $response = $client->post('/agendas/1/events', ['data' => ['key' => 'value', 'array' => ['input']]]);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testDelete(): void
    {
        $this->markTestSkipped();
        $client = $this->createPartialMock(Client::class, ['request', 'nonce']);

        $client->expects(self::once())
            ->method('nonce')
            ->willReturn(1234567);

        $client->expects(self::once())
            ->method('request')
            ->with(
                'DELETE',
                new Uri('https://api.openagenda.com/v2/agendas/1/events/1'),
                [
                    'headers' => [
                        'Content-Type' => 'text/plain',
                        'nonce' => 1234567,
                        'access-token' => 'testing',
                        'User-Agent' => 'Openagenda-api/2.1.0',
                    ],
                ]
            )
            ->willReturn(new Response(200, [], '{"json":"object"}'));

        $client->setAccessToken('testing');
        $response = $client->delete('/agendas/1/events/1', ['headers' => ['Content-Type' => 'text/plain']]);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}

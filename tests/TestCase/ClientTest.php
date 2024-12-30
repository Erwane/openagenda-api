<?php
/**
 * @noinspection PhpParamsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace OpenAgenda\Test\TestCase;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use OpenAgenda\Client;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Wrapper\HttpWrapper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Collection\Collection;

/**
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

    /**
     * @test
     * @covers ::post
     * @covers ::setAccessToken
     * @covers ::_optionsToMultipart
     */
    public function testPostNoToken(): void
    {
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

    /**
     * @test
     * @covers ::post
     * @covers ::setAccessToken
     * @covers ::_optionsToMultipart
     */
    public function testPostWithToken(): void
    {
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

    /**
     * @test
     * @covers ::delete
     */
    public function testDelete(): void
    {
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

    /**
     * @test
     * @covers ::doRequest
     */
    public function testUserAgent(): void
    {
        $client = $this->createPartialMock(Client::class, ['request']);
        $client->expects(self::once())
            ->method('request')
            ->with(
                'GET',
                'https://api.openagenda.com/v2/agendas',
                [
                    'headers' => ['USER-agent' => 'Openagenda-api/2.1.0'],
                    'query' => ['key' => null],
                ]
            )
            ->willReturn(new Response(200, [], '{"json":"object"}'));

        $client->get('/agendas', ['headers' => ['USER-agent' => 'testing']]);
    }
}

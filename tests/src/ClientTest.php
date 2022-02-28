<?php
/**
 * @noinspection PhpParamsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace OpenAgenda\Test;

use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use OpenAgenda\Client;
use OpenAgenda\OpenAgendaException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @coversDefaultClass \OpenAgenda\Client
 */
class ClientTest extends TestCase
{
    /**
     * @test
     * @covers ::nonce
     */
    public function testNonce(): void
    {
        $client = new Client();

        $this->assertIsInt($client->nonce());
    }

    /**
     * @test
     * @covers ::doRequest
     */
    public function testDoQueryClientException(): void
    {
        $client = $this->createPartialMock(Client::class, ['request']);
        $client->expects(self::once())
            ->method('request')
            ->willThrowException(new RequestException(
                'client',
                new Request('GET', 'https://testing'),
                new Response(501, [], '{"message":"error"}')
            ));

        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('error');
        $this->expectExceptionCode(501);
        $client->get('/agendas');
    }

    /**
     * @test
     * @covers ::doRequest
     */
    public function testDoQueryGuzzleException(): void
    {
        $client = $this->createPartialMock(Client::class, ['request']);
        $client->expects(self::once())
            ->method('request')
            ->willThrowException(new InvalidArgumentException('GuzzleException', 1));

        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('GuzzleException');
        $this->expectExceptionCode(1);
        $client->get('/agendas');
    }

    /**
     * @test
     * @covers ::get
     * @covers ::setPublicKey
     */
    public function testGet(): void
    {
        $client = $this->createPartialMock(Client::class, ['request']);
        $client->expects(self::once())
            ->method('request')
            ->with(
                'GET',
                'https://api.openagenda.com/v2/agendas',
                [
                    'query' => ['size' => 2, 'key' => 'testing'],
                    'headers' => ['User-Agent' => 'Openagenda-api/2.1.0'],
                ]
            )
            ->willReturn(new Response(200, [], '{"json":"object"}'));

        $client->setPublicKey('testing');
        $response = $client->get('/agendas', ['query' => ['size' => 2]]);

        $this->assertInstanceOf(ResponseInterface::class, $response);
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

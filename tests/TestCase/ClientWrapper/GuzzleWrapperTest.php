<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace OpenAgenda\Test\TestCase\ClientWrapper;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use League\Uri\Uri;
use OpenAgenda\ClientWrapper\GuzzleWrapper;
use PHPUnit\Framework\TestCase;

/**
 * @uses   \OpenAgenda\ClientWrapper\GuzzleWrapper
 * @covers \OpenAgenda\ClientWrapper\GuzzleWrapper
 */
class GuzzleWrapperTest extends TestCase
{
    /**
     * @var (\GuzzleHttp\Client&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $http;

    /**
     * @var \GuzzleHttp\Psr7\Request
     */
    protected $request;

    /**
     * @var \League\Uri\Uri
     */
    protected $uri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->http = $this->getMockBuilder(Client::class)
            ->onlyMethods(['request'])
            ->getMock();

        $this->request = new Request('GET', 'https://example.com');
        $this->uri = Uri::createFromString('https://example.com');
    }

    public static function dataPrepareOptions(): array
    {
        $resource = fopen(__FILE__, 'r');

        return [
            [
                ['headers' => ['x-foo' => 'bar']],
                [],
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => \OpenAgenda\Client::USER_AGENT,
                        'x-foo' => 'bar',
                    ],
                    'allow_redirects' => false,
                ],
            ],
            [
                [],
                ['key' => 'value', 'other' => 23],
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => \OpenAgenda\Client::USER_AGENT,
                    ],
                    'allow_redirects' => false,
                    'json' => ['key' => 'value', 'other' => 23],
                ],
            ],
            [
                [],
                ['key' => 'value', 'image' => $resource],
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => \OpenAgenda\Client::USER_AGENT,
                    ],
                    'allow_redirects' => false,
                    'multipart' => [
                        [
                            'name' => 'key',
                            'contents' => 'value',
                        ],
                        [
                            'name' => 'image',
                            'contents' => $resource,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataPrepareOptions
     */
    public function testPrepareOptions($options, $data, $expected)
    {
        $wrapper = new GuzzleWrapper($this->http);

        $results = $wrapper->prepareOptions($options, $data);

        $this->assertEquals($expected, $results);
    }

    public function testSendRequest()
    {
        $http = $this->getMockBuilder(Client::class)
            ->onlyMethods(['sendRequest'])
            ->getMock();

        $http->expects($this->once())
            ->method('sendRequest')
            ->with($this->request);

        $wrapper = new GuzzleWrapper($http);
        $wrapper->sendRequest($this->request);
    }

    public function testMethodHead()
    {
        $this->http->expects($this->once())
            ->method('request')
            ->with(
                'HEAD',
                'https://example.com',
                [
                    'allow_redirects' => false,
                    'headers' => [
                        'User-Agent' => \OpenAgenda\Client::USER_AGENT,
                        'Accept' => 'application/json',
                    ],
                ]
            );
        $wrapper = new GuzzleWrapper($this->http);
        $wrapper->head($this->uri);
    }

    public function testMethodGet()
    {
        $this->http->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://example.com',
                [
                    'allow_redirects' => false,
                    'headers' => [
                        'User-Agent' => \OpenAgenda\Client::USER_AGENT,
                        'Accept' => 'application/json',
                    ],
                ]
            );
        $wrapper = new GuzzleWrapper($this->http);
        $wrapper->get($this->uri);
    }

    public function testMethodPost()
    {
        $this->http->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://example.com',
                [
                    'allow_redirects' => false,
                    'headers' => [
                        'User-Agent' => \OpenAgenda\Client::USER_AGENT,
                        'Accept' => 'application/json',
                        'x-foo' => 'bar',
                    ],
                    'json' => ['foo' => 'bar'],
                ]
            );
        $wrapper = new GuzzleWrapper($this->http);
        $wrapper->post($this->uri, ['foo' => 'bar'], ['headers' => ['x-foo' => 'bar']]);
    }

    public function testMethodPatch()
    {
        $this->http->expects($this->once())
            ->method('request')
            ->with(
                'PATCH',
                'https://example.com',
                [
                    'allow_redirects' => false,
                    'headers' => [
                        'User-Agent' => \OpenAgenda\Client::USER_AGENT,
                        'Accept' => 'application/json',
                        'x-foo' => 'bar',
                    ],
                    'json' => ['foo' => 'bar'],
                ]
            );
        $wrapper = new GuzzleWrapper($this->http);
        $wrapper->patch($this->uri, ['foo' => 'bar'], ['headers' => ['x-foo' => 'bar']]);
    }

    public function testMethodDelete()
    {
        $this->http->expects($this->once())
            ->method('request')
            ->with(
                'DELETE',
                'https://example.com',
                [
                    'allow_redirects' => false,
                    'headers' => [
                        'User-Agent' => \OpenAgenda\Client::USER_AGENT,
                        'Accept' => 'application/json',
                        'x-foo' => 'bar',
                    ],
                ]
            );
        $wrapper = new GuzzleWrapper($this->http);
        $wrapper->delete($this->uri, [], ['headers' => ['x-foo' => 'bar']]);
    }
}

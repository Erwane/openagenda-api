<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace OpenAgenda\Test\TestCase\Endpoint;

use GuzzleHttp\Psr7\Response;
use League\Uri\Uri;
use OpenAgenda\Client;
use OpenAgenda\Endpoint\Agendas;
use OpenAgenda\Test\Utility\FileResource;
use OpenAgenda\Wrapper\HttpWrapper;
use PHPUnit\Framework\TestCase;
use Ramsey\Collection\Collection;

/**
 * @uses   \OpenAgenda\Endpoint\Agendas
 * @covers \OpenAgenda\Endpoint\Agendas
 */
class AgendasTest extends TestCase
{
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

        $this->client = new Client([
            'public_key' => 'testing',
            'wrapper' => $this->wrapper,
        ]);
    }

    public static function dataGetUriSuccess(): array
    {
        return [
            [
                [],
                Uri::createFromString('https://api.openagenda.com/v2/agendas'),
            ],
            [
                [
                    'limit' => 2,
                    'fields' => ['summary', 'schema'],
                    'search' => 'Agenda',
                    'official' => true,
                    'slug' => 'agenda',
                    'id' => 12,
                    'network' => 34,
                    'sort' => 'created_desc',
                ],
                'https://api.openagenda.com/v2/agendas?size=2&fields%5B0%5D=summary&fields%5B1%5D=schema&search=Agenda&official=1&slug%5B0%5D=agenda&uid%5B0%5D=12&network=34&sort=createdAt.desc',
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriSuccess
     */
    public function testGetUriSuccess($params, $expected)
    {
        $endpoint = new Agendas($this->client, $params);
        $uri = $endpoint->getUri();
        $this->assertEquals(Uri::createFromString($expected), $uri);
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/agendas-ok.json');

        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas?size=2',
                [
                    'headers' => [
                        'key' => 'testing',
                    ],
                ]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $endpoint = new Agendas($this->client, ['limit' => 2]);

        $agendas = $endpoint->get();

        $this->assertInstanceOf(Collection::class, $agendas);
        $this->assertEquals(\OpenAgenda\Entity\Agenda::class, $agendas->getType());
        $this->assertCount(2, $agendas);
    }
}

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
namespace OpenAgenda\Test\TestCase\Endpoint;

use GuzzleHttp\Psr7\Response;
use OpenAgenda\Endpoint\Agendas;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;
use Ramsey\Collection\Collection;

/**
 * Endpoint\Agendas tests
 *
 * @uses   \OpenAgenda\Endpoint\Agendas
 * @covers \OpenAgenda\Endpoint\Agendas
 */
class AgendasTest extends EndpointTestCase
{
    public static function dataGetUriSuccess(): array
    {
        return [
            [
                [],
                [
                    'path' => '/v2/agendas',
                    'query' => [],
                ],
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
                [
                    'path' => '/v2/agendas',
                    'query' => [
                        'size' => '2',
                        'fields' => ['summary', 'schema'],
                        'search' => 'Agenda',
                        'official' => '1',
                        'slug' => ['agenda'],
                        'uid' => ['12'],
                        'network' => '34',
                        'sort' => 'createdAt.desc',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriSuccess
     */
    public function testGetUriSuccess($params, $expected)
    {
        $endpoint = new Agendas($params);
        $uri = $endpoint->getUri();
        $this->assertEquals($expected['path'], $uri->getPath());
        parse_str((string)$uri->getQuery(), $query);
        $this->assertEquals($expected['query'], $query);
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

        $endpoint = new Agendas(['limit' => 2]);

        $agendas = $endpoint->get();

        $this->assertInstanceOf(Collection::class, $agendas);
        $this->assertEquals(Agenda::class, $agendas->getType());
        $this->assertCount(2, $agendas);
    }
}

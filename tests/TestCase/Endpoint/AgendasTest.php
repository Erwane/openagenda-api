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

use OpenAgenda\Collection;
use OpenAgenda\Endpoint\Agendas;
use OpenAgenda\Entity\Agenda as AgendaEntity;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;

/**
 * Endpoint\Agendas tests
 *
 * @uses   \OpenAgenda\Endpoint\Agendas
 * @covers \OpenAgenda\Endpoint\Agendas
 */
class AgendasTest extends EndpointTestCase
{
    public static function datatestGetUrlSuccess(): array
    {
        return [
            [
                'get',
                [],
                [
                    'path' => '/v2/agendas',
                    'query' => [],
                ],
            ],
            [
                'get',
                [
                    'size' => 2,
                    'fields' => ['summary', 'schema'],
                    'search' => 'Agenda',
                    'official' => true,
                    'slug' => 'agenda',
                    'uid' => 12,
                    'network' => 34,
                    'sort' => 'createdAt.desc',
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

            // My agendas. _path is set by EndpointFactory::make()
            [
                'get',
                [
                    '_path' => '/agendas/mines',
                ],
                [
                    'path' => '/v2/me/agendas',
                    'query' => [],
                ],
            ],
        ];
    }

    /**
     * @dataProvider datatestGetUrlSuccess
     */
    public function testtestGetUrlSuccess($method, $params, $expected)
    {
        $endpoint = new Agendas($params);
        $url = $endpoint->getUrl($method);
        $this->assertEquals($expected['path'], parse_url($url, PHP_URL_PATH));
        parse_str((string)parse_url($url, PHP_URL_QUERY), $query);
        $this->assertEquals($expected['query'], $query);
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/agendas/agendas.json');
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/agendas?size=2',
            ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Agendas(['size' => 2]);

        $results = $endpoint->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(AgendaEntity::class, $results->first());
        $this->assertCount(2, $results);
    }

    public function testGetMines()
    {
        $payload = FileResource::instance($this)->getContent('Response/agendas/mines.json');
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/me/agendas?size=1',
            ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Agendas(['size' => 1, '_path' => '/agendas/mines']);

        $results = $endpoint->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(AgendaEntity::class, $results->first());
        $this->assertCount(1, $results);
    }
}

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

use OpenAgenda\Endpoint\Agenda;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;

/**
 * Endpoint\Agenda tests
 *
 * @uses   \OpenAgenda\Endpoint\Agenda
 * @covers \OpenAgenda\Endpoint\Agenda
 */
class AgendaTest extends EndpointTestCase
{
    public static function dataGetUriSuccess(): array
    {
        return [
            [
                'exists',
                ['uid' => 12345],
                [
                    'path' => '/v2/agendas/12345',
                    'query' => [],
                ],
            ],
            [
                'get',
                ['uid' => 12345],
                [
                    'path' => '/v2/agendas/12345',
                    'query' => [],
                ],
            ],
            [
                'get',
                [
                    'uid' => 12345,
                    'detailed' => true,
                ],
                [
                    'path' => '/v2/agendas/12345',
                    'query' => [
                        'detailed' => '1',
                    ],
                ],
            ],

        ];
    }

    /**
     * @dataProvider dataGetUriSuccess
     */
    public function testGetUriSuccess($method, $params, $expected)
    {
        $endpoint = new Agenda($params);
        $url = $endpoint->getUrl($method);
        $this->assertEquals($expected['path'], parse_url($url, PHP_URL_PATH));
        parse_str((string)parse_url($url, PHP_URL_QUERY), $query);
        $this->assertEquals($expected['query'], $query);
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/agendas/agenda.json');
        $this->mockRequest(false, 'get', [
                'https://api.openagenda.com/v2/agendas/12345',
                ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Agenda(['uid' => 12345]);

        $entity = $endpoint->get();

        $this->assertInstanceOf(\OpenAgenda\Entity\Agenda::class, $entity);
        $this->assertFalse($entity->isNew());
    }

    public function testExists()
    {
        $this->mockRequest(false, 'head', [
                'https://api.openagenda.com/v2/agendas/12345',
                ['headers' => ['key' => 'publicKey']],
        ], [200, '']);

        $endpoint = new Agenda(['uid' => 12345]);

        $this->assertTrue($endpoint->exists());
    }
}

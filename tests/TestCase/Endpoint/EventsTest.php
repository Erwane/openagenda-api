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
use OpenAgenda\Endpoint\Events;
use OpenAgenda\Entity\Event as EventEntity;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;

/**
 * Endpoint\Events tests
 *
 * @uses   \OpenAgenda\Endpoint\Events
 * @covers \OpenAgenda\Endpoint\Events
 */
class EventsTest extends EndpointTestCase
{
    public static function dataCheckGeo(): array
    {
        return [
            [[], true],
            [['northEast' => ['lat' => 48.9527, 'lng' => 2.4484], 'southWest' => ['lat' => 48.8560, 'lng' => 2.1801]], true],
            [['northEast' => ['lng' => 2.4484], 'southWest' => ['lat' => 48.8560, 'lng' => 2.1801]], false],
            [['northEast' => ['lat' => 48.9527], 'southWest' => ['lat' => 48.8560, 'lng' => 2.1801]], false],
            [['northEast' => ['lat' => 48.9527, 'lng' => 2.4484], 'southWest' => ['lng' => 2.1801]], false],
            [['northEast' => ['lat' => 48.9527, 'lng' => 2.4484], 'southWest' => ['lat' => 48.8560]], false],
        ];
    }

    /**
     * @dataProvider dataCheckGeo
     */
    public function testCheckGeo($context, $expected): void
    {
        $result = Events::checkGeo($context);
        $this->assertSame($expected, $result);
    }

    public static function dataGetUriSuccess(): array
    {
        return [
            [
                'get',
                ['agendaUid' => 123],
                [
                    'path' => '/v2/agendas/123/events',
                    'query' => [],
                ],
            ],
            [
                'get',
                [
                    'agendaUid' => 123,
                    'detailed' => 1,
                    'longDescriptionFormat' => 'markdown',
                    'size' => 2,
                    'includeLabels' => 1,
                    'includeFields' => ['uid', 'title'],
                    'monolingual' => 'fr',
                    'removed' => 1,
                    'city' => ['Paris'],
                    'department' => ['Hauts-de-Seine'],
                    'region' => 'Normandie',
                    'timings[gte]' => '2021-02-07T09:00:00',
                    'timings[lte]' => '2022-02-07T09:00:00',
                    'updatedAt[gte]' => '2023-02-07T09:00:00',
                    'updatedAt[lte]' => '2024-02-07T09:00:00',
                    'search' => 'Concert',
                    'uid' => [56158955, 55895615],
                    'slug' => 'festival-dete',
                    'featured' => 1,
                    'relative' => ['passed', 'upcoming'],
                    'state' => EventEntity::STATE_READY,
                    'keyword' => ['gratuit', 'exposition'],
                    'geo' => ['northEast' => ['lat' => 48.9527, 'lng' => 2.4484], 'southWest' => ['lat' => 48.8560, 'lng' => 2.1801]],
                    'locationUid' => [123, 456],
                    'accessibility' => [EventEntity::ACCESS_HI, EventEntity::ACCESS_PI],
                    'status' => [EventEntity::STATUS_SCHEDULED, EventEntity::STATUS_RESCHEDULED],
                ],
                [
                    'path' => '/v2/agendas/123/events',
                    'query' => [
                        'detailed' => '1',
                        'longDescriptionFormat' => 'markdown',
                        'size' => '2',
                        'includeLabels' => '1',
                        'includeFields' => ['uid', 'title'],
                        'monolingual' => 'fr',
                        'removed' => '1',
                        'city' => ['Paris'],
                        'department' => ['Hauts-de-Seine'],
                        'region' => 'Normandie',
                        'timings' => [
                            'gte' => '2021-02-07T09:00:00',
                            'lte' => '2022-02-07T09:00:00',
                        ],
                        'updatedAt' => [
                            'gte' => '2023-02-07T09:00:00',
                            'lte' => '2024-02-07T09:00:00',
                        ],
                        'search' => 'Concert',
                        'uid' => [56158955, 55895615],
                        'slug' => 'festival-dete',
                        'featured' => '1',
                        'relative' => ['passed', 'upcoming'],
                        'state' => '1',
                        'keyword' => ['gratuit', 'exposition'],
                        'geo' => ['northEast' => ['lat' => 48.9527, 'lng' => 2.4484], 'southWest' => ['lat' => 48.8560, 'lng' => 2.1801]],
                        'locationUid' => [123, 456],
                        'accessibility' => ['hi', 'pi'],
                        'status' => [1, 2],
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
        $endpoint = new Events($params);
        $url = $endpoint->getUrl($method);
        $this->assertEquals($expected['path'], parse_url($url, PHP_URL_PATH));
        parse_str((string)parse_url($url, PHP_URL_QUERY), $query);
        $this->assertEquals($expected['query'], $query);
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/events/events.json');
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/agendas/123/events?size=2',
            ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Events(['agendaUid' => 123, 'size' => 2]);

        $results = $endpoint->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(EventEntity::class, $results->first());
        $this->assertCount(1, $results);
    }
}

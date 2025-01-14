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
namespace OpenAgenda\Test\TestCase\Functional;

use GuzzleHttp\Psr7\Response;
use OpenAgenda\DateTime;
use OpenAgenda\Endpoint\Event as EventEndpoint;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Event;
use OpenAgenda\Test\OpenAgendaTestCase;
use OpenAgenda\Test\Utility\FileResource;

/**
 * @coversNothing
 */
class EventsFunctionalTest extends OpenAgendaTestCase
{
    /**
     * $events = $oa->events($params);
     */
    public function testSearch(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/events/events.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];

        $this->assertClientCall(
            $client,
            $this->once(),
            'get',
            $payload,
            [
                'path' => '/v2/agendas/123/events',
                'query' => [
                    'detailed' => '1',
                    'longDescriptionFormat' => 'HTMLWithEmbeds',
                    'size' => '2',
                    'includeLabels' => '1',
                    'includeFields' => ['uid', 'title', 'location.city'],
                    'monolingual' => 'fr',
                    'removed' => '0',
                    'city' => ['Lausanne', 'Genève'],
                    'department' => ['Hauts-de-Seine'],
                    'region' => 'Normandie',
                    'timings' => [
                        'gte' => '2023-06-01T00:00:00',
                        'lte' => '2023-06-30T23:59:59',
                    ],
                    'updatedAt' => [
                        'gte' => '2023-06-10T00:00:00',
                        'lte' => '2023-06-20T23:59:59',
                    ],
                    'search' => 'my event',
                    'uid' => ['56158955', '55895615'],
                    'slug' => 'festival-dete',
                    'featured' => '0',
                    'relative' => ['passed', 'upcoming', 'current'],
                    'state' => '2',
                    'keyword' => ['gratuit', 'exposition'],
                    'geo' => [
                        'northEast' => ['lat' => '48.9527', 'lng' => '2.4484'],
                        'southWest' => ['lat' => '48.856', 'lng' => '2.1801'],
                    ],
                    'locationUid' => ['123', '456'],
                    'accessibility' => ['hi', 'vi'],
                    'status' => ['5', '2'],
                    'sort' => 'timingsWithFeatured.asc',
                ],
            ]
        );

        $params = [
            'agendaUid' => 123,
            'detailed' => true,
            'longDescriptionFormat' => 'HTMLWithEmbeds',
            'size' => 2,
            'includeLabels' => true,
            'includeFields' => ['uid', 'title', 'location.city'],
            'monolingual' => 'fr',
            // todo test null, true false in Endpoint\Event
            'removed' => false,
            'city' => ['Lausanne', 'Genève'],
            'department' => ['Hauts-de-Seine'],
            'region' => 'Normandie',
            'timings[gte]' => DateTime::parse('2023-06-01'),
            'timings[lte]' => DateTime::parse('2023-06-30T23:59:59'),
            'updatedAt[gte]' => '2023-06-10',
            'updatedAt[lte]' => DateTime::parse('2023-06-20T23:59:59'),
            'search' => 'my event',
            'uid' => [56158955, 55895615],
            'slug' => 'festival-dete',
            'featured' => false,
            'relative' => ['passed', 'upcoming', 'current'],
            'state' => Event::STATE_PUBLISHED,
            'keyword' => ['gratuit', 'exposition'],
            'geo' => [
                'northEast' => ['lat' => 48.9527, 'lng' => 2.4484],
                'southWest' => ['lat' => 48.8560, 'lng' => 2.1801],
            ],
            'locationUid' => [123, 456],
            'accessibility' => [Event::ACCESS_HI, Event::ACCESS_VI],
            'status' => [Event::STATUS_FULL, Event::STATUS_RESCHEDULED],
            'sort' => 'timingsWithFeatured.asc',
        ];
        $events = $oa->events($params);
        $this->assertCount(1, $events);
    }

    /**
     * $events = $agenda->events($params);
     */
    public function testSearchFromAgenda(): void
    {
        [, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/events/events.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];

        $this->assertClientCall(
            $client,
            $this->once(),
            'get',
            $payload,
            [
                'path' => '/v2/agendas/123/events',
                'query' => [
                    'longDescriptionFormat' => 'HTMLWithEmbeds',
                ],
            ]
        );

        $params = [
            'longDescriptionFormat' => EventEndpoint::DESC_FORMAT_EMBEDS,
        ];

        $agenda = new Agenda(['uid' => 123]);
        $events = $agenda->events($params);
        $this->assertCount(1, $events);
    }

    /**
     * $exists = $oa->event($params)->exists();
     */
    public function testExists(): void
    {
        [$oa, $client] = $this->oa();

        $this->assertClientCall(
            $client,
            $this->once(),
            'head',
            200,
            ['path' => '/v2/agendas/123/events/456']
        );
        $params = ['agendaUid' => 123, 'uid' => 456];
        $exists = $oa->event($params)->exists();
        $this->assertTrue($exists);
    }

    /**
     * $exists = $agenda->event($params)->exists();
     */
    public function testExistsFromAgenda(): void
    {
        [, $client] = $this->oa();

        $this->assertClientCall(
            $client,
            $this->once(),
            'head',
            200,
            [
                'path' => '/v2/agendas/123/events/456',
            ]
        );
        $params = ['agendaUid' => 123, 'uid' => 456];

        $agenda = new Agenda(['uid' => 123]);
        $exists = $agenda->event($params)->exists();
        $this->assertTrue($exists);
    }

    /**
     * $event = $oa->event($params)->get();
     */
    public function testGet(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/events/event.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];

        $this->assertClientCall(
            $client,
            $this->once(),
            'get',
            $payload,
            [
                'path' => '/v2/agendas/123/events/456',
                'query' => [
                    'longDescriptionFormat' => 'HTMLWithEmbeds',
                ],
            ]
        );

        $params = [
            'agendaUid' => 123,
            'uid' => 456,
            'longDescriptionFormat' => 'HTMLWithEmbeds',
        ];

        $event = $oa->event($params)->get();
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * $event = $agenda->event($params)->get();
     */
    public function testGetFromAgenda(): void
    {
        [, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/events/event.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];

        $this->assertClientCall(
            $client,
            $this->once(),
            'get',
            $payload,
            ['path' => '/v2/agendas/123/events/456',]
        );
        $params = ['uid' => 456];

        $agenda = new Agenda(['uid' => 123]);
        $event = $agenda->event($params)->get();
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * $event = $oa->event($data)->create();
     */
    public function testCreate(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/events/event.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];

        $this->assertClientCall(
            $client,
            $this->once(),
            'post',
            $payload,
            ['path' => '/v2/agendas/123/events'],
            [
                'title' => ['fr' => 'Event title'],
                'description' => ['fr' => 'Event description'],
                'longDescription' => ['fr' => 'Long **html** description'],
                'conditions' => ['fr' => 'conditions FR', 'en' => 'conditions EN'],
                'keywords' => ['fr' => ['tag1', 'tag2']],
                'image' => null,
                'imageCredits' => null,
                'registration' => [
                    'https://formationcontinue.univ-rennes1.fr/cafeinfo',
                    '0203040506',
                ],
                'accessibility' => [
                    Event::ACCESS_HI => false,
                    Event::ACCESS_II => false,
                    Event::ACCESS_MI => false,
                    Event::ACCESS_PI => false,
                    Event::ACCESS_VI => true,
                ],
                'timings' => [
                    [
                        'begin' => '2023-06-30T20:30:00+01:00',
                        'end' => '2023-06-30T23:00:00+01:00',
                    ],
                ],
                'age' => ['min' => 7, 'max' => 150],
                'locationUid' => 789,
                'attendanceMode' => Event::ATTENDANCE_MIXED,
                'onlineAccessLink' => 'https://attendance-link.com',
                'status' => Event::STATUS_ONLINE,
                'state' => Event::STATE_PUBLISHED,
            ]
        );

        $data = [
            'agendaUid' => 123,
            'title' => 'Event title',
            'description' => 'Event description',
            'longDescription' => 'Long <b>html</b> description',
            'conditions' => ['fr' => 'conditions FR', 'en' => 'conditions EN'],
            'keywords' => ['tag1', 'tag2'],
            'image' => null,
            'imageCredits' => null,
            'registration' => [
                'https://formationcontinue.univ-rennes1.fr/cafeinfo',
                '0203040506',
            ],
            'accessibility' => Event::ACCESS_VI,
            'timings' => [
                [
                    'begin' => '2023-06-30T20:30:00+01:00',
                    'end' => '2023-06-30T23:00:00+01:00',
                ],
            ],
            'age' => [7, 150],
            'locationUid' => 789,
            'attendanceMode' => Event::ATTENDANCE_MIXED,
            'onlineAccessLink' => 'https://attendance-link.com',
            'status' => Event::STATUS_ONLINE,
            'state' => Event::STATE_PUBLISHED,
        ];

        $event = $oa->event($data)->create();
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * $event = $agenda->event($data)->create();
     */
    public function testCreateFromAgenda(): void
    {
        [, $client, $wrapper] = $this->oa();

        // Url image check
        $wrapper->expects($this->once())
            ->method('head')
            ->willReturn(new Response(200, ['content-type' => 'image/jpeg', 'content-length' => 848153]));

        $payload = json_decode(FileResource::instance($this)->getContent('Response/events/event.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'post',
            $payload,
            ['path' => '/v2/agendas/123/events'],
            [
                'locationUid' => 789,
                'title' => ['fr' => 'My event'],
                'description' => ['fr' => 'Event description'],
                'timings' => [
                    [
                        'begin' => '2023-06-30T20:30:00+01:00',
                        'end' => '2023-06-30T23:00:00+01:00',
                    ],
                ],
                'image' => ['url' => 'https://httpbin.org/image/jpeg'],
            ]
        );

        $agenda = new Agenda(['uid' => 123]);

        $data = [
            'agendaUid' => 123,
            'locationUid' => 789,
            'title' => 'My event',
            'description' => 'Event description',
            'timings' => [
                [
                    'begin' => '2023-06-30T20:30:00+01:00',
                    'end' => '2023-06-30T23:00:00+01:00',
                ],
            ],
            'image' => 'https://httpbin.org/image/jpeg',
        ];
        $event = $agenda->event($data)->create();
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * $event = $oa->event($data)->update();
     */
    public function testUpdate(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/events/event.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'patch',
            $payload,
            ['path' => '/v2/agendas/123/events/456'],
            ['state' => 2]
        );

        $data = ['uid' => 456, 'agendaUid' => 123, 'state' => Event::STATE_PUBLISHED];

        $event = $oa->event($data)->update();
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * Update event from entity
     */
    public function testUpdateFromEvent(): void
    {
        [, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/events/event.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'patch',
            $payload,
            ['path' => '/v2/agendas/123/events/456'],
            [
                'state' => 2,
            ]
        );

        $event = new Event(['uid' => 456, 'agendaUid' => 123, 'locationUid' => 789, 'state' => Event::STATE_READY], ['markClean' => true]);
        $event->setNew(false);
        $event->locationUid = 789;
        $event->state = Event::STATE_PUBLISHED;
        $event = $event->update();
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * $event = $oa->event($params)->delete();
     */
    public function testDelete(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/events/event.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'delete',
            $payload,
            ['path' => '/v2/agendas/123/events/456']
        );

        $event = $oa->event(['agendaUid' => 123, 'uid' => 456])->delete();
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * $event = $oa->event($params)
     * ->get()
     * ->delete();
     */
    public function testDeleteFromEvent(): void
    {
        [, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/events/event.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'delete',
            $payload,
            ['path' => '/v2/agendas/123/events/456']
        );

        $event = new Event(['uid' => 456, 'agendaUid' => 123]);
        $event = $event->delete();
        $this->assertInstanceOf(Event::class, $event);
    }
}

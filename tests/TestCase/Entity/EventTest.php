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
namespace OpenAgenda\Test\TestCase\Entity;

use Cake\Chronos\Chronos;
use GuzzleHttp\Psr7\Response;
use OpenAgenda\Endpoint\Agenda as AgendaEndpoint;
use OpenAgenda\Endpoint\Location as LocationEndpoint;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Event;
use OpenAgenda\Entity\Location;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\OpenAgendaTestCase;
use OpenAgenda\Test\Utility\FileResource;

/**
 * Entity\Event tests
 *
 * @uses   \OpenAgenda\Entity\Event
 * @covers \OpenAgenda\Entity\Event
 */
class EventTest extends OpenAgendaTestCase
{
    public function testAliasesIn()
    {
        $json = FileResource::instance($this)->getContent('Response/events/event.json');
        $payload = json_decode($json, true);
        $ent = new Event($payload['event']);
        $result = $ent->toArray();

        // Extract entity for tests
        /** @var \OpenAgenda\Entity\Agenda $agenda */
        $agenda = $result['agenda'];
        /** @var \OpenAgenda\Entity\Location $agenda */
        $location = $result['location'];
        unset($result['agenda'], $result['location']);

        $this->assertEquals([
            'id' => 123,
            'image' => null,
            'description' => 'My agenda',
            'official' => false,
            'title' => 'My agenda',
            'slug' => 'my-agenda',
            'url' => null,
        ], $agenda->toArray());

        $this->assertEquals([
            'id' => 456,
            'agenda_id' => 123,
            'name' => 'My location',
            'address' => '122 rue de charonne, 75011 Paris, France',
            'access' => [],
            'description' => [],
            'image' => null,
            'image_credits' => null,
            'slug' => 'my-location_2426083',
            'location_set_id' => null,
            'city' => 'Paris',
            'department' => 'Paris',
            'region' => 'Île-de-France',
            'postal_code' => '75011',
            'insee' => null,
            'country' => 'FR',
            'district' => 'Quartier Sainte-Marguerite',
            'latitude' => 48.854969,
            'longitude' => 2.386696,
            'created_at' => Chronos::parse('2025-01-06T18:09:50.000Z'),
            'updated_at' => Chronos::parse('2025-01-09T08:56:52.000Z'),
            // 'website' => null,
            'email' => null,
            'phone' => null,
            'links' => [],
            'timezone' => 'Europe/Paris',
            'ext_id' => null,
            'state' => true,
        ], $location->toArray());

        $this->assertEquals([
            'id' => 9906334,
            'slug' => 'testing-6090479',
            'state' => 0,
            'status' => 1,
            'featured' => false,
            'types' => [41],
            'image' => null,
            'image_credits' => null,
            'title' => ['en' => 'My event', 'fr' => 'Mon évènement'],
            'description' => ['en' => 'Short description', 'fr' => 'Description courte'],
            'long_description' => ['en' => 'Long description', 'fr' => 'Description longue'],
            'keywords' => ['en' => ['my', 'event'], 'fr' => ['mon', 'évènement']],
            'conditions' => ['en' => 'price en', 'fr' => 'price fr'],
            'age' => ['min' => 7, 'max' => 121],
            'registration' => [],
            'accessibility' => ['ii' => false, 'hi' => true, 'vi' => false, 'pi' => true, 'mi' => false,],
            'links' => [],
            'attendance_mode' => 1,
            'online_access_link' => null,
            'timings' => [
                [
                    'begin' => Chronos::parse('2025-01-06T11:00:00+01:00'),
                    'end' => Chronos::parse('2025-01-06T15:00:00+01:00'),
                ],
                [
                    'begin' => Chronos::parse('2025-01-06T15:00:00+01:00'),
                    'end' => Chronos::parse('2025-01-06T18:00:00+01:00'),
                ],
            ],
            'timezone' => 'Europe/Paris',
            'created_at' => Chronos::parse('2025-01-09T13:53:29.658Z'),
            'updated_at' => Chronos::parse('2025-01-09T13:53:30.000Z'),
        ], $result);
    }

    public function testAliasesOut()
    {
        $ent = new Event([
            'id' => 9906334,
            'slug' => 'testing-6090479',
            'state' => 0,
            'status' => 1,
            'featured' => false,
            'agenda' => new Agenda(['id' => 456]),
            'location' => new Location(['id' => 123, 'agenda_id' => 456]),
            'type' => [41],
            'image' => null,
            'image_credits' => null,
            'title' => ['en' => 'My event', 'fr' => 'Mon évènement'],
            'description' => ['en' => 'Short description', 'fr' => 'Description courte'],
            'long_description' => ['en' => 'Long description', 'fr' => 'Description longue'],
            'keywords' => ['en' => ['my', 'event'], 'fr' => ['mon', 'évènement']],
            'conditions' => ['en' => 'price en', 'fr' => 'price fr'],
            'age' => ['min' => 7, 'max' => 121],
            'registration' => [],
            'accessibility' => ['ii' => false, 'hi' => true, 'vi' => false, 'pi' => true, 'mi' => false],
            'links' => [],
            'attendance_mode' => 1,
            'online_access_link' => null,
            'timings' => [
                [
                    'begin' => Chronos::parse('2025-01-06T11:00:00+01:00'),
                    'end' => Chronos::parse('2025-01-06T15:00:00+01:00'),
                ],
                [
                    'begin' => Chronos::parse('2025-01-06T15:00:00+01:00'),
                    'end' => Chronos::parse('2025-01-06T18:00:00+01:00'),
                ],
            ],
            'timezone' => 'Europe/Paris',
            'created_at' => Chronos::parse('2024-12-27T15:41:32.000Z'),
            'updated_at' => Chronos::parse('2024-12-27T15:42:32.000Z'),
        ]);

        $this->assertSame([
            'slug' => 'testing-6090479',
            'state' => 0,
            'status' => 1,
            'featured' => 0,
            'type' => [41],
            'image' => null,
            'imageCredits' => null,
            'title' => ['en' => 'My event', 'fr' => 'Mon évènement'],
            'description' => ['en' => 'Short description', 'fr' => 'Description courte'],
            'longDescription' => ['en' => 'Long description', 'fr' => 'Description longue'],
            'keywords' => ['en' => ['my', 'event'], 'fr' => ['mon', 'évènement']],
            'conditions' => ['en' => 'price en', 'fr' => 'price fr'],
            'age' => ['min' => 7, 'max' => 121],
            'registration' => [],
            'accessibility' => ['ii' => false, 'hi' => true, 'vi' => false, 'pi' => true, 'mi' => false,],
            'links' => [],
            'attendanceMode' => 1,
            'onlineAccessLink' => null,
            'timings' => [
                [
                    'begin' => '2025-01-06T11:00:00+01:00',
                    'end' => '2025-01-06T15:00:00+01:00',
                ],
                [
                    'begin' => '2025-01-06T15:00:00+01:00',
                    'end' => '2025-01-06T18:00:00+01:00',
                ],
            ],
            'timezone' => 'Europe/Paris',
            'createdAt' => '2024-12-27T15:41:32',
            'updatedAt' => '2024-12-27T15:42:32',
            'locationUid' => 123,
        ], $ent->toOpenAgenda());
    }

    public static function dataSetTimings()
    {
        $end = Chronos::now();
        $begin = $end->subHours(2);

        return [
            [[], []],
            [
                [['begin' => $begin, 'end' => $end->toAtomString()]],
                [['begin' => $begin, 'end' => Chronos::parse($end->toAtomString())]],
            ],
        ];
    }

    /**
     * @covers       \OpenAgenda\Entity\Event::_setTimings
     * @dataProvider dataSetTimings
     */
    public function testSetTimings($input, $expected): void
    {
        $entity = new Event();
        $entity->timings = $input;

        $this->assertEquals($expected, $entity->timings);
    }

    public static function dataClientNotSet()
    {
        return [
            ['update'],
            ['delete'],
        ];
    }

    /**
     * @dataProvider dataClientNotSet
     */
    public function testClientNotSet($method): void
    {
        OpenAgenda::resetClient();
        $entity = new Event(['id' => 123]);
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('OpenAgenda object was not previously created or Client not set.');
        $entity->{$method}();
    }

    public function testUpdate()
    {
        $wrapper = $this->clientWrapper(['auth' => true]);

        $entity = new Event([
            'id' => 456,
            'agenda_id' => 123,
            'title' => 'My event',
            'description' => 'Event description',
            'state' => Event::STATE_PUBLISHED,
        ]);

        $payload = FileResource::instance($this)->getContent('Response/events/event.json');
        $wrapper->expects($this->once())
            ->method('patch')
            ->willReturn(new Response(200, [], $payload));

        $entity->state = Event::STATE_MODERATION;
        $new = $entity->update();

        $this->assertInstanceOf(Event::class, $new);
        $this->assertSame(Event::STATE_MODERATION, $new->state);
    }

    public function testDelete()
    {
        $wrapper = $this->clientWrapper(['auth' => true]);

        $entity = new Event([
            'id' => 456,
            'agenda_id' => 123,
        ]);

        $payload = FileResource::instance($this)->getContent('Response/events/event.json');
        $wrapper->expects($this->once())
            ->method('delete')
            ->willReturn(new Response(200, [], $payload));

        $new = $entity->delete();
        $this->assertInstanceOf(Event::class, $new);
    }

    public function testAgenda()
    {
        $entity = new Event([
            'id' => 456,
            'agenda_id' => 123,
        ]);

        $endpoint = $entity->agenda();

        $this->assertInstanceOf(AgendaEndpoint::class, $endpoint);
        $this->assertEquals([
            'head' => 'https://api.openagenda.com/v2/agendas/123',
            'get' => 'https://api.openagenda.com/v2/agendas/123',
            'post' => 'https://api.openagenda.com/v2/agendas/123',
            'patch' => 'https://api.openagenda.com/v2/agendas/123',
            'delete' => 'https://api.openagenda.com/v2/agendas/123',
            'params' => [
                '_path' => '/agenda',
                'id' => 123,
            ],
        ], $endpoint->toArray());
    }

    public function testLocation()
    {
        $entity = new Event(['id' => 123, 'agenda_id' => 789]);

        $endpoint = $entity->location([
            'id' => 456,
            'agenda_id' => 123,
            'name' => 'My location',
            'address' => 'Random address',
            'country' => 'FR',
        ]);

        $this->assertInstanceOf(LocationEndpoint::class, $endpoint);
        $this->assertEquals([
            'head' => 'https://api.openagenda.com/v2/agendas/789/locations/456',
            'get' => 'https://api.openagenda.com/v2/agendas/789/locations/456',
            'post' => 'https://api.openagenda.com/v2/agendas/789/locations/456',
            'patch' => 'https://api.openagenda.com/v2/agendas/789/locations/456',
            'delete' => 'https://api.openagenda.com/v2/agendas/789/locations/456',
            'params' => [
                '_path' => '/location',
                'id' => 456,
                'agenda_id' => 789,
                'name' => 'My location',
                'address' => 'Random address',
                'country' => 'FR',
            ],
        ], $endpoint->toArray());
    }
}

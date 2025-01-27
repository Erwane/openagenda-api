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

use GuzzleHttp\Psr7\Response;
use OpenAgenda\DateTime;
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
    public function testFromOpenAgenda()
    {
        $json = FileResource::instance($this)->getContent('Response/events/event.json');
        $payload = json_decode($json, true);
        $ent = new Event($payload['event']);
        $result = $ent->toArray();

        // Extract entity for tests
        /** @var \OpenAgenda\Entity\Agenda $agenda */
        $agenda = $result['originAgenda'];
        /** @var \OpenAgenda\Entity\Location $agenda */
        $location = $result['location'];
        unset($result['originAgenda'], $result['location']);

        $this->assertSame([
            'uid' => 123,
            'title' => 'My agenda',
            'slug' => 'my-agenda',
            'description' => 'My agenda',
            'url' => null,
            'image' => null,
            'official' => false,
            'private' => null,
            'indexed' => null,
            'networkUid' => null,
            'locationSetUid' => null,
            'createdAt' => null,
            'updatedAt' => null,
        ], $agenda);

        [$created, $updated] = [$location['createdAt'], $location['updatedAt']];
        unset($location['createdAt'], $location['updatedAt']);

        $this->assertSame([
            'uid' => 456,
            'agendaUid' => 123,
            'name' => 'My location',
            'address' => '122 rue de Charonne, 75011 Paris, France',
            'access' => [],
            'description' => [],
            'image' => null,
            'imageCredits' => null,
            'slug' => 'my-location_2426083',
            'locationSetUid' => null,
            'city' => 'Paris',
            'department' => 'Paris',
            'region' => 'Île-de-France',
            'postalCode' => '75011',
            'insee' => null,
            'countryCode' => 'FR',
            'district' => 'Quartier Sainte-Marguerite',
            'latitude' => 48.854969,
            'longitude' => 2.386696,
            'website' => null,
            'email' => null,
            'phone' => null,
            'links' => [],
            'timezone' => 'Europe/Paris',
            'extId' => null,
            'state' => true,
        ], $location);
        $this->assertEquals([$created, $updated], [
            DateTime::parse('2025-01-06T18:09:50.000Z'),
            DateTime::parse('2025-01-09T08:56:52.000Z'),
        ]);

        [$created, $updated, $timings] = [$result['createdAt'], $result['updatedAt'], $result['timings']];
        unset($result['createdAt'], $result['updatedAt'], $result['timings']);

        $this->assertSame([
            'uid' => 9906334,
            'agendaUid' => 123,
            'locationUid' => 456,
            'slug' => 'testing-6090479',
            'title' => ['en' => 'My event', 'fr' => 'Mon évènement'],
            'description' => ['en' => 'Short description', 'fr' => 'Description courte'],
            'longDescription' => ['en' => 'Long description', 'fr' => 'Description longue'],
            'conditions' => ['en' => 'price en', 'fr' => 'price fr'],
            'keywords' => ['en' => ['my', 'event'], 'fr' => ['mon', 'évènement']],
            'image' => null,
            'imageCredits' => null,
            'registration' => [],
            'accessibility' => [
                'hi' => true,
                'ii' => false,
                'mi' => false,
                'pi' => true,
                'vi' => false,
            ],
            'type' => [41],
            'age' => ['min' => 7, 'max' => 121],
            'attendanceMode' => 1,
            'onlineAccessLink' => null,
            'links' => [],
            'timezone' => 'Europe/Paris',
            'status' => 1,
            'state' => 0,
            'featured' => false,
        ], $result);
        $this->assertEquals([$created, $updated, $timings], [
            DateTime::parse('2025-01-09T13:53:29.658Z'),
            DateTime::parse('2025-01-09T13:53:30.000Z'),
            [
                [
                    'begin' => DateTime::parse('2025-01-06T11:00:00+01:00'),
                    'end' => DateTime::parse('2025-01-06T15:00:00+01:00'),
                ],
                [
                    'begin' => DateTime::parse('2025-01-06T15:00:00+01:00'),
                    'end' => DateTime::parse('2025-01-06T18:00:00+01:00'),
                ],
            ],
        ]);
    }

    public function testToOpenAgenda()
    {
        $ent = new Event([
            'uid' => 9906334,
            'slug' => 'testing-6090479',
            'state' => 0,
            'status' => 1,
            'featured' => false,
            'agenda' => new Agenda(['uid' => 456]),
            'location' => new Location(['uid' => 123, 'agendaUid' => 456]),
            'type' => [41],
            'image' => 'https://httpbin.org/image/jpeg',
            'imageCredits' => 'Image credits',
            'title' => ['en' => 'My event', 'fr' => 'Mon évènement'],
            'description' => ['en' => 'Short description', 'fr' => 'Description courte'],
            'longDescription' => ['en' => 'Long description', 'fr' => 'Description longue'],
            'keywords' => ['en' => ['my', 'event'], 'fr' => ['mon', 'évènement']],
            'conditions' => ['en' => 'price en', 'fr' => 'price fr'],
            'age' => ['min' => 7, 'max' => 121],
            'registration' => [],
            'accessibility' => ['ii' => false, 'hi' => true, 'vi' => false, 'pi' => true, 'mi' => false],
            'links' => [],
            'attendanceMode' => 1,
            'onlineAccessLink' => null,
            'timings' => [
                [
                    'begin' => DateTime::parse('2025-01-06T11:00:00+01:00'),
                    'end' => DateTime::parse('2025-01-06T15:00:00+01:00'),
                ],
                [
                    'begin' => DateTime::parse('2025-01-06T15:00:00+01:00'),
                    'end' => DateTime::parse('2025-01-06T18:00:00+01:00'),
                ],
            ],
            'timezone' => 'Europe/Paris',
            'createdAt' => DateTime::parse('2024-12-27T15:41:32.000Z'),
            'updatedAt' => DateTime::parse('2024-12-27T15:42:32.000Z'),
        ]);

        $this->assertSame([
            'slug' => 'testing-6090479',
            'state' => 0,
            'status' => 1,
            'featured' => 0,
            'type' => [41],
            'image' => ['url' => 'https://httpbin.org/image/jpeg'],
            'imageCredits' => 'Image credits',
            'title' => ['en' => 'My event', 'fr' => 'Mon évènement'],
            'description' => ['en' => 'Short description', 'fr' => 'Description courte'],
            'longDescription' => ['en' => 'Long description', 'fr' => 'Description longue'],
            'keywords' => ['en' => ['my', 'event'], 'fr' => ['mon', 'évènement']],
            'conditions' => ['en' => 'price en', 'fr' => 'price fr'],
            'age' => ['min' => 7, 'max' => 121],
            'registration' => [],
            'accessibility' => [
                'hi' => true,
                'ii' => false,
                'mi' => false,
                'pi' => true,
                'vi' => false,
            ],
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

    public function testToOpenAgendaImageResource(): void
    {
        $resource = fopen(TESTS . 'resources/wendywei-1537637.jpg', 'r');
        $ent = new Event(['image' => $resource]);
        $this->assertSame(['image' => $resource], $ent->toOpenAgenda());
    }

    public function testToOpenAgendaImagePath(): void
    {
        $ent = new Event(['image' => TESTS . 'resources/wendywei-1537637.jpg']);
        $this->assertIsResource($ent->toOpenAgenda()['image']);
    }

    public static function dataSetTimings()
    {
        $begin = DateTime::parse(NOW);
        /** @var \OpenAgenda\DateTime $end */
        $end = $begin->modify('2 hour');

        return [
            [[], []],
            [
                [['begin' => $begin, 'end' => $end->toAtomString()]],
                [
                    [
                        'begin' => $begin,
                        'end' => $end,
                    ],
                ],
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

    public static function dataSetAge(): array
    {
        return [
            [[], ['min' => null, 'max' => null]],
            [[7, 50], ['min' => 7, 'max' => 50]],
            [['min' => 7, 'max' => 50], ['min' => 7, 'max' => 50]],
        ];
    }

    /**
     * @covers       \OpenAgenda\Entity\Event::_setAge
     * @dataProvider dataSetAge
     */
    public function testSetAge($input, $expected): void
    {
        $entity = new Event();
        $entity->age = $input;

        $this->assertEquals($expected, $entity->age);
    }

    public static function dataAccessibility(): array
    {
        return [
            [
                [],
                [
                    Event::ACCESS_HI => false,
                    Event::ACCESS_II => false,
                    Event::ACCESS_MI => false,
                    Event::ACCESS_PI => false,
                    Event::ACCESS_VI => false,
                ],
            ],
            [
                Event::ACCESS_HI,
                [
                    Event::ACCESS_HI => true,
                    Event::ACCESS_II => false,
                    Event::ACCESS_MI => false,
                    Event::ACCESS_PI => false,
                    Event::ACCESS_VI => false,
                ],
            ],
            [
                [
                    Event::ACCESS_MI,
                    Event::ACCESS_VI,
                ],
                [
                    Event::ACCESS_HI => false,
                    Event::ACCESS_II => false,
                    Event::ACCESS_MI => true,
                    Event::ACCESS_PI => false,
                    Event::ACCESS_VI => true,
                ],
            ],
            [
                [
                    Event::ACCESS_II => true,
                    Event::ACCESS_PI => true,
                ],
                [
                    Event::ACCESS_HI => false,
                    Event::ACCESS_II => true,
                    Event::ACCESS_MI => false,
                    Event::ACCESS_PI => true,
                    Event::ACCESS_VI => false,
                ],
            ],
        ];
    }

    /**
     * @covers       \OpenAgenda\Entity\Event::_setAccessibility
     * @dataProvider dataAccessibility
     */
    public function testAccessibility($input, $expected): void
    {
        $entity = new Event();
        $entity->accessibility = $input;

        $this->assertEquals($expected, $entity->accessibility);
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
        $entity = new Event(['uid' => 123]);
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('OpenAgenda object was not previously created or Client not set.');
        $entity->{$method}();
    }

    public function testUpdate()
    {
        $wrapper = $this->clientWrapper(['auth' => true]);

        $entity = new Event([
            'uid' => 456,
            'agendaUid' => 123,
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
            'uid' => 456,
            'agendaUid' => 123,
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
            'uid' => 456,
            'agendaUid' => 123,
        ]);

        $endpoint = $entity->agenda();

        $this->assertInstanceOf(AgendaEndpoint::class, $endpoint);
        $this->assertSame([
            'exists' => 'https://api.openagenda.com/v2/agendas/123',
            'get' => 'https://api.openagenda.com/v2/agendas/123',
            'create' => 'https://api.openagenda.com/v2/agendas/123',
            'update' => 'https://api.openagenda.com/v2/agendas/123',
            'delete' => 'https://api.openagenda.com/v2/agendas/123',
            'params' => [
                'uid' => 123,
                '_path' => '/agenda',
            ],
        ], $endpoint->toArray());
    }

    public function testLocation()
    {
        $entity = new Event(['uid' => 123, 'agendaUid' => 789]);

        $endpoint = $entity->location([
            'uid' => 456,
            'agendaUid' => 123,
            'name' => 'My location',
            'address' => 'Random address',
            'countryCode' => 'FR',
        ]);

        $this->assertInstanceOf(LocationEndpoint::class, $endpoint);
        $this->assertSame([
            'exists' => 'https://api.openagenda.com/v2/agendas/789/locations/456',
            'get' => 'https://api.openagenda.com/v2/agendas/789/locations/456',
            'create' => 'https://api.openagenda.com/v2/agendas/789/locations',
            'update' => 'https://api.openagenda.com/v2/agendas/789/locations/456',
            'delete' => 'https://api.openagenda.com/v2/agendas/789/locations/456',
            'params' => [
                'uid' => 456,
                'agendaUid' => 789,
                'name' => 'My location',
                'address' => 'Random address',
                'countryCode' => 'FR',
                '_path' => '/location',
            ],
        ], $endpoint->toArray());
    }

    /** @covers \OpenAgenda\Entity\Event::_setTitle */
    public function testSetTitle(): void
    {
        $entity = new Event(['title' => 'My event']);
        $this->assertEquals(['fr' => 'My event'], $entity->title);
    }

    /** @covers \OpenAgenda\Entity\Event::_setTitle */
    public function testSetTitleTruncate(): void
    {
        $string = str_pad('start_', 145, '-');
        $entity = new Event(['title' => $string]);
        $this->assertEquals(140, strlen($entity->title['fr']));
        $this->assertStringEndsWith('-- ...', $entity->title['fr']);
    }

    /** @covers \OpenAgenda\Entity\Event::_setDescription */
    public function testSetDescription(): void
    {
        $entity = new Event([
            'description' => <<<HTML
<span>This</span> description
<p>should be on <a href="not this">one</a></p>
<ul>
<li>line </li>
<li>and clean.</li>
</ul>
HTML
            ,
        ]);
        $this->assertEquals(['fr' => 'This description should be on one line and clean.'], $entity->description);
    }

    /** @covers \OpenAgenda\Entity\Event::_setDescription */
    public function testSetDescriptionTruncate(): void
    {
        $string = str_pad('start_', 201, '-');
        $entity = new Event(['description' => $string]);
        $this->assertEquals(200, strlen($entity->description['fr']));
        $this->assertStringEndsWith('-- ...', $entity->description['fr']);
    }

    /** @covers \OpenAgenda\Entity\Event::_setLongDescription */
    public function testSetLongDescriptionTruncate(): void
    {
        $string = str_pad('start_', 10009, '-');
        $entity = new Event(['longDescription' => $string]);
        $this->assertEquals(10000, strlen($entity->longDescription['fr']));
        $this->assertStringEndsWith('-- ...', $entity->longDescription['fr']);
    }

    /** @covers \OpenAgenda\Entity\Event::_setConditions */
    public function testSetConditionsLangAndTruncate(): void
    {
        $string = str_pad('start_', 260, '-');
        $entity = new Event(['conditions' => $string]);
        $this->assertEquals(255, strlen($entity->conditions['fr']));
        $this->assertStringEndsWith('-- ...', $entity->conditions['fr']);
    }

    public static function dataSetKeywords()
    {
        return [
            [
                'tag1',
                ['fr' => ['tag1']],
            ],
            [
                ['tag1', ' <span>tag 2</span> '],
                ['fr' => ['tag1', 'tag 2']],
            ],
            [
                ['fr' => ['tag1', 'tag2'], 'en' => ['tag3', 'tag4']],
                ['fr' => ['tag1', 'tag2'], 'en' => ['tag3', 'tag4']],
            ],
        ];
    }

    /**
     * @dataProvider dataSetKeywords
     * @covers       \OpenAgenda\Entity\Event::_setKeywords
     */
    public function testSetKeywords($keywords, $expected): void
    {
        $entity = new Event(['keywords' => $keywords]);
        $this->assertSame($expected, $entity->keywords);
    }

    public static function dataGetAgendaUid(): array
    {
        return [
            [[], null],
            [['agendaUid' => '1'], 1],
            [['agenda' => new Agenda(['uid' => '1'])], 1],
            [['originAgenda' => new Agenda(['uid' => '1'])], 1],
        ];
    }

    /**
     * @dataProvider dataGetAgendaUid
     * @covers       \OpenAgenda\Entity\Event::_getAgenda
     * @covers       \OpenAgenda\Entity\Event::_getAgendaUid
     */
    public function testGetAgendaUid($data, $expected): void
    {
        $ent = new Event($data);
        $this->assertSame($expected, $ent->agendaUid);
    }

    public static function dataGetLocationUid(): array
    {
        return [
            [[], null],
            [['locationUid' => 1], 1],
            [['location' => new Location(['uid' => 1])], 1],
        ];
    }

    /**
     * @dataProvider dataGetLocationUid
     * @covers       \OpenAgenda\Entity\Event::_getLocation
     * @covers       \OpenAgenda\Entity\Event::_getLocationUid
     */
    public function testGetLocationUid($data, $expected): void
    {
        $ent = new Event($data);
        $this->assertSame($expected, $ent->locationUid);
    }
}

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

use Cake\Chronos\Chronos;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Location;
use OpenAgenda\Test\OpenAgendaTestCase;
use OpenAgenda\Test\Utility\FileResource;

/**
 * @coversNothing
 */
class LocationsFunctionalTest extends OpenAgendaTestCase
{
    /**
     * $locations = $oa->locations(['agendaUid' => 123, 'name' => 'My Location']);
     */
    public function testSearch()
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/locations/locations.json'), true);
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
                'path' => '/v2/agendas/123/locations',
                'query' => [
                    'size' => '2',
                    'detailed' => '1',
                    'search' => 'my location',
                    'state' => '1',
                    'createdAt' => [
                        'gte' => '2023-06-01T00:00:00',
                        'lte' => '2023-06-30T23:59:59',
                    ],
                    'updatedAt' => [
                        'gte' => '2023-06-10T00:00:00',
                        'lte' => '2023-06-20T23:59:59',
                    ],
                    'order' => 'name.desc',
                ],
            ]
        );

        $locations = $oa->locations([
            'agendaUid' => 123,
            'size' => 2,
            'detailed' => true,
            'search' => 'my location',
            'state' => true,
            'createdAt[gte]' => Chronos::parse('2023-06-01'),
            'createdAt[lte]' => Chronos::parse('2023-06-30T23:59:59'),
            'updatedAt[gte]' => '2023-06-10',
            'updatedAt[lte]' => Chronos::parse('2023-06-20T23:59:59'),
            'order' => 'name.desc',
        ]);
        $this->assertCount(1, $locations);
    }

    /**
     * Test search from Agenda
     * $locations = $agenda->locations(['name' => 'My Location']);
     */
    public function testSearchFromAgenda(): void
    {
        [, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/locations/locations.json'), true);
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
                'path' => '/v2/agendas/123/locations',
                'query' => [
                    'size' => '2',
                    'detailed' => '1',
                    'search' => 'my location',
                    'state' => '1',
                    'createdAt' => [
                        'gte' => '2023-06-01T00:00:00',
                        'lte' => '2023-06-30T23:59:59',
                    ],
                    'updatedAt' => [
                        'gte' => '2023-06-10T00:00:00',
                        'lte' => '2023-06-20T23:59:59',
                    ],
                    'order' => 'name.desc',
                ],
            ]
        );

        $agenda = new Agenda(['uid' => 123]);

        $locations = $agenda->locations([
            'size' => 2,
            'detailed' => true,
            'search' => 'my location',
            'state' => true,
            'createdAt[gte]' => Chronos::parse('2023-06-01'),
            'createdAt[lte]' => Chronos::parse('2023-06-30T23:59:59'),
            'updatedAt[gte]' => '2023-06-10',
            'updatedAt[lte]' => Chronos::parse('2023-06-20T23:59:59'),
            'order' => 'name.desc',
        ]);
        $this->assertCount(1, $locations);
    }

    /**
     * Test location exists
     * $exists = $oa->location(['uid' => 456, 'agendaUid' => 123])->exists();
     */
    public function testExists(): void
    {
        [$oa, $client] = $this->oa();

        $this->assertClientCall(
            $client,
            $this->once(),
            'head',
            200,
            ['path' => '/v2/agendas/123/locations/456']
        );
        $exists = $oa->location(['uid' => 456, 'agendaUid' => 123])->exists();
        $this->assertTrue($exists);
    }

    /**
     * Test location exists
     * $exists = $oa->location(['extId' => 456, 'agendaUid' => 123])->exists();
     */
    public function testExistsExtId(): void
    {
        [$oa, $client] = $this->oa();
        $this->assertClientCall(
            $client,
            $this->once(),
            'head',
            200,
            ['path' => '/v2/agendas/123/locations/ext/my-id']
        );
        $exists = $oa->location(['extId' => 'my-id', 'agendaUid' => 123])->exists();
        $this->assertTrue($exists);
    }

    /**
     * Test location exists from Agenda
     * $exists = $agenda->location(['uid' => 456])->exists();
     */
    public function testExistsFromAgenda(): void
    {
        [, $client] = $this->oa();
        $this->assertClientCall(
            $client,
            $this->once(),
            'head',
            200,
            ['path' => '/v2/agendas/123/locations/456']
        );

        $agenda = new Agenda(['uid' => 123]);
        $exists = $agenda->location(['uid' => 456])->exists();
        $this->assertTrue($exists);
    }

    /**
     * Test getting one location from id.
     * $location = $oa->location(['uid' => 456, 'agendaUid' => 123])->get();
     */
    public function testGet(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/locations/location.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'get',
            $payload,
            ['path' => '/v2/agendas/123/locations/456']
        );
        $location = $oa->location(['uid' => 456, 'agendaUid' => 123])->get();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test getting one location
     */
    public function testGetFromAgenda(): void
    {
        [, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/locations/location.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'get',
            $payload,
            ['path' => '/v2/agendas/123/locations/ext/my-location-id']
        );

        $agenda = new Agenda(['uid' => 123]);

        $location = $agenda->location(['extId' => 'my-location-id'])->get();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test create location
     * $location = $oa->location($data)->create();
     */
    public function testCreate(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/locations/location.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $imagePath = FileResource::instance($this)->getPath('wendywei-1537637.jpg');

        $this->assertClientCall(
            $client,
            $this->once(),
            'post',
            $payload,
            ['path' => '/v2/agendas/123/locations'],
            [
                'extId' => 'my-id',
                'name' => 'My location',
                'address' => '122 rue de Charonne, 75011 Paris, France',
                'countryCode' => 'FR',
                'state' => 1,
                'description' => ['fr' => 'Location description'],
                'access' => ['fr' => 'Location access'],
                'website' => 'https://example.com',
                'email' => 'email@example.com',
                'phone' => '+33123456789',
                'links' => ['https://www.louvre.fr', 'https://www.facebook.com/museedulouvre'],
                'image' => $imagePath,
                'imageCredits' => 'Image credits',
                'region' => 'Normandie',
                'department' => 'Oise',
                'district' => '11 ème',
                'city' => 'Paris',
                'postalCode' => '75011',
                'insee' => '75011',
                'latitude' => '1.2345',
                'longitude' => '6.7890',
                'timezone' => 'Europe/Paris',
            ]
        );

        $data = [
            'agendaUid' => 123,
            'extId' => 'my-id',
            'name' => 'My location',
            'address' => '122 rue de Charonne, 75011 Paris, France',
            'countryCode' => 'fr',
            'state' => true,
            'description' => 'Location description',
            'access' => 'Location access',
            'website' => 'https://example.com',
            'email' => 'email@example.com',
            'phone' => '0123456789',
            'links' => ['https://www.louvre.fr', 'https://www.facebook.com/museedulouvre'],
            'image' => $imagePath,
            'imageCredits' => 'Image credits',
            'region' => 'Normandie',
            'department' => 'Oise',
            'district' => '11 ème',
            'city' => 'Paris',
            'postalCode' => '75011',
            'insee' => '75011',
            'latitude' => 1.2345,
            'longitude' => 6.7890,
            'timezone' => 'Europe/Paris',
        ];

        $location = $oa->location($data)->create();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test create location from Agenda
     * $location = $agenda->location($data)->create();
     */
    public function testCreateFromAgenda(): void
    {
        [, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/locations/location.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'post',
            $payload,
            ['path' => '/v2/agendas/123/locations'],
            [
                'name' => 'My location',
                'address' => '122 rue de Charonne, 75011 Paris, France',
                'countryCode' => 'FR',
            ]
        );

        $agenda = new Agenda(['uid' => 123]);

        $data = [
            'agendaUid' => 123,
            'name' => 'My location',
            'address' => '122 rue de Charonne, 75011 Paris, France',
            'countryCode' => 'fr',
        ];
        $location = $agenda->location($data)->create();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test update location from oa
     * $location = $oa->location(['agendaUid' => 123, 'uid' => 456, 'state' => true])->update();
     */
    public function testUpdate(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/locations/location.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'patch',
            $payload,
            ['path' => '/v2/agendas/123/locations/456'],
            [
                'state' => 1,
            ]
        );

        $data = ['uid' => 456, 'agendaUid' => 123, 'state' => true];

        $location = $oa->location($data)->update();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test update location from Location
     */
    public function testUpdateFromLocation(): void
    {
        [, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/locations/location.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'patch',
            $payload,
            ['path' => '/v2/agendas/123/locations/456'],
            [
                'state' => 1,
            ]
        );

        $location = new Location(['uid' => 456, 'agendaUid' => 123, 'extId' => 'my-id', 'state' => 0], ['markClean' => true]);
        $location->setNew(false);
        $location->extId = 'my-id';
        $location->state = true;
        $location = $location->update();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test delete location from oa
     */
    public function testDelete(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/locations/location.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'delete',
            $payload,
            ['path' => '/v2/agendas/123/locations/456']
        );

        $location = $oa->location(['agendaUid' => 123, 'uid' => 456])->delete();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test delete location from Location
     */
    public function testDeleteFromLocation(): void
    {
        [, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/locations/location.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall(
            $client,
            $this->once(),
            'delete',
            $payload,
            ['path' => '/v2/agendas/123/locations/456']
        );

        $location = new Location(['uid' => 456, 'agendaUid' => 123]);
        $location = $location->delete();
        $this->assertInstanceOf(Location::class, $location);
    }
}

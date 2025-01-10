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
use OpenAgenda\Entity\Location;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\OpenAgendaTestCase;
use OpenAgenda\Test\Utility\FileResource;

/**
 * Entity\Location tests
 *
 * @uses   \OpenAgenda\Entity\Location
 * @covers \OpenAgenda\Entity\Location
 */
class LocationTest extends OpenAgendaTestCase
{
    public function testAliasesIn()
    {
        $json = FileResource::instance($this)->getContent('Response/locations/location.json');
        $payload = json_decode($json, true);
        $ent = new Location($payload['location']);
        $result = $ent->toArray();
        $this->assertEquals([
            'uid' => 35867424,
            'name' => 'Centres sociaux de Wattrelos 59150',
            'address' => '4 rue Edouard Herriot 59150 Wattrelos',
            'access' => [],
            'description' => [],
            // 'image' => null,
            'imageCredits' => null,
            'slug' => 'centres-sociaux-de-wattrelos-59150_6977111',
            'city' => 'Wattrelos',
            'department' => 'Nord',
            'region' => 'Hauts-de-France',
            'postalCode' => '59150',
            'insee' => '59650',
            'countryCode' => 'FR',
            'district' => null,
            'latitude' => 50.70428,
            'longitude' => 3.235638,
            'createdAt' => Chronos::parse('2024-12-27T15:41:32.000Z'),
            'updatedAt' => Chronos::parse('2024-12-27T15:42:32.000Z'),
            // 'website' => null,
            'email' => null,
            'phone' => null,
            'links' => [],
            'timezone' => 'Europe/Paris',
            'extId' => null,
            'state' => false,
        ], $result);
    }

    public function testAliasesOut()
    {
        $ent = new Location([
            'uid' => 35867424,
            'name' => 'Centres sociaux de Wattrelos 59150',
            'address' => '4 rue Edouard Herriot 59150 Wattrelos',
            'access' => [],
            'description' => [],
            // 'image' => null,
            'imageCredits' => null,
            'slug' => 'centres-sociaux-de-wattrelos-59150_6977111',
            'locationSetId' => null,
            'city' => 'Wattrelos',
            'department' => 'Nord',
            'region' => 'Hauts-de-France',
            'postalCode' => '59150',
            'insee' => '59650',
            'countryCode' => 'FR',
            'district' => null,
            'latitude' => 50.70428,
            'longitude' => 3.235638,
            'createdAt' => Chronos::parse('2024-12-27T15:41:32.000Z'),
            'updatedAt' => Chronos::parse('2024-12-27T15:42:32.000Z'),
            // 'website' => null,
            'email' => null,
            'phone' => null,
            'links' => [],
            'timezone' => 'Europe/Paris',
            'extId' => null,
            'state' => true,
        ]);

        $this->assertSame([
            'name' => 'Centres sociaux de Wattrelos 59150',
            'address' => '4 rue Edouard Herriot 59150 Wattrelos',
            'access' => [],
            'description' => [],
            'imageCredits' => null,
            'slug' => 'centres-sociaux-de-wattrelos-59150_6977111',
            'city' => 'Wattrelos',
            'department' => 'Nord',
            'region' => 'Hauts-de-France',
            'postalCode' => '59150',
            'insee' => '59650',
            'countryCode' => 'FR',
            'district' => null,
            'latitude' => 50.70428,
            'longitude' => 3.235638,
            'createdAt' => '2024-12-27T15:41:32',
            'updatedAt' => '2024-12-27T15:42:32',
            'email' => null,
            'phone' => null,
            'links' => [],
            'timezone' => 'Europe/Paris',
            'extId' => null,
            'state' => 1,
        ], $ent->toOpenAgenda());
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
        $entity = new Location(['uid' => 123]);
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('OpenAgenda object was not previously created or Client not set.');
        $entity->{$method}();
    }

    public function testUpdate()
    {
        $wrapper = $this->clientWrapper(['auth' => true]);

        $entity = new Location([
            'uid' => 456,
            'agendaUid' => 123,
            'name' => 'My location',
            'address' => 'Random address',
            'countryCode' => 'FR',
            'state' => true,
        ]);

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('patch')
            ->willReturn(new Response(200, [], $payload));

        $entity->state = false;
        $new = $entity->update();

        $this->assertInstanceOf(Location::class, $new);
        $this->assertFalse($new->state);
    }

    public function testDelete()
    {
        $wrapper = $this->clientWrapper(['auth' => true]);

        $entity = new Location([
            'uid' => 456,
            'agendaUid' => 123,
        ]);

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('delete')
            ->willReturn(new Response(200, [], $payload));

        $new = $entity->delete();
        $this->assertInstanceOf(Location::class, $new);
    }

    public function testAgenda()
    {
        $entity = new Location([
            'uid' => 456,
            'agendaUid' => 123,
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
                'uid' => 123,
            ],
        ], $endpoint->toArray());
    }
}

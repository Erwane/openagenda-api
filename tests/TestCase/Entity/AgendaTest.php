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
use OpenAgenda\Endpoint\Location as LocationEndpoint;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\OpenAgendaTestCase;
use OpenAgenda\Test\Utility\FileResource;
use Ramsey\Collection\Collection;

/**
 * @uses \OpenAgenda\Entity\Agenda
 * @covers \OpenAgenda\Entity\Agenda
 */
class AgendaTest extends OpenAgendaTestCase
{
    public function testAliasesIn()
    {
        $json = FileResource::instance($this)->getContent('Response/agendas/agenda.json');
        $payload = json_decode($json, true);
        $ent = new Agenda($payload);
        $result = $ent->toArray();
        $this->assertEquals([
            'uid' => 41648,
            'title' => 'La Semaine Nationale de la Petite Enfance',
            'description' => "Du 15 au 24 mars 2025, tous ensemble pour l'éveil du tout-petit ! \nUne semaine d'ateliers & activités à travers toute la France, pour réunir le trio parent-enfant-professionnel.",
            'slug' => 'semainepetiteenfance',
            'url' => 'https://www.semainepetiteenfance.fr',
            'image' => 'https://cdn.openagenda.com/main/agenda41648.jpg?__ts=1664443597781',
            'official' => true,
            'private' => false,
            'indexed' => true,
            'networkUid' => null,
            'locationSetUid' => null,
            'createdAt' => Chronos::parse('2016-07-27T12:24:08.000Z'),
            'updatedAt' => Chronos::parse('2025-01-04T10:31:53.000Z'),
        ], $result);
    }

    public function testAliasesOut()
    {
        $ent = new Agenda([
            'uid' => 41648,
            'title' => 'La Semaine Nationale de la Petite Enfance',
            'description' => "Du 15 au 24 mars 2025, tous ensemble pour l'éveil du tout-petit ! \nUne semaine d'ateliers & activités à travers toute la France, pour réunir le trio parent-enfant-professionnel.",
            'slug' => 'semainepetiteenfance',
            'url' => 'https://www.semainepetiteenfance.fr',
            'image' => 'https://cdn.openagenda.com/main/agenda41648.jpg?__ts=1664443597781',
            'official' => true,
            'private' => false,
            'indexed' => true,
            'networkUid' => null,
            'locationSetUid' => null,
            'createdAt' => Chronos::parse('2016-07-27T12:24:08.000Z'),
            'updatedAt' => Chronos::parse('2025-01-04T10:31:53.000Z'),
        ]);

        $this->assertSame([
            'uid' => 41648,
            'title' => 'La Semaine Nationale de la Petite Enfance',
            'description' => "Du 15 au 24 mars 2025, tous ensemble pour l'éveil du tout-petit ! \nUne semaine d'ateliers & activités à travers toute la France, pour réunir le trio parent-enfant-professionnel.",
            'slug' => 'semainepetiteenfance',
            'url' => 'https://www.semainepetiteenfance.fr',
            'image' => 'https://cdn.openagenda.com/main/agenda41648.jpg?__ts=1664443597781',
            'official' => 1,
            'private' => 0,
            'indexed' => 1,
            'networkUid' => null,
            'locationSetUid' => null,
            'createdAt' => '2016-07-27T12:24:08',
            'updatedAt' => '2025-01-04T10:31:53',
        ], $ent->toOpenAgenda());
    }

    public static function dataClientNotSet()
    {
        return [
            ['locations'],
        ];
    }

    /**
     * @dataProvider dataClientNotSet
     */
    public function testClientNotSet($method): void
    {
        OpenAgenda::resetClient();
        $entity = new Agenda(['agendaUid' => 123]);
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('OpenAgenda object was not previously created or Client not set.');
        $entity->{$method}();
    }

    public function testGetLocations()
    {
        $entity = new Agenda(['uid' => 123]);

        $wrapper = $this->clientWrapper();
        $payload = FileResource::instance($this)->getContent('Response/locations/locations.json');
        $wrapper->expects($this->once())
            ->method('get')
            ->willReturn(new Response(200, [], $payload));

        $locations = $entity->locations(['name' => 'My Location']);

        $this->assertInstanceOf(Collection::class, $locations);
    }

    public function testLocation()
    {
        $entity = new Agenda(['uid' => 123]);

        $endpoint = $entity->location([
            'uid' => 456,
            'agendaUid' => 123,
            'name' => 'My location',
            'address' => 'Random address',
            'countryCode' => 'FR',
        ]);

        $this->assertInstanceOf(LocationEndpoint::class, $endpoint);
        $this->assertEquals([
            'head' => 'https://api.openagenda.com/v2/agendas/123/locations/456',
            'get' => 'https://api.openagenda.com/v2/agendas/123/locations/456',
            'post' => 'https://api.openagenda.com/v2/agendas/123/locations/456',
            'patch' => 'https://api.openagenda.com/v2/agendas/123/locations/456',
            'delete' => 'https://api.openagenda.com/v2/agendas/123/locations/456',
            'params' => [
                '_path' => '/location',
                'uid' => 456,
                'agendaUid' => 123,
                'name' => 'My location',
                'address' => 'Random address',
                'countryCode' => 'FR',
            ],
        ], $endpoint->toArray());
    }
}
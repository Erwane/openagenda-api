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
use OpenAgenda\Endpoint\Location as LocationEndpoint;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\OpenAgendaTestCase;
use OpenAgenda\Test\Utility\FileResource;
use Ramsey\Collection\Collection;

class AgendaTest extends OpenAgendaTestCase
{
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
        $entity = new Agenda(['id' => 123]);
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('OpenAgenda object was not previously created or Client not set.');
        $entity->{$method}();
    }

    public function testGetLocations()
    {
        $entity = new Agenda(['id' => 123]);

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
        $entity = new Agenda(['id' => 123]);

        $endpoint = $entity->location([
            'id' => 456,
            'agenda_id' => 123,
            'name' => 'My location',
            'address' => 'Random address',
            'country' => 'FR',
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
                'id' => 456,
                'agenda_id' => 123,
                'name' => 'My location',
                'address' => 'Random address',
                'country' => 'FR',
            ],
        ], $endpoint->toArray());
    }
}

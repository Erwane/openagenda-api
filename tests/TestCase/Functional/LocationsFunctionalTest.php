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
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Location;
use OpenAgenda\Test\OpenAgendaTestCase;
use OpenAgenda\Test\Utility\FileResource;

/**
 * @coversNothing
 */
class LocationsFunctionalTest extends OpenAgendaTestCase
{
    public function testSearch()
    {
        // $locations = $oa->locations(['agendaUid' => 123, 'name' => 'My Location']);
        $this->markTestIncomplete();
    }

    /**
     * Test search one location from Agenda
     */
    public function testSearchFromAgenda(): void
    {
        // $locations = $agenda->locations(['name' => 'My Location']);
        $wrapper = $this->clientWrapper();

        $payload = FileResource::instance($this)->getContent('Response/locations/locations.json');
        $wrapper->expects($this->once())
            ->method('get')
            ->willReturn(new Response(200, [], $payload));

        $agenda = new Agenda(['uid' => 123]);

        $location = $agenda->locations(['search' => 'My location'])->first();
        $this->assertInstanceOf(Location::class, $location);
    }

    public function testExists(): void
    {
        // $exists = $oa->location(['uid' => 456, 'agendaUid' => 123])->exists();
        $this->markTestIncomplete();
    }

    public function testExistsFromAgenda(): void
    {
        // $exists = $agenda->location(['uid' => 456])->exists();
        $this->markTestIncomplete();
    }

    /**
     * Test getting one location from id.
     * Test location exists
     */
    public function testGet(): void
    {
        // $location = $oa->location(['uid' => 456, 'agendaUid' => 123])->get();
        [$oa, $wrapper] = $this->oa();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('get')
            ->willReturn(new Response(200, [], $payload));
        $wrapper->expects($this->once())
            ->method('head')
            ->willReturn(new Response(200));

        $location = $oa->location(['uid' => 456, 'agendaUid' => 123])->get();
        $exists = $oa->location(['uid' => 456, 'agendaUid' => 123])->exists();

        $this->assertInstanceOf(Location::class, $location);
        $this->assertTrue($exists);
    }

    /**
     * Test getting one location
     * Test location exists
     */
    public function testGetFromAgenda(): void
    {
        // $location = $agenda->location(['extId' => 'my-location-id'])->get();

        $wrapper = $this->clientWrapper();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('get')
            ->willReturn(new Response(200, [], $payload));
        $wrapper->expects($this->once())
            ->method('head')
            ->willReturn(new Response(200));

        $agenda = new Agenda(['uid' => 123]);

        $location = $agenda->location(['extId' => 'my-location-id'])->get();
        $exists = $agenda->location(['uid' => 456])->exists();

        $this->assertInstanceOf(Location::class, $location);
        $this->assertTrue($exists);
    }

    /**
     * Test create location from oa
     */
    public function testCreate(): void
    {
        // $location = $oa->location($data)->create();
        [$oa, $wrapper] = $this->oa();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->exactly(2))
            ->method('post')
            ->willReturnOnConsecutiveCalls(
                new Response(200, [], '{"access_token": "my authorization token"}'),
                new Response(200, [], $payload)
            );

        $data = [
            'agendaUid' => 123,
            'name' => 'My location',
            'address' => '122 rue de charonne, 75011 Paris, France',
            'countryCode' => 'fr',
        ];
        $location = $oa->location($data)->create();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test create location from Agenda
     */
    public function testCreateFromAgenda(): void
    {
        // location = $agenda->location($data)->create();
        $wrapper = $this->clientWrapper();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->exactly(2))
            ->method('post')
            ->willReturnOnConsecutiveCalls(
                new Response(200, [], '{"access_token": "my authorization token"}'),
                new Response(200, [], $payload)
            );

        $agenda = new Agenda(['uid' => 123]);

        $data = [
            'agendaUid' => 123,
            'name' => 'My location',
            'address' => '122 rue de charonne, 75011 Paris, France',
            'countryCode' => 'fr',
        ];
        $location = $agenda->location($data)->create();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test update location from oa
     */
    public function testUpdate(): void
    {
        // $location = $oa->location(['agendaUid' => 123, 'uid' => 456, 'state' => true])->update();
        [$oa, $wrapper] = $this->oa();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('post')
            ->willReturn(new Response(200, [], '{"access_token": "my authorization token"}'));
        $wrapper->expects($this->once())
            ->method('patch')
            ->willReturn(new Response(200, [], $payload));

        $data = ['uid' => 456, 'agendaUid' => 123, 'state' => true];
        $location = $oa->location($data)->update();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test update location from Location
     */
    public function testUpdateFromLocation(): void
    {
        // $location = $location->update(true); // Full update
        $wrapper = $this->clientWrapper();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('post')
            ->willReturn(new Response(200, [], '{"access_token": "my authorization token"}'));
        $wrapper->expects($this->once())
            ->method('patch')
            ->willReturn(new Response(200, [], $payload));

        $location = new Location(['uid' => 456, 'agendaUid' => 123]);
        $location->state = true;
        $location = $location->update();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test delete location from oa
     */
    public function testDelete(): void
    {
        // $location = $oa->location(['agendaUid' => 123, 'uid' => 456])->delete();
        [$oa, $wrapper] = $this->oa();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('post')
            ->willReturn(new Response(200, [], '{"access_token": "my authorization token"}'));
        $wrapper->expects($this->once())
            ->method('delete')
            ->willReturn(new Response(200, [], $payload));

        $data = ['uid' => 456, 'agendaUid' => 123];
        $location = $oa->location($data)->delete();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test delete location from Location
     */
    public function testDeleteFromLocation(): void
    {
        // $location = $oa->location(['uid' => 456, 'agendaUid' => 123])->get()->delete();
        $wrapper = $this->clientWrapper();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('post')
            ->willReturn(new Response(200, [], '{"access_token": "my authorization token"}'));
        $wrapper->expects($this->once())
            ->method('delete')
            ->willReturn(new Response(200, [], $payload));

        $location = new Location(['uid' => 456, 'agendaUid' => 123]);
        $location = $location->delete();
        $this->assertInstanceOf(Location::class, $location);
    }
}

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
use OpenAgenda\Test\FunctionalTestCase;
use OpenAgenda\Test\Utility\FileResource;

class LocationsFunctionalTest extends FunctionalTestCase
{
    /**
     * Test search one location from Agenda
     */
    public function testSearchLocationFromAgenda(): void
    {
        $wrapper = $this->clientWrapper();

        $payload = FileResource::instance($this)->getContent('Response/locations/locations.json');
        $wrapper->expects($this->once())
            ->method('get')
            ->willReturn(new Response(200, [], $payload));

        $agenda = new Agenda(['id' => 123]);

        $location = $agenda->locations(['search' => 'My location'])->first();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test getting one location from id.
     * Test location exists
     */
    public function testLocation(): void
    {
        [$oa, $wrapper] = $this->oa();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('get')
            ->willReturn(new Response(200, [], $payload));
        $wrapper->expects($this->once())
            ->method('head')
            ->willReturn(new Response(200));

        $location = $oa->location(['id' => 456, 'agenda_id' => 123])->get();
        $exists = $oa->location(['id' => 456, 'agenda_id' => 123])->exists();

        $this->assertInstanceOf(Location::class, $location);
        $this->assertTrue($exists);
    }

    /**
     * Test getting one location
     * Test location exists
     */
    public function testLocationFromAgenda(): void
    {
        $wrapper = $this->clientWrapper();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('get')
            ->willReturn(new Response(200, [], $payload));
        $wrapper->expects($this->once())
            ->method('head')
            ->willReturn(new Response(200));

        $agenda = new Agenda(['id' => 123]);

        $location = $agenda->location(['ext_id' => 'my-location-id'])->get();
        $exists = $agenda->location(['id' => 456])->exists();

        $this->assertInstanceOf(Location::class, $location);
        $this->assertTrue($exists);
    }

    /**
     * Test create location from oa
     */
    public function testCreate(): void
    {
        [$oa, $wrapper] = $this->oa();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->exactly(2))
            ->method('post')
            ->willReturnOnConsecutiveCalls(
                new Response(200, [], '{"access_token": "my authorization token"}'),
                new Response(200, [], $payload)
            );

        $data = ['agenda_id' => 123, 'name' => 'My location'];
        $location = $oa->location($data)->create();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test create location from Agenda
     */
    public function testCreateFromAgenda(): void
    {
        $wrapper = $this->clientWrapper();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->exactly(2))
            ->method('post')
            ->willReturnOnConsecutiveCalls(
                new Response(200, [], '{"access_token": "my authorization token"}'),
                new Response(200, [], $payload)
            );

        $agenda = new Agenda(['id' => 123]);

        $data = ['agenda_id' => 123, 'name' => 'My location'];
        $location = $agenda->location($data)->create();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test update location from oa
     */
    public function testUpdate(): void
    {
        [$oa, $wrapper] = $this->oa();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('post')
            ->willReturn(new Response(200, [], '{"access_token": "my authorization token"}'));
        $wrapper->expects($this->once())
            ->method('patch')
            ->willReturn(new Response(200, [], $payload));

        $data = ['id' => 456, 'agenda_id' => 123, 'state' => true];
        $location = $oa->location($data)->update();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test update location from Location
     */
    public function testUpdateFromLocation(): void
    {
        $wrapper = $this->clientWrapper();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('post')
            ->willReturn(new Response(200, [], '{"access_token": "my authorization token"}'));
        $wrapper->expects($this->once())
            ->method('patch')
            ->willReturn(new Response(200, [], $payload));

        $location = new Location(['id' => 456, 'agenda_id' => 123]);
        $location->state = true;
        $location = $location->update();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test delete location from oa
     */
    public function testDelete(): void
    {
        [$oa, $wrapper] = $this->oa();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('post')
            ->willReturn(new Response(200, [], '{"access_token": "my authorization token"}'));
        $wrapper->expects($this->once())
            ->method('delete')
            ->willReturn(new Response(200, [], $payload));

        $data = ['id' => 456, 'agenda_id' => 123];
        $location = $oa->location($data)->delete();
        $this->assertInstanceOf(Location::class, $location);
    }

    /**
     * Test delete location from Location
     */
    public function testDeleteFromLocation(): void
    {
        $wrapper = $this->clientWrapper();

        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $wrapper->expects($this->once())
            ->method('post')
            ->willReturn(new Response(200, [], '{"access_token": "my authorization token"}'));
        $wrapper->expects($this->once())
            ->method('delete')
            ->willReturn(new Response(200, [], $payload));

        $location = new Location(['id' => 456, 'agenda_id' => 123]);
        $location = $location->delete();
        $this->assertInstanceOf(Location::class, $location);
    }
}

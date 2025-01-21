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

use OpenAgenda\Endpoint\Location;
use OpenAgenda\Entity\Location as LocationEntity;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;

/**
 * Endpoint\Location tests
 *
 * @uses   \OpenAgenda\Endpoint\Location
 * @covers \OpenAgenda\Endpoint\Location
 */
class LocationTest extends EndpointTestCase
{
    public static function dataPresenceIdOrExtId(): array
    {
        return [
            [['data' => [], 'newRecord' => true], false],
            [['data' => [], 'newRecord' => false], true],
            [['data' => ['uid' => 1], 'newRecord' => true], false],
            [['data' => ['extId' => 1], 'newRecord' => true], false],
            [['data' => ['uid' => 1], 'newRecord' => false], false],
            [['data' => ['extId' => 1], 'newRecord' => false], false],
        ];
    }

    /** @dataProvider dataPresenceIdOrExtId */
    public function testPresenceIdOrExtId($context, $expected): void
    {
        $success = Location::presenceIdOrExtId($context);
        $this->assertSame($expected, $success);
    }

    public static function dataGetUriSuccess(): array
    {
        return [
            [
                'exists',
                ['agendaUid' => 123, 'uid' => 456, 'extId' => 'my-internal-id'],
                'path' => '/v2/agendas/123/locations/456',
            ],
            [
                'get',
                ['agendaUid' => 123, 'uid' => 456, 'extId' => 'my-internal-id'],
                'path' => '/v2/agendas/123/locations/456',
            ],
            [
                'get',
                ['agendaUid' => 123, 'extId' => 'my-internal-id'],
                '/v2/agendas/123/locations/ext/my-internal-id',
            ],
            [
                'create',
                ['agendaUid' => 123, 'uid' => 456, 'extId' => 'my-internal-id'],
                '/v2/agendas/123/locations',
            ],
            [
                'update',
                ['agendaUid' => 123, 'uid' => 456],
                '/v2/agendas/123/locations/456',
            ],
            [
                'update',
                ['agendaUid' => 123, 'extId' => 'my-internal-id'],
                '/v2/agendas/123/locations/ext/my-internal-id',
            ],
            [
                'delete',
                ['agendaUid' => 123, 'uid' => 456],
                '/v2/agendas/123/locations/456',
            ],
            [
                'delete',
                ['agendaUid' => 123, 'extId' => 'my-internal-id'],
                '/v2/agendas/123/locations/ext/my-internal-id',
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriSuccess
     */
    public function testGetUriSuccess($method, $params, $expected)
    {
        $endpoint = new Location($params);
        $url = $endpoint->getUrl($method);
        $this->assertEquals($expected, parse_url($url, PHP_URL_PATH));
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/agendas/123/locations/35867424',
            ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Location(['agendaUid' => 123, 'uid' => 35867424]);

        $entity = $endpoint->get();

        $this->assertInstanceOf(LocationEntity::class, $entity);
        $this->assertFalse($entity->isNew());
        $this->assertEquals(35867424, $entity->uid);
        $this->assertEquals(123, $entity->agendaUid);
    }

    public function testExists()
    {
        $this->mockRequest(false, 'head', [
            'https://api.openagenda.com/v2/agendas/123/locations/456',
            ['headers' => ['key' => 'publicKey']],
        ], [200, '']);

        $endpoint = new Location(['agendaUid' => 123, 'uid' => 456]);
        $exists = $endpoint->exists();

        $this->assertTrue($exists);
    }

    public function testCreate()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/post.json');
        $this->mockRequest(true, 'post', [
            'https://api.openagenda.com/v2/agendas/123/locations',
            [
                'name' => 'My location',
                'address' => '1, place liberté, 75001 Paris, France',
                'countryCode' => 'FR',
                'state' => '1',
            ],
        ], [200, $payload]);

        $endpoint = new Location([
            'agendaUid' => 123,
            'uid' => 16153029,
            'name' => 'My location',
            'address' => '1, place liberté, 75001 Paris, France',
            'countryCode' => 'FR',
            'state' => true,
        ]);

        $entity = $endpoint->create();
        $this->assertInstanceOf(LocationEntity::class, $entity);
        $this->assertTrue($entity->isNew());
        $this->assertEquals(16153029, $entity->uid);
        $this->assertEquals(123, $entity->agendaUid);
    }

    public function testUpdate()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/post.json');
        $this->mockRequest(true, 'patch', [
            'https://api.openagenda.com/v2/agendas/123/locations/16153029',
            [
                'state' => 1,
            ],
        ], [200, $payload]);

        $endpoint = new Location([
            'agendaUid' => 123,
            'uid' => 16153029,
            'state' => 1,
        ]);

        $entity = $endpoint->update();
        $this->assertInstanceOf(LocationEntity::class, $entity);
        $this->assertFalse($entity->isNew());
        $this->assertEquals(16153029, $entity->uid);
        $this->assertEquals(123, $entity->agendaUid);
    }

    public function testDelete()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/delete.json');
        $this->mockRequest(true, 'delete', [
            'https://api.openagenda.com/v2/agendas/123/locations/82680484',
        ], [200, $payload]);

        $endpoint = new Location(['agendaUid' => 123, 'uid' => 82680484]);
        $entity = $endpoint->delete();
        $this->assertInstanceOf(LocationEntity::class, $entity);
        $this->assertFalse($entity->isNew());
        $this->assertEquals(82680484, $entity->uid);
        $this->assertEquals(123, $entity->agendaUid);
    }
}

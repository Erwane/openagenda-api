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

use Cake\Validation\Validator;
use OpenAgenda\Endpoint\Location;
use OpenAgenda\Entity\Location as LocationEntity;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;
use OpenAgenda\Validation;

/**
 * Endpoint\Location tests
 *
 * @uses   \OpenAgenda\Endpoint\Location
 * @covers \OpenAgenda\Endpoint\Location
 */
class LocationTest extends EndpointTestCase
{
    public function testValidationUriPath()
    {
        $endpoint = new Location([]);

        $v = $endpoint->validationUriPath(new Validator());

        // agendaUid
        $field = $v->field('agendaUid');
        $this->assertTrue($field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);
    }

    public function testValidationUriPathWithId()
    {
        $endpoint = new Location([]);

        $v = $endpoint->validationUriPathWithId(new Validator());

        // agendaUid
        $this->assertTrue($v->hasField('agendaUid'));

        // id
        $field = $v->field('uid');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('presenceIdOrExtId', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // extId
        $field = $v->field('extId');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('presenceIdOrExtId', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);
    }

    public static function dataValidationUriPath(): array
    {
        return [
            ['exists'],
            ['get'],
            ['update'],
            ['delete'],
        ];
    }

    /**
     * @dataProvider dataValidationUriPath
     * @covers       \OpenAgenda\Endpoint\Location::validationUriPathExists
     * @covers       \OpenAgenda\Endpoint\Location::validationUriPathGet
     * @covers       \OpenAgenda\Endpoint\Location::validationUriPathUpdate
     * @covers       \OpenAgenda\Endpoint\Location::validationUriPathDelete
     */
    public function testValidationUriPathMethods($method)
    {
        $endpoint = new Location();

        $method = 'validationUriPath' . ucfirst($method);

        $v = $endpoint->{$method}(new Validator());
        $this->assertTrue($v->hasField('agendaUid'));
        $this->assertTrue($v->hasField('uid'));
        $this->assertTrue($v->hasField('extId'));
    }

    public static function dataValidationCreateUpdate()
    {
        return [
            ['validationCreate'],
            ['validationUpdate'],
        ];
    }

    /**
     * Testing validations for post and patch
     *
     * @uses         \OpenAgenda\Endpoint\Location::validationCreate()
     * @uses         \OpenAgenda\Endpoint\Location::validationUpdate()
     * @dataProvider dataValidationCreateUpdate
     */
    public function testValidationCreateUpdate($method)
    {
        $endpoint = new Location([]);

        /** @var \Cake\Validation\Validator $v */
        $v = $endpoint->{$method}(new Validator());

        // agendaUid
        $this->assertTrue($v->hasField('agendaUid'));

        // id
        $field = $v->field('uid');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('presenceIdOrExtId', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // extId
        $field = $v->field('extId');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('presenceIdOrExtId', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // name
        $field = $v->field('name');
        $this->assertEquals('create', $field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertEquals([100], $rules['maxLength']->get('pass'));

        // address
        $field = $v->field('address');
        $this->assertEquals('create', $field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertEquals([255], $rules['maxLength']->get('pass'));

        // countryCode
        $field = $v->field('countryCode');
        $this->assertEquals('create', $field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertEquals([2, 2], $rules['lengthBetween']->get('pass'));

        // state
        $field = $v->field('state');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);

        // description
        $field = $v->field('description');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals([Validation::class, 'multilingual'], $rules['multilingual']->get('rule'));
        $this->assertEquals([5000], $rules['multilingual']->get('pass'));

        // access
        $field = $v->field('access');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals([Validation::class, 'multilingual'], $rules['multilingual']->get('rule'));
        $this->assertEquals([1000], $rules['multilingual']->get('pass'));

        // website
        $field = $v->field('website');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('url', $rules);

        // email
        $field = $v->field('email');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('email', $rules);

        // phone
        $field = $v->field('phone');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals([Validation::class, 'phone'], $rules['phone']->get('rule'));

        // links
        $field = $v->field('links');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('isArray', $rules);

        // image
        $field = $v->field('image');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertIsCallable($rules['image']->get('rule'));
        $this->assertEquals('checkImage', $rules['image']->get('rule')[1]);
        $this->assertEquals(10, $rules['image']->get('pass')[0]);

        // imageCredits
        $field = $v->field('imageCredits');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // region
        $field = $v->field('region');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // department
        $field = $v->field('department');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // district
        $field = $v->field('district');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // city
        $field = $v->field('city');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // postalCode
        $field = $v->field('postalCode');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // insee
        $field = $v->field('insee');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // latitude
        $field = $v->field('latitude');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('numeric', $rules);

        // longitude
        $field = $v->field('longitude');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('numeric', $rules);

        // timezone
        $field = $v->field('timezone');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);
    }

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

    public static function dataCheckImage(): array
    {
        $path = 'resources/wendywei-1537637.jpg';
        $realPath = TESTS . $path;

        return [
            [['file'], 1, false],
            ['resources/wendywei-1537637.jpg', 1, false],
            [$realPath, 0.001, false],
            [fopen($realPath, 'r'), 0.001, false],
            [$realPath, 1, true],
            [fopen($realPath, 'r'), 1, true],
        ];
    }

    /** @dataProvider dataCheckImage */
    public function testCheckImage($input, $limit, $expected): void
    {
        $success = Location::checkImage($input, $limit);
        $this->assertSame($expected, $success);
    }

    public static function dataGetUriErrors(): array
    {
        return [
            [
                'exists',
                [],
                [
                    'agendaUid' => [
                        '_required' => 'This field is required',
                    ],
                    'uid' => [
                        '_required' => 'One of `id` or `extId` is required',
                    ],
                    'extId' => [
                        '_required' => 'One of `id` or `extId` is required',
                    ],
                ],
            ],
            [
                'get',
                [],
                [
                    'agendaUid' => [
                        '_required' => 'This field is required',
                    ],
                    'uid' => [
                        '_required' => 'One of `id` or `extId` is required',
                    ],
                    'extId' => [
                        '_required' => 'One of `id` or `extId` is required',
                    ],
                ],
            ],
            [
                'create',
                [],
                [
                    'agendaUid' => [
                        '_required' => 'This field is required',
                    ],
                ],
            ],
            [
                'update',
                [],
                [
                    'agendaUid' => [
                        '_required' => 'This field is required',
                    ],
                    'uid' => [
                        '_required' => 'One of `id` or `extId` is required',
                    ],
                    'extId' => [
                        '_required' => 'One of `id` or `extId` is required',
                    ],
                ],
            ],
            [
                'delete',
                [],
                [
                    'agendaUid' => [
                        '_required' => 'This field is required',
                    ],
                    'uid' => [
                        '_required' => 'One of `id` or `extId` is required',
                    ],
                    'extId' => [
                        '_required' => 'One of `id` or `extId` is required',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriErrors
     */
    public function testGetUriErrors($method, $params, $expected)
    {
        $endpoint = new Location($params);
        $message = [
            'message' => 'OpenAgenda\\Endpoint\\Location has errors.',
            'errors' => $expected,
        ];
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $endpoint->getUri($method);
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
        $uri = $endpoint->getUri($method);
        $this->assertEquals($expected, $uri->getPath());
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/agendas/123/locations/456',
            ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Location(['agendaUid' => 123, 'uid' => 456]);

        $entity = $endpoint->get();

        $this->assertInstanceOf(LocationEntity::class, $entity);
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
            ['headers' => ['access-token' => 'authorization-key', 'nonce' => 1734957296123456]],
        ], [200, $payload]);

        $endpoint = new Location([
            'agendaUid' => 123,
            'uid' => 456,
            'name' => 'My location',
            'address' => '1, place liberté, 75001 Paris, France',
            'countryCode' => 'FR',
            'state' => true,
        ]);

        $entity = $endpoint->create();
        $this->assertInstanceOf(LocationEntity::class, $entity);
    }

    public function testUpdate()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/post.json');
        $this->mockRequest(true, 'patch', [
            'https://api.openagenda.com/v2/agendas/123/locations/456',
            [
                'state' => 1,
            ],
            ['headers' => ['access-token' => 'authorization-key', 'nonce' => 1734957296123456]],
        ], [200, $payload]);

        $endpoint = new Location([
            'agendaUid' => 123,
            'uid' => 456,
            'state' => 1,
        ]);

        $entity = $endpoint->update();
        $this->assertInstanceOf(LocationEntity::class, $entity);
    }

    public function testCreateException(): void
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessageMatches('/"name":{"_required"/');
        $endpoint = new Location(['agendaUid' => 1]);
        $endpoint->create();
    }

    public function testUpdateException(): void
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessageMatches('/"uid":{"_required"/');
        $endpoint = new Location(['agendaUid' => 1]);
        $endpoint->update();
    }

    public function testDelete()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/delete.json');
        $this->mockRequest(true, 'delete', [
            'https://api.openagenda.com/v2/agendas/123/locations/456',
            ['headers' => ['access-token' => 'authorization-key', 'nonce' => 1734957296123456]],
        ], [200, $payload]);

        $endpoint = new Location(['agendaUid' => 123, 'uid' => 456]);
        $entity = $endpoint->delete();
        $this->assertInstanceOf(LocationEntity::class, $entity);
    }
}

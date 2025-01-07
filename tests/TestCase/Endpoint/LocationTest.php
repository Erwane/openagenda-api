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
use InvalidArgumentException;
use OpenAgenda\Endpoint\Location;
use OpenAgenda\Entity\Location as LocationEntity;
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
    public function testValidationUriPathGet()
    {
        $endpoint = new Location([]);

        $v = $endpoint->validationUriPathGet(new Validator());

        // agenda_id
        $this->assertTrue($v->hasField('agenda_id'));
        $field = $v->field('agenda_id');
        $this->assertTrue($field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // id
        $field = $v->field('id');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('checkIdOrExtId', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // ext_id
        $field = $v->field('ext_id');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('checkIdOrExtId', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);
    }

    public function testValidationUriPathExists()
    {
        $endpoint = new Location([]);

        $v = $endpoint->validationUriPathExists(new Validator());
        $this->assertTrue($v->hasField('agenda_id'));
        $this->assertTrue($v->hasField('id'));
        $this->assertTrue($v->hasField('ext_id'));
    }

    public function testValidationUriPathDelete()
    {
        $endpoint = new Location([]);

        $v = $endpoint->validationUriPathDelete(new Validator());
        $this->assertTrue($v->hasField('agenda_id'));
        $this->assertTrue($v->hasField('id'));
        $this->assertTrue($v->hasField('ext_id'));
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

        $v = $endpoint->{$method}(new Validator());

        // agenda_id
        // todo test agenda_id field ?
        // $this->assertTrue($v->hasField('agenda_id'));

        // id
        $field = $v->field('id');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('checkIdOrExtId', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // ext_id
        $field = $v->field('ext_id');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('checkIdOrExtId', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // name
        $field = $v->field('name');
        $this->assertEquals('create', $field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);
        $this->assertArrayHasKey('maxLength', $rules);
        $this->assertEquals([100], $rules['maxLength']->get('pass'));

        // address
        $field = $v->field('address');
        $this->assertEquals('create', $field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);
        $this->assertArrayHasKey('maxLength', $rules);
        $this->assertEquals([255], $rules['maxLength']->get('pass'));

        // country
        $field = $v->field('country');
        $this->assertEquals('create', $field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);
        $this->assertArrayHasKey('lengthBetween', $rules);
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
        $this->assertArrayHasKey('multilingual', $rules);
        $this->assertEquals([Validation::class, 'multilingual'], $rules['multilingual']->get('rule'));
        $this->assertEquals([5000], $rules['multilingual']->get('pass'));

        // access
        $field = $v->field('access');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('multilingual', $rules);
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
        $this->assertArrayHasKey('phone', $rules);
        $this->assertEquals([Validation::class, 'phone'], $rules['phone']->get('rule'));

        // links
        $field = $v->field('links');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('isArray', $rules);

        // image
        // todo

        // image_credits
        $field = $v->field('image_credits');
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

        // postal_code
        $field = $v->field('postal_code');
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

    public static function dataGetUriErrors(): array
    {
        return [
            [
                'get',
                [],
                [
                    'agenda_id' => [
                        '_required' => 'This field is required',
                    ],
                    'id' => [
                        '_required' => 'One of `id` or `ext_id` is required',
                    ],
                    'ext_id' => [
                        '_required' => 'One of `id` or `ext_id` is required',
                    ],
                ],
            ],
            [
                'get',
                ['agenda_id' => 123],
                [
                    'id' => [
                        '_required' => 'One of `id` or `ext_id` is required',
                    ],
                    'ext_id' => [
                        '_required' => 'One of `id` or `ext_id` is required',
                    ],
                ],
            ],
            [
                'create',
                [],
                [
                    'agenda_id' => [
                        '_required' => 'This field is required',
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $endpoint->getUri($method);
    }

    public static function dataGetUriSuccess(): array
    {
        return [
            [
                'get',
                ['agenda_id' => 123, 'id' => 456, 'ext_id' => 'my-internal-id'],
                'path' => '/v2/agendas/123/locations/456',
            ],
            [
                'get',
                ['agenda_id' => 123, 'ext_id' => 'my-internal-id'],
                '/v2/agendas/123/locations/ext/my-internal-id',
            ],
            [
                'create',
                ['agenda_id' => 123, 'id' => 456, 'ext_id' => 'my-internal-id'],
                '/v2/agendas/123/locations',
            ],
            [
                'update',
                ['agenda_id' => 123, 'ext_id' => 'my-internal-id'],
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
            ['headers' => ['key' => 'testing']],
        ], [200, $payload]);

        $endpoint = new Location(['agenda_id' => 123, 'id' => 456]);

        $entity = $endpoint->get();

        $this->assertInstanceOf(LocationEntity::class, $entity);
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
            ],
            ['headers' => ['access-token' => 'authorization-key', 'nonce' => 1734957296123456]],
        ], [200, $payload]);

        $endpoint = new Location([
            'agenda_id' => 123,
            'id' => 456,
            'name' => 'My location',
            'address' => '1, place liberté, 75001 Paris, France',
            'country' => 'FR',
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
            'agenda_id' => 123,
            'id' => 456,
            'state' => 1,
        ]);

        $entity = $endpoint->update();
        $this->assertInstanceOf(LocationEntity::class, $entity);
    }

    public function testDelete()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/delete.json');
        $this->mockRequest(true, 'delete', [
            'https://api.openagenda.com/v2/agendas/123/locations/456',
            ['headers' => ['access-token' => 'authorization-key', 'nonce' => 1734957296123456]],
        ], [200, $payload]);

        $endpoint = new Location(['agenda_id' => 123, 'id' => 456]);
        $entity = $endpoint->delete();
        $this->assertInstanceOf(LocationEntity::class, $entity);
    }
}

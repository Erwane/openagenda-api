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

use Cake\Validation\Validation;
use Cake\Validation\Validator;
use InvalidArgumentException;
use OpenAgenda\Endpoint\Locations;
use OpenAgenda\Entity\Location as LocationEntity;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;
use Ramsey\Collection\Collection;

/**
 * Endpoint\Locations tests
 *
 * @uses   \OpenAgenda\Endpoint\Locations
 * @covers \OpenAgenda\Endpoint\Locations
 */
class LocationsTest extends EndpointTestCase
{
    public function testValidationUriPath()
    {
        $endpoint = new Locations([]);

        $v = $endpoint->validationUriPath(new Validator());

        // agenda_id
        $this->assertTrue($v->hasField('agenda_id'));
        $field = $v->field('agenda_id');
        $this->assertTrue($field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);
    }

    public function testValidationUriPathGet()
    {
        $endpoint = new Locations();

        $v = $endpoint->validationUriPathGet(new Validator());

        // agenda_id
        $this->assertTrue($v->hasField('agenda_id'));
        $field = $v->field('agenda_id');
        $this->assertTrue($field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // limit
        $this->assertTrue($v->hasField('limit'));
        $field = $v->field('limit');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('numeric', $rules);
        $this->assertArrayHasKey('greaterThanOrEqual', $rules);
        $this->assertEquals(['>=', 1], $rules['greaterThanOrEqual']->get('pass'));

        // detailed
        $this->assertTrue($v->hasField('detailed'));
        $field = $v->field('detailed');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);

        // state
        $this->assertTrue($v->hasField('state'));
        $field = $v->field('state');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);

        // search
        $this->assertTrue($v->hasField('search'));
        $field = $v->field('search');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // created_lte
        $this->assertTrue($v->hasField('created_lte'));
        $field = $v->field('created_lte');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('dateTime', $rules);
        $this->assertEquals(['ymd', Validation::DATETIME_ISO8601], $rules['dateTime']->get('pass')[0]);

        // created_gte
        $this->assertTrue($v->hasField('created_gte'));
        $field = $v->field('created_gte');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('dateTime', $rules);
        $this->assertEquals(['ymd', Validation::DATETIME_ISO8601], $rules['dateTime']->get('pass')[0]);

        // updated_lte
        $this->assertTrue($v->hasField('updated_lte'));
        $field = $v->field('updated_lte');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('dateTime', $rules);
        $this->assertEquals(['ymd', Validation::DATETIME_ISO8601], $rules['dateTime']->get('pass')[0]);

        // updated_gte
        $this->assertTrue($v->hasField('updated_gte'));
        $field = $v->field('updated_gte');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('dateTime', $rules);
        $this->assertEquals(['ymd', Validation::DATETIME_ISO8601], $rules['dateTime']->get('pass')[0]);

        // sort
        $this->assertTrue($v->hasField('sort'));
        $field = $v->field('sort');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);
        $this->assertArrayHasKey('inList', $rules);
        $this->assertEquals([
            'name_asc',
            'name_desc',
            'created_asc',
            'created_desc',
        ], $rules['inList']->get('pass')[0]);
    }

    public static function dataGetUriErrors(): array
    {
        return [
            [
                'GET',
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
        $endpoint = new Locations($params);
        $message = [
            'message' => 'OpenAgenda\\Endpoint\\Locations has errors.',
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
                'GET',
                ['agenda_id' => 123],
                [
                    'path' => '/v2/agendas/123/locations',
                    'query' => [],
                ],
            ],
            [
                'GET',
                [
                    'agenda_id' => 123,
                    'limit' => 2,
                    'search' => 'Location',
                    'detailed' => true,
                    'state' => true,
                    'created_lte' => '2023-06-02',
                    'updated_lte' => '2023-06-02T12:40:00+0100',
                    'sort' => 'created_desc',
                ],
                [
                    'path' => '/v2/agendas/123/locations',
                    'query' => [
                        'size' => '2',
                        'search' => 'Location',
                        'detailed' => '1',
                        'state' => '1',
                        'createdAt' => ['lte' => '2023-06-02T00:00:00'],
                        'updatedAt' => ['lte' => '2023-06-02T12:40:00'],
                        'sort' => 'createdAt.desc',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriSuccess
     */
    public function testGetUriSuccess($method, $params, $expected)
    {
        $endpoint = new Locations($params);
        $uri = $endpoint->getUri($method);
        $this->assertEquals($expected['path'], $uri->getPath());
        parse_str((string)$uri->getQuery(), $query);
        $this->assertEquals($expected['query'], $query);
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/locations-ok.json');
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/agendas/123/locations?size=2',
            ['headers' => ['key' => 'testing']],
        ], [200, $payload]);

        $endpoint = new Locations(['agenda_id' => 123, 'limit' => 2]);

        $locations = $endpoint->get();

        $this->assertInstanceOf(Collection::class, $locations);
        $this->assertEquals(LocationEntity::class, $locations->getType());
        $this->assertCount(1, $locations);
    }
}

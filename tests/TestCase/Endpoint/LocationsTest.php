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
use OpenAgenda\Validator;
use OpenAgenda\Collection;
use OpenAgenda\Endpoint\Locations;
use OpenAgenda\Entity\Location as LocationEntity;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;

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

        // agendaUid
        $field = $v->field('agendaUid');
        $this->assertTrue($field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);
    }

    public function testValidationUriPathGet()
    {
        $endpoint = new Locations();

        $v = $endpoint->validationUriPathGet(new Validator());

        // agendaUid
        $field = $v->field('agendaUid');
        $this->assertTrue($field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // limit
        $field = $v->field('size');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('numeric', $rules);
        $this->assertEquals(['>=', 1], $rules['greaterThanOrEqual']->get('pass'));

        // detailed
        $field = $v->field('detailed');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);

        // state
        $field = $v->field('state');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);

        // search
        $field = $v->field('search');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // createdAt[lte]
        $field = $v->field('createdAt[lte]');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals(['ymd', Validation::DATETIME_ISO8601], $rules['dateTime']->get('pass')[0]);

        // createdAt[gte]
        $field = $v->field('createdAt[gte]');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals(['ymd', Validation::DATETIME_ISO8601], $rules['dateTime']->get('pass')[0]);

        // updated_lte
        $field = $v->field('updatedAt[lte]');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals(['ymd', Validation::DATETIME_ISO8601], $rules['dateTime']->get('pass')[0]);

        // updated_gte
        $field = $v->field('updatedAt[gte]');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals(['ymd', Validation::DATETIME_ISO8601], $rules['dateTime']->get('pass')[0]);

        // order
        $field = $v->field('order');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals([
            'name.asc',
            'name.desc',
            'createdAt.asc',
            'createdAt.desc',
        ], $rules['inList']->get('pass')[0]);
    }

    public static function dataGetUriErrors(): array
    {
        return [
            [
                'get',
                [],
                [
                    'agendaUid' => [
                        '_required' => 'This field is required',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriErrors
     */
    public function testGetUrlErrors($method, $params, $expected)
    {
        $endpoint = new Locations($params);
        $message = [
            'message' => 'OpenAgenda\\Endpoint\\Locations has errors.',
            'errors' => $expected,
        ];
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $endpoint->getUrl($method);
    }

    public static function dataGetUriSuccess(): array
    {
        return [
            [
                'get',
                ['agendaUid' => 123],
                [
                    'path' => '/v2/agendas/123/locations',
                    'query' => [],
                ],
            ],
            [
                'get',
                [
                    'agendaUid' => 123,
                    'size' => 2,
                    'search' => 'Location',
                    'detailed' => true,
                    'state' => true,
                    'createdAt[lte]' => '2023-06-02',
                    'updatedAt[lte]' => '2023-06-02T12:40:00+0100',
                    'order' => 'createdAt.desc',
                ],
                [
                    'path' => '/v2/agendas/123/locations',
                    'query' => [
                        'size' => '2',
                        'search' => 'Location',
                        'detailed' => '1',
                        'state' => '1',
                        'createdAt' => ['lte' => '2023-06-02T00:00:00'],
                        'updatedAt' => ['lte' => '2023-06-02T11:40:00'],
                        'order' => 'createdAt.desc',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriSuccess
     */
    public function testGetUrlSuccess($method, $params, $expected)
    {
        $endpoint = new Locations($params);
        $url = $endpoint->getUrl($method);
        $this->assertEquals($expected['path'], parse_url($url, PHP_URL_PATH));
        parse_str((string)parse_url($url, PHP_URL_QUERY), $query);
        $this->assertEquals($expected['query'], $query);
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/locations.json');
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/agendas/123/locations?size=2',
            ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Locations(['agendaUid' => 123, 'size' => 2]);

        $results = $endpoint->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(LocationEntity::class, $results->first());
        $this->assertCount(1, $results);
    }
}

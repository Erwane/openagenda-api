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
use OpenAgenda\Endpoint\Events;
use OpenAgenda\Entity\Event as EventEntity;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;
use OpenAgenda\Validation;
use Ramsey\Collection\Collection;

/**
 * Endpoint\Events tests
 *
 * @uses   \OpenAgenda\Endpoint\Events
 * @covers \OpenAgenda\Endpoint\Events
 */
class EventsTest extends EndpointTestCase
{
    public function testValidationUriPath()
    {
        $endpoint = new Events();

        $v = $endpoint->validationUriPath(new Validator());

        $this->assertCount(1, $v);

        // agendaUid
        $field = $v->field('agendaUid');
        $this->assertTrue($field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);
    }

    public function testValidationUriQueryGet()
    {
        $endpoint = new Events();

        $v = $endpoint->validationUriQueryGet(new Validator());

        $this->assertCount(26, $v);

        // detailed
        $field = $v->field('detailed');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);

        // longDescriptionFormat
        $field = $v->field('longDescriptionFormat');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals(['markdown', 'HTML', 'HTMLWithEmbeds'], $rules['inList']->get('pass')[0]);

        // size
        $field = $v->field('size');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // page
        $field = $v->field('page');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // includeLabels
        $field = $v->field('includeLabels');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);

        // includeFields
        $field = $v->field('includeFields');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('isArray', $rules);

        // monolingual
        $field = $v->field('monolingual');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals([Validation::class, 'lang'], $rules['monolingual']->get('rule'));

        // removed
        $field = $v->field('removed');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);

        // city
        $field = $v->field('city');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('isArray', $rules);

        // department
        $field = $v->field('department');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('isArray', $rules);

        // region
        $field = $v->field('region');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // timings[lte]
        $field = $v->field('timings[lte]');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('dateTime', $rules);

        // timings[gte]
        $field = $v->field('timings[gte]');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('dateTime', $rules);

        // updatedAt[lte]
        $field = $v->field('updatedAt[lte]');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('dateTime', $rules);

        // updatedAt[gte]
        $field = $v->field('updatedAt[gte]');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('dateTime', $rules);

        // search
        $field = $v->field('search');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // uid
        $field = $v->field('uid');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('isArray', $rules);

        // slug
        $field = $v->field('slug');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // featured
        $field = $v->field('featured');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);

        // relative
        $field = $v->field('relative');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals(['passed', 'upcoming', 'current'], $rules['multipleOptions']->get('pass')[0]);

        // state
        $field = $v->field('state');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals([
            EventEntity::STATE_REFUSED,
            EventEntity::STATE_MODERATION,
            EventEntity::STATE_READY,
            EventEntity::STATE_PUBLISHED,
        ], $rules['inList']->get('pass')[0]);

        // keyword
        $field = $v->field('keyword');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('isArray', $rules);

        // geo
        $field = $v->field('geo');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertIsCallable($rules['geo']->get('rule'));
        $this->assertEquals('checkGeo', $rules['geo']->get('rule')[1]);

        // locationUid
        $field = $v->field('locationUid');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('isArray', $rules);

        // accessibility
        $field = $v->field('accessibility');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals([
            EventEntity::ACCESS_HI,
            EventEntity::ACCESS_II,
            EventEntity::ACCESS_VI,
            EventEntity::ACCESS_MI,
            EventEntity::ACCESS_PI,
        ], $rules['multipleOptions']->get('pass')[0]);

        // status
        $field = $v->field('status');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertEquals([
            EventEntity::STATUS_SCHEDULED,
            EventEntity::STATUS_RESCHEDULED,
            EventEntity::STATUS_ONLINE,
            EventEntity::STATUS_DEFERRED,
            EventEntity::STATUS_FULL,
            EventEntity::STATUS_CANCELED,
        ], $rules['multipleOptions']->get('pass')[0]);
    }

    public static function dataCheckGeo(): array
    {
        return [
            [[], true],
            [['northEast' => ['lat' => 48.9527, 'lng' => 2.4484], 'southWest' => ['lat' => 48.8560, 'lng' => 2.1801]], true],
            [['northEast' => ['lng' => 2.4484], 'southWest' => ['lat' => 48.8560, 'lng' => 2.1801]], false],
            [['northEast' => ['lat' => 48.9527], 'southWest' => ['lat' => 48.8560, 'lng' => 2.1801]], false],
            [['northEast' => ['lat' => 48.9527, 'lng' => 2.4484], 'southWest' => ['lng' => 2.1801]], false],
            [['northEast' => ['lat' => 48.9527, 'lng' => 2.4484], 'southWest' => ['lat' => 48.8560]], false],
        ];
    }

    /**
     * @dataProvider dataCheckGeo
     */
    public function testCheckGeo($context, $expected): void
    {
        $result = Events::checkGeo($context);
        $this->assertSame($expected, $result);
    }

    public function testGetUriErrors()
    {
        $endpoint = new Events([]);
        $message = [
            'message' => 'OpenAgenda\\Endpoint\\Events has errors.',
            'errors' => [
                'agendaUid' => [
                    '_required' => 'This field is required',
                ],
            ],
        ];
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $endpoint->getUri('GET');
    }

    public static function dataGetUriSuccess(): array
    {
        return [
            [
                'get',
                ['agendaUid' => 123],
                [
                    'path' => '/v2/agendas/123/events',
                    'query' => [],
                ],
            ],
            [
                'get',
                [
                    'agendaUid' => 123,
                    'detailed' => 1,
                    'longDescriptionFormat' => 'markdown',
                    'size' => 2,
                    'includeLabels' => 1,
                    'includeFields' => ['uid', 'title'],
                    'monolingual' => 'fr',
                    'removed' => 1,
                    'city' => ['Paris'],
                    'department' => ['Hauts-de-Seine'],
                    'region' => 'Normandie',
                    'timings[gte]' => '2021-02-07T09:00:00',
                    'timings[lte]' => '2022-02-07T09:00:00',
                    'updatedAt[gte]' => '2023-02-07T09:00:00',
                    'updatedAt[lte]' => '2024-02-07T09:00:00',
                    'search' => 'Concert',
                    'uid' => [56158955, 55895615],
                    'slug' => 'festival-dete',
                    'featured' => 1,
                    'relative' => ['passed', 'upcoming'],
                    'state' => EventEntity::STATE_READY,
                    'keyword' => ['gratuit', 'exposition'],
                    'geo' => ['northEast' => ['lat' => 48.9527, 'lng' => 2.4484], 'southWest' => ['lat' => 48.8560, 'lng' => 2.1801]],
                    'locationUid' => [123, 456],
                    'accessibility' => [EventEntity::ACCESS_HI, EventEntity::ACCESS_PI],
                    'status' => [EventEntity::STATUS_SCHEDULED, EventEntity::STATUS_RESCHEDULED],
                ],
                [
                    'path' => '/v2/agendas/123/events',
                    'query' => [
                        'detailed' => '1',
                        'longDescriptionFormat' => 'markdown',
                        'size' => '2',
                        'includeLabels' => '1',
                        'includeFields' => ['uid', 'title'],
                        'monolingual' => 'fr',
                        'removed' => '1',
                        'city' => ['Paris'],
                        'department' => ['Hauts-de-Seine'],
                        'region' => 'Normandie',
                        'timings' => [
                            'gte' => '2021-02-07T09:00:00',
                            'lte' => '2022-02-07T09:00:00',
                        ],
                        'updatedAt' => [
                            'gte' => '2023-02-07T09:00:00',
                            'lte' => '2024-02-07T09:00:00',
                        ],
                        'search' => 'Concert',
                        'uid' => [56158955, 55895615],
                        'slug' => 'festival-dete',
                        'featured' => '1',
                        'relative' => ['passed', 'upcoming'],
                        'state' => '1',
                        'keyword' => ['gratuit', 'exposition'],
                        'geo' => ['northEast' => ['lat' => 48.9527, 'lng' => 2.4484], 'southWest' => ['lat' => 48.8560, 'lng' => 2.1801]],
                        'locationUid' => [123, 456],
                        'accessibility' => ['hi', 'pi'],
                        'status' => [1, 2],
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
        $endpoint = new Events($params);
        $uri = $endpoint->getUri($method);
        $this->assertEquals($expected['path'], $uri->getPath());
        parse_str((string)$uri->getQuery(), $query);
        $this->assertEquals($expected['query'], $query);
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/events/events.json');
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/agendas/123/events?size=2',
            ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Events(['agendaUid' => 123, 'size' => 2]);

        $results = $endpoint->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(EventEntity::class, $results->getType());
        $this->assertCount(1, $results);
    }
}

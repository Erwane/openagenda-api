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

use Cake\Chronos\Chronos;
use Cake\Validation\Validator;
use InvalidArgumentException;
use OpenAgenda\Endpoint\Event;
use OpenAgenda\Entity\Event as EventEntity;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;
use OpenAgenda\Validation;

/**
 * Endpoint\Events tests
 *
 * @uses   \OpenAgenda\Endpoint\Event
 * @covers \OpenAgenda\Endpoint\Event
 */
class EventTest extends EndpointTestCase
{
    public function testValidationUriPathGet()
    {
        $endpoint = new Event([]);

        $v = $endpoint->validationUriPathGet(new Validator());

        $this->assertCount(2, $v);

        // agendaUid
        $field = $v->field('agendaUid');
        $this->assertTrue($field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // id
        $field = $v->field('uid');
        $this->assertTrue($field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);
    }

    public function testValidationUriPathExists()
    {
        $endpoint = new Event([]);

        $v = $endpoint->validationUriPathExists(new Validator());

        $this->assertCount(2, $v);
        // agendaUid
        $field = $v->field('agendaUid');
        $this->assertTrue($field->isPresenceRequired());
        // uid
        $field = $v->field('uid');
        $this->assertTrue($field->isPresenceRequired());
    }

    public function testValidationUriPathCreate()
    {
        $endpoint = new Event([]);

        $v = $endpoint->validationUriPathCreate(new Validator());

        $this->assertCount(1, $v);
        // agendaUid
        $field = $v->field('agendaUid');
        $this->assertTrue($field->isPresenceRequired());
    }

    public function testValidationUriPathUpdate()
    {
        $endpoint = new Event([]);

        $v = $endpoint->validationUriPathUpdate(new Validator());

        $this->assertCount(2, $v);
        // agendaUid
        $field = $v->field('agendaUid');
        $this->assertTrue($field->isPresenceRequired());
        // uid
        $field = $v->field('uid');
        $this->assertTrue($field->isPresenceRequired());
    }

    public function testValidationUriPathDelete()
    {
        $endpoint = new Event([]);

        $v = $endpoint->validationUriPathDelete(new Validator());

        $this->assertCount(2, $v);
        // agendaUid
        $field = $v->field('agendaUid');
        $this->assertTrue($field->isPresenceRequired());
        // uid
        $field = $v->field('uid');
        $this->assertTrue($field->isPresenceRequired());
    }

    public function testValidationUriQueryGet(): void
    {
        $endpoint = new Event([]);

        $v = $endpoint->validationUriQueryGet(new Validator());

        $this->assertCount(1, $v);

        // longDescriptionFormat
        $field = $v->field('longDescriptionFormat');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('inList', $rules);
        $this->assertEquals(['markdown', 'HTML', 'HTMLWithEmbeds'], $rules['inList']->get('pass')[0]);
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
     * @uses         \OpenAgenda\Endpoint\Event::validationCreate()
     * @uses         \OpenAgenda\Endpoint\Event::validationUpdate()
     * @dataProvider dataValidationCreateUpdate
     */
    public function testValidationCreateUpdate($method)
    {
        $endpoint = new Event([]);

        /** @var \Cake\Validation\Validator $v */
        $v = $endpoint->{$method}(new Validator());

        $this->assertCount(18, $v);

        // agendaUid
        $this->assertTrue($v->hasField('agendaUid'));

        // id
        $field = $v->field('uid');
        $this->assertSame('update', $field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // title
        $field = $v->field('title');
        $this->assertSame('create', $field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('multilingual', $rules);
        $this->assertEquals([Validation::class, 'multilingual'], $rules['multilingual']->get('rule'));
        $this->assertEquals(140, $rules['multilingual']->get('pass')[0]);

        // description
        $field = $v->field('description');
        $this->assertSame('create', $field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('multilingual', $rules);
        $this->assertEquals([Validation::class, 'multilingual'], $rules['multilingual']->get('rule'));
        $this->assertEquals(200, $rules['multilingual']->get('pass')[0]);

        // longDescription
        $field = $v->field('longDescription');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('multilingual', $rules);
        $this->assertEquals([Validation::class, 'multilingual'], $rules['multilingual']->get('rule'));
        $this->assertEquals(10000, $rules['multilingual']->get('pass')[0]);

        // conditions
        $field = $v->field('conditions');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('multilingual', $rules);
        $this->assertEquals([Validation::class, 'multilingual'], $rules['multilingual']->get('rule'));
        $this->assertEquals(255, $rules['multilingual']->get('pass')[0]);

        // keywords
        $field = $v->field('keywords');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('multilingual', $rules);
        $this->assertEquals([Validation::class, 'multilingual'], $rules['multilingual']->get('rule'));
        $this->assertEquals(255, $rules['multilingual']->get('pass')[0]);

        // todo image
        $field = $v->field('image');
        $this->assertTrue($field->isEmptyAllowed());
        // $rules = $field->rules();

        // imageCredits
        $field = $v->field('imageCredits');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('maxLength', $rules);
        $this->assertEquals(255, $rules['maxLength']->get('pass')[0]);

        // registration
        $field = $v->field('registration');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('isArray', $rules);

        // accessibility
        $field = $v->field('accessibility');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('accessibility', $rules);
        $this->assertEquals([Validation::class, 'accessibility'], $rules['accessibility']->get('rule'));

        // timings
        $field = $v->field('timings');
        $this->assertSame('create', $field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('timings', $rules);
        $this->assertEquals('checkTimings', $rules['timings']->get('rule')[1]);

        // age
        $field = $v->field('age');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('age', $rules);
        $this->assertEquals('checkAge', $rules['age']->get('rule')[1]);

        // locationUid
        $field = $v->field('locationUid');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('presenceLocationId', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // attendanceMode
        $field = $v->field('attendanceMode');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('inList', $rules);
        $this->assertEquals([
            EventEntity::ATTENDANCE_OFFLINE,
            EventEntity::ATTENDANCE_ONLINE,
            EventEntity::ATTENDANCE_MIXED,
        ], $rules['inList']->get('pass')[0]);

        // onlineAccessLink
        $field = $v->field('onlineAccessLink');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('presenceOnlineAccessLink', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('url', $rules);

        // status
        $field = $v->field('status');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('inList', $rules);
        $this->assertEquals([
            EventEntity::STATUS_SCHEDULED,
            EventEntity::STATUS_RESCHEDULED,
            EventEntity::STATUS_ONLINE,
            EventEntity::STATUS_DEFERRED,
            EventEntity::STATUS_FULL,
            EventEntity::STATUS_CANCELED,
        ], $rules['inList']->get('pass')[0]);

        // state
        $field = $v->field('state');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('inList', $rules);
        $this->assertEquals([
            EventEntity::STATE_REFUSED,
            EventEntity::STATE_MODERATION,
            EventEntity::STATE_READY,
            EventEntity::STATE_PUBLISHED,
        ], $rules['inList']->get('pass')[0]);
    }

    public static function dataCheckTimings(): array
    {
        return [
            [
                [],
                false,
            ],
            [
                [['begin' => '2025-01-06T11:00:00+01:00', 'end' => '2025-01-06T13:00:00+01:00']],
                true,
            ],
            [
                [
                    [
                        'begin' => Chronos::parse('2025-01-06T11:00:00+01:00'),
                        'end' => Chronos::parse('2025-01-06T12:00:00+01:00'),
                    ],
                ],
                true,
            ],
            // same date
            [
                [['begin' => '2025-01-06T11:00:00+01:00', 'end' => '2025-01-06T11:00:00+01:00']],
                false,
            ],
            [
                [['begin' => 'not a date time', 'end' => '2025-01-06T11:00:00+01:00']],
                false,
            ],
            [
                [['begin' => '2025-01-06T11:00:00+01:00', 'end' => 'not a date time']],
                false,
            ],
            [
                [['begin' => '2025-01-06T11:00:00+01:00']],
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataCheckTimings
     */
    public function testCheckTimings($input, $expected)
    {
        $success = Event::checkTimings($input);
        $this->assertSame($expected, $success);
    }

    public static function dataCheckAge(): array
    {
        return [
            [
                [],
                true,
            ],
            [
                ['min' => null, 'max' => null],
                true,
            ],
            [
                ['min' => 7, 'max' => 120],
                true,
            ],
            [
                ['min' => null, 'max' => 120],
                false,
            ],
            [
                ['min' => 7, 'max' => null],
                false,
            ],
            [
                ['max' => 120],
                false,
            ],
            [
                ['min' => 7],
                false,
            ],
            [
                ['min' => 18, 'max' => 16],
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataCheckAge
     */
    public function testCheckAge($input, $expected)
    {
        $success = Event::checkAge($input);
        $this->assertSame($expected, $success);
    }

    public static function dataPresenceLocationId(): array
    {
        return [
            // New record, no attendanceMode require location uid
            [
                ['newRecord' => true, 'data' => []],
                true,
            ],
            // Update record
            [
                ['newRecord' => false, 'data' => []],
                false,
            ],
            [
                ['data' => ['attendanceMode' => EventEntity::ATTENDANCE_OFFLINE]],
                true,
            ],
            [
                ['data' => ['attendanceMode' => EventEntity::ATTENDANCE_MIXED]],
                true,
            ],
            [
                ['data' => ['attendanceMode' => EventEntity::ATTENDANCE_ONLINE]],
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataPresenceLocationId
     */
    public function testPresenceLocationId($context, $expected)
    {
        $success = Event::presenceLocationId($context);
        $this->assertSame($expected, $success);
    }

    public static function dataPresenceOnlineAccessLink(): array
    {
        return [
            [
                ['data' => ['attendanceMode' => EventEntity::ATTENDANCE_OFFLINE]],
                false,
            ],
            [
                ['data' => ['attendanceMode' => EventEntity::ATTENDANCE_MIXED]],
                true,
            ],
            [
                ['data' => ['attendanceMode' => EventEntity::ATTENDANCE_ONLINE]],
                true,
            ],
        ];
    }

    /**
     * @dataProvider dataPresenceOnlineAccessLink
     */
    public function testPresenceOnlineAccessLink($context, $expected)
    {
        $success = Event::presenceOnlineAccessLink($context);
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
                        '_required' => 'This field is required',
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
                        '_required' => 'This field is required',
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
                        '_required' => 'This field is required',
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
        $endpoint = new Event($params);
        $message = [
            'message' => 'OpenAgenda\\Endpoint\\Event has errors.',
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
                'exists',
                ['agendaUid' => 123, 'uid' => 456],
                'path' => '/v2/agendas/123/events/456',
            ],
            [
                'get',
                ['agendaUid' => 123, 'uid' => 456],
                'path' => '/v2/agendas/123/events/456',
            ],
            [
                'create',
                ['agendaUid' => 123, 'uid' => 456],
                '/v2/agendas/123/events',
            ],
            [
                'update',
                ['agendaUid' => 123, 'uid' => 456],
                '/v2/agendas/123/events/456',
            ],
            [
                'delete',
                ['agendaUid' => 123, 'uid' => 456],
                '/v2/agendas/123/events/456',
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriSuccess
     */
    public function testGetUriSuccess($method, $params, $expected)
    {
        $endpoint = new Event($params);
        $uri = $endpoint->getUri($method);
        $this->assertEquals($expected, $uri->getPath());
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/events/event.json');
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/agendas/123/events/456',
            ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Event(['agendaUid' => 123, 'uid' => 456]);

        $entity = $endpoint->get();

        $this->assertInstanceOf(EventEntity::class, $entity);
    }

    public function testExists()
    {
        $this->mockRequest(false, 'head', [
            'https://api.openagenda.com/v2/agendas/123/events/456',
            ['headers' => ['key' => 'publicKey']],
        ], [200, '']);

        $endpoint = new Event(['agendaUid' => 123, 'uid' => 456]);
        $exists = $endpoint->exists();

        $this->assertTrue($exists);
    }

    public function testCreate()
    {
        $payload = FileResource::instance($this)->getContent('Response/events/post.json');
        $this->mockRequest(true, 'post', [
            'https://api.openagenda.com/v2/agendas/123/events',
            [
                'locationUid' => 789,
                'title' => ['fr' => 'My Event'],
                'description' => ['fr' => 'Event description'],
                'timings' => [
                    ['begin' => '2025-01-06T11:00:00+01:00', 'end' => '2025-01-06T15:00:00+01:00'],
                ],
            ],
            ['headers' => ['access-token' => 'authorization-key', 'nonce' => 1734957296123456]],
        ], [200, $payload]);

        $endpoint = new Event([
            'agendaUid' => 123,
            'uid' => 456,
            'locationUid' => 789,
            'title' => 'My Event',
            'description' => 'Event description',
            'timings' => [['begin' => '2025-01-06T11:00:00.000+01:00', 'end' => '2025-01-06T15:00:00.000+01:00']],
        ]);

        $entity = $endpoint->create();
        $this->assertInstanceOf(EventEntity::class, $entity);
    }

    public function testUpdate()
    {
        $payload = FileResource::instance($this)->getContent('Response/events/post.json');
        $this->mockRequest(true, 'patch', [
            'https://api.openagenda.com/v2/agendas/123/events/456',
            [
                'state' => EventEntity::STATE_PUBLISHED,
            ],
            ['headers' => ['access-token' => 'authorization-key', 'nonce' => 1734957296123456]],
        ], [200, $payload]);

        $endpoint = new Event([
            'agendaUid' => 123,
            'uid' => 456,
            'state' => EventEntity::STATE_PUBLISHED,
        ]);

        $entity = $endpoint->update();
        $this->assertInstanceOf(EventEntity::class, $entity);
    }

    public function testDelete()
    {
        $payload = FileResource::instance($this)->getContent('Response/events/delete.json');
        $this->mockRequest(true, 'delete', [
            'https://api.openagenda.com/v2/agendas/123/events/456',
            ['headers' => ['access-token' => 'authorization-key', 'nonce' => 1734957296123456]],
        ], [200, $payload]);

        $endpoint = new Event(['agendaUid' => 123, 'uid' => 456]);
        $entity = $endpoint->delete();
        $this->assertInstanceOf(EventEntity::class, $entity);
    }
}

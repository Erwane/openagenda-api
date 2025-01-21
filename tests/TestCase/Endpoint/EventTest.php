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

use GuzzleHttp\Psr7\Response;
use OpenAgenda\DateTime;
use OpenAgenda\Endpoint\Event;
use OpenAgenda\Entity\Event as EventEntity;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;
use OpenAgenda\Wrapper\HttpWrapperException;

/**
 * Endpoint\Events tests
 *
 * @uses   \OpenAgenda\Endpoint\Event
 * @covers \OpenAgenda\Endpoint\Event
 */
class EventTest extends EndpointTestCase
{
    public static function dataCheckImage(): array
    {
        $jpg = TESTS . 'resources/wendywei-1537637.jpg';
        $png = TESTS . 'resources/wendywei-1537637.png';
        $webp = TESTS . 'resources/wendywei-1537637.webp';

        return [
            ['resources/wendywei-1537637.jpg', 1, false],
            [$jpg, 0.001, false],
            [fopen($jpg, 'r'), 0.001, false],
            [$jpg, 1, true],
            [fopen($jpg, 'r'), 1, true],
            [fopen($png, 'r'), 1, true],
            [fopen($webp, 'r'), 1, true],
        ];
    }

    /** @dataProvider dataCheckImage */
    public function testCheckImageUseValidationImage($check, $limit, $expected): void
    {
        $success = Event::checkImage($check, $limit);
        $this->assertSame($expected, $success);
    }

    public function testCheckImageNoClient(): void
    {
        OpenAgenda::resetClient();
        $this->expectException(OpenAgendaException::class);
        Event::checkImage('https://httpbin.org/image/png');
    }

    public function testCheckImageUrlNotJpeg(): void
    {
        $this->wrapper->expects($this->once())
            ->method('head')
            ->with('https://httpbin.org/image/bmp')
            ->willReturn(new Response(200, ['content-type' => 'image/bmp', 'content-length' => 1000]));

        $success = Event::checkImage('https://httpbin.org/image/bmp');
        $this->assertFalse($success);
    }

    public function testCheckImageWrapperException(): void
    {
        $this->wrapper->expects($this->once())
            ->method('head')
            ->willThrowException(new HttpWrapperException());

        $success = Event::checkImage('https://httpbin.org/image/png');
        $this->assertFalse($success);
    }

    public function testCheckImageUrlJpegTooLarge(): void
    {
        $this->wrapper->expects($this->once())
            ->method('head')
            ->with('https://httpbin.org/image/jpeg')
            ->willReturn(new Response(200, ['content-type' => 'image/jpeg', 'content-length' => 20000]));

        $success = Event::checkImage('https://httpbin.org/image/jpeg', 0.001);
        $this->assertFalse($success);
    }

    public function testCheckImageUrlSuccess(): void
    {
        $this->wrapper->expects($this->once())
            ->method('head')
            ->with('https://httpbin.org/image/jpeg')
            ->willReturn(new Response(200, ['content-type' => 'image/jpeg', 'content-length' => 848153]));

        $success = Event::checkImage('https://httpbin.org/image/jpeg');
        $this->assertTrue($success);
    }

    public static function dataCheckTimings(): array
    {
        return [
            [
                [],
                false,
            ],
            [
                ['begin' => '2025-01-06T11:00:00+01:00', 'end' => '2025-01-06T13:00:00+01:00'],
                false,
            ],
            [
                [['begin' => '2025-01-06T11:00:00+01:00', 'end' => '2025-01-06T13:00:00+01:00']],
                true,
            ],
            [
                [
                    [
                        'begin' => DateTime::parse('2025-01-06T11:00:00+01:00'),
                        'end' => DateTime::parse('2025-01-06T12:00:00+01:00'),
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

    public static function dataCheckAccessibility()
    {
        return [
            [[], true],
            [
                [
                    EventEntity::ACCESS_HI => true,
                    EventEntity::ACCESS_II => true,
                    EventEntity::ACCESS_MI => true,
                    EventEntity::ACCESS_PI => true,
                    EventEntity::ACCESS_VI => true,
                ], true,
            ],
            [
                [
                    EventEntity::ACCESS_HI,
                ], false,
            ],
            [
                ['unknown' => true], false,
            ],
        ];
    }

    /**
     * @dataProvider dataCheckAccessibility
     */
    public function testCheckAccessibility($input, $expected)
    {
        $success = Event::checkAccessibility($input);
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
        $url = $endpoint->getUrl($method);
        $this->assertEquals($expected, parse_url($url, PHP_URL_PATH));
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/events/event.json');
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/agendas/123/events/9906334',
            ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Event(['agendaUid' => 123, 'uid' => 9906334]);

        $entity = $endpoint->get();

        $this->assertInstanceOf(EventEntity::class, $entity);
        $this->assertFalse($entity->isNew());
        $this->assertEquals(9906334, $entity->uid);
        $this->assertEquals(123, $entity->agendaUid);
        $this->assertEquals(456, $entity->locationUid);
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
            'https://api.openagenda.com/v2/agendas/41630080/events',
            [
                'locationUid' => 42921249,
                'title' => ['fr' => 'My Event'],
                'description' => ['fr' => 'Event description'],
                'timings' => [
                    ['begin' => '2025-01-06T11:00:00+01:00', 'end' => '2025-01-06T15:00:00+01:00'],
                ],
            ],
        ], [200, $payload]);

        $endpoint = new Event([
            'agendaUid' => 41630080,
            'uid' => 456,
            'locationUid' => 42921249,
            'title' => 'My Event',
            'description' => 'Event description',
            'timings' => [['begin' => '2025-01-06T11:00:00.000+01:00', 'end' => '2025-01-06T15:00:00.000+01:00']],
        ]);

        $entity = $endpoint->create();
        $this->assertInstanceOf(EventEntity::class, $entity);
        $this->assertTrue($entity->isNew());
        $this->assertEquals(41294774, $entity->uid);
        $this->assertEquals(41630080, $entity->agendaUid);
        $this->assertEquals(42921249, $entity->locationUid);
    }

    public function testUpdate()
    {
        $payload = FileResource::instance($this)->getContent('Response/events/post.json');
        $this->mockRequest(true, 'patch', [
            'https://api.openagenda.com/v2/agendas/41630080/events/41294774',
            [
                'state' => EventEntity::STATE_PUBLISHED,
            ],
        ], [200, $payload]);

        $endpoint = new Event([
            'agendaUid' => 41630080,
            'uid' => 41294774,
            'state' => EventEntity::STATE_PUBLISHED,
        ]);

        $entity = $endpoint->update();
        $this->assertInstanceOf(EventEntity::class, $entity);
        $this->assertFalse($entity->isNew());
        $this->assertEquals(41294774, $entity->uid);
        $this->assertEquals(41630080, $entity->agendaUid);
        $this->assertEquals(42921249, $entity->locationUid);
    }

    public function testDelete()
    {
        $payload = FileResource::instance($this)->getContent('Response/events/delete.json');
        $this->mockRequest(true, 'delete', [
            'https://api.openagenda.com/v2/agendas/41630080/events/41294774',
        ], [200, $payload]);

        $endpoint = new Event(['agendaUid' => 41630080, 'uid' => 41294774]);
        $entity = $endpoint->delete();
        $this->assertInstanceOf(EventEntity::class, $entity);
        $this->assertFalse($entity->isNew());
        $this->assertEquals(41294774, $entity->uid);
        $this->assertEquals(41630080, $entity->agendaUid);
        $this->assertEquals(42921249, $entity->locationUid);
    }
}

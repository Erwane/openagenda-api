<?php
/**
 * @noinspection PhpParamsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use OpenAgenda\Cache;
use OpenAgenda\Client;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Event;
use OpenAgenda\Entity\Location;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \OpenAgenda\OpenAgenda
 */
class OpenAgendaTest extends TestCase
{
    /**
     * @var \OpenAgenda\OpenAgenda
     */
    private $oa = null;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::clear();
        $this->oa = $this->mock(['getAccessToken']);
    }

    /**
     * @test
     * @covers ::setAgendaUid
     * @covers ::getAgendaUid
     */
    public function testAgendaUid(): void
    {
        $this->assertNull($this->oa->getAgendaUid());

        $this->oa->setAgendaUid(23);
        $this->assertSame(23, $this->oa->getAgendaUid());
    }

    /**
     * @test
     * @covers ::setBaseUrl
     * @covers ::newEvent
     */
    public function testSetBaseUrl(): void
    {
        $this->oa->setBaseUrl('https://openagenda.com');

        $event = $this->oa->newEvent();

        $this->assertSame('https://openagenda.com/', $event->get('baseUrl'));
    }

    /**
     * @test
     * @covers ::setClient
     */
    public function testSetClient(): void
    {
        $client = $this->createMock(Client::class);

        $this->oa->setClient($client);

        $this->assertSame($this->oa->getClient(), $client);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getClient
     */
    public function testGetClient(): void
    {
        $this->assertInstanceOf(Client::class, $this->oa->getClient());
    }

    /**
     * @test
     * @covers ::getAccessToken
     */
    public function testGetAccessTokenFromCache(): void
    {
        Cache::set('openagenda-token', sha1('access-token'));

        $oa = new OpenAgenda('testing', 'secret');
        $token = $oa->getAccessToken();

        $this->assertSame('4dda0f28d8f74c276185fe75b126ef54a7f67ff1', $token);
    }

    public function dataGetAccessTokenInvalid()
    {
        return [
            [401, '{"access_token": "testing","expires_in": 3600}'],
            [200, '{"access_token": "","expires_in": 3600}'],
        ];
    }

    /**
     * @test
     * @covers ::getAccessToken
     * @dataProvider dataGetAccessTokenInvalid
     */
    public function testGetAccessTokenEmpty(int $code, string $body): void
    {
        $client = $this->createPartialMock(Client::class, ['post']);
        $client->expects(self::once())
            ->method('post')
            ->willReturn(new Response($code, [], $body));

        $oa = new OpenAgenda('testing', 'secret');
        $oa->setClient($client);

        $token = $oa->getAccessToken();

        $this->assertNull($token);
    }

    /**
     * @test
     * @covers ::getAccessToken
     */
    public function testGetAccessTokenException(): void
    {
        $client = $this->createPartialMock(Client::class, ['post']);
        $client->expects(self::once())
            ->method('post')
            ->willThrowException(new OpenAgendaException());

        $oa = new OpenAgenda('testing', 'secret');
        $oa->setClient($client);

        $token = $oa->getAccessToken();

        $this->assertNull($token);
    }

    /**
     * @test
     * @covers ::getAccessToken
     */
    public function testGetAccessToken(): void
    {
        $client = $this->createPartialMock(Client::class, ['post']);
        $client->expects(self::once())
            ->method('post')
            ->with('/requestAccessToken', [
                'json' => [
                    'grant-type' => 'authorization_code',
                    'code' => 'secret',
                ],
            ])
            ->willReturn(new Response(
                200,
                [],
                '{"access_token": "4dda0f28d8f74c276185fe75b126ef54a7f67ff1","expires_in": 3600}'
            ));

        $oa = new OpenAgenda('testing', 'secret');
        $oa->setClient($client);

        $token = $oa->getAccessToken();

        $this->assertSame('4dda0f28d8f74c276185fe75b126ef54a7f67ff1', $token);
    }

    /**
     * @test
     * @covers ::getLocation
     */
    public function testGetLocationInvalideData(): void
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('invalid location data');

        $this->oa->getLocation('testing');
    }

    /**
     * @test
     * @covers ::getLocation
     */
    public function testGetLocationNew(): void
    {
        $oa = $this->mock(['createLocation']);
        $oa->expects(self::once())
            ->method('createLocation')
            ->willReturn(1);

        $location = $oa->getLocation(['latitude' => 1.1, 'longitude' => 2.2]);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertTrue($location->isNew());
        $this->assertEmpty($location->getDirty());
        $this->assertSame(1, $location->get('id'));
        $this->assertSame(1.1, $location->latitude);
        $this->assertSame(2.2, $location->longitude);
    }

    private function mock(array $methods)
    {
        return $this->getMockBuilder(OpenAgenda::class)
            ->setConstructorArgs(['testing', 'secret'])
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @test
     * @covers ::getLocation
     */
    public function testGetLocationExisting(): void
    {
        $oa = $this->mock(['createLocation']);
        $oa->expects(self::never())
            ->method('createLocation');

        $location = $oa->getLocation(1);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertFalse($location->isNew());
        $this->assertSame(1, $location->get('id'));
    }

    public function dataCreateLocationMissingField(): array
    {
        return [
            [
                [],
                'missing name field',
            ],
            [
                ['name' => 'testing'],
                'missing latitude field',
            ],
            [
                [
                    'name' => 'testing',
                    'latitude' => 1.1,
                ],
                'missing longitude field',
            ],
            [
                [
                    'name' => 'testing',
                    'latitude' => 1.1,
                    'longitude' => 2.2,
                ],
                'missing address field',
            ],
            [
                [
                    'name' => 'testing',
                    'latitude' => 1.1,
                    'longitude' => 2.2,
                    'address' => 'Street',
                ],
                'missing countryCode field',
            ],
        ];
    }

    /**
     * @test
     * @covers ::createLocation
     * @dataProvider dataCreateLocationMissingField
     */
    public function testCreateLocationNoPlacename(array $data, string $message): void
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage($message);

        $this->oa->createLocation($data);
    }

    /**
     * @test
     * @covers ::createLocation
     */
    public function testCreateLocationRequestFailed(): void
    {
        $client = $this->createPartialMock(Client::class, ['post']);
        $client->expects(self::once())
            ->method('post')
            ->willThrowException(new OpenAgendaException());

        $oa = $this->mock(['getClient', 'getAccessToken']);
        $oa->expects(self::once())
            ->method('getAccessToken')
            ->willReturn('access-token');

        $oa->expects(self::once())
            ->method('getClient')
            ->willReturn($client);

        $result = $oa->createLocation([
            'name' => 'testing',
            'latitude' => 1.1,
            'longitude' => 2.2,
            'address' => 'Street',
            'countryCode' => 'FR',
        ]);

        $this->assertNull($result);
    }

    /**
     * @test
     * @covers ::createLocation
     */
    public function testCreateLocationApiFail(): void
    {
        $client = $this->createPartialMock(Client::class, ['post']);
        $client->expects(self::once())
            ->method('post')
            ->with('/agendas/1/locations')
            ->willReturn(new Response(400, [], ''));
        $oa = $this->mock(['getClient', 'getAccessToken']);
        $oa->expects(self::once())
            ->method('getAccessToken')
            ->willReturn('access-token');

        $oa->expects(self::once())
            ->method('getClient')
            ->willReturn($client);

        $result = $oa->setAgendaUid(1)
            ->createLocation([
                'name' => 'testing',
                'latitude' => 1.1,
                'longitude' => 2.2,
                'address' => 'Street',
                'countryCode' => 'FR',
            ]);

        $this->assertNull($result);
    }

    /**
     * @test
     * @covers ::createLocation
     */
    public function testCreateLocation(): void
    {
        $client = $this->createPartialMock(Client::class, ['post']);
        $client->expects(self::once())
            ->method('post')
            ->with('/agendas/1/locations')
            ->willReturn(new Response(200, [], <<<JSON
{
  "success": true,
  "location": {
    "uid": 123456,
    "setUid": null,
    "slug": "test-location_3585156"
  }
}
JSON
            ));

        $oa = $this->mock(['getClient', 'getAccessToken']);
        $oa->expects(self::once())
            ->method('getAccessToken')
            ->willReturn('access-token');

        $oa->expects(self::once())
            ->method('getClient')
            ->willReturn($client);

        $result = $oa->setAgendaUid(1)
            ->createLocation([
                'name' => 'testing',
                'latitude' => 1.1,
                'longitude' => 2.2,
                'address' => 'Street',
                'countryCode' => 'FR',
            ]);

        $this->assertSame(123456, $result);
    }

    /**
     * @test
     * @covers ::getUidFromSlug
     */
    public function testGetUidFromSlugNumeric(): void
    {
        $agenda = $this->oa->getUidFromSlug(1);

        $this->assertSame(1, $agenda->uid);
    }

    /**
     * @test
     * @covers ::getUidFromSlug
     */
    public function testGetUidFromSlugInCache(): void
    {
        Cache::set('openagenda-id', ['in-cache' => 2], 2);

        $client = $this->createPartialMock(Client::class, ['get']);
        $client->expects(self::never())->method('get');

        $this->oa->setClient($client);

        $agenda = $this->oa->getUidFromSlug('in-cache');

        $this->assertSame(2, $agenda->uid);
    }

    /**
     * @test
     * @covers ::getUidFromSlug
     */
    public function testGetUidFromSlugClientException(): void
    {
        $client = $this->createPartialMock(Client::class, ['get']);
        $client->expects(self::once())
            ->method('get')
            ->willThrowException(new OpenAgendaException('response error', 500));

        $this->oa->setClient($client);

        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('response error');

        $this->oa->getUidFromSlug('testing');
    }

    /**
     * @test
     * @covers ::getUidFromSlug
     */
    public function testGetUidFromSlugRequestException(): void
    {
        $client = $this->createPartialMock(Client::class, ['get']);
        $client->expects(self::once())
            ->method('get')
            ->willThrowException(new OpenAgendaException('fail'));

        $this->oa->setClient($client);

        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('fail');

        $this->oa->getUidFromSlug('testing');
    }

    /**
     * @test
     * @covers ::getUidFromSlug
     */
    public function testGetUidFromSlugNoData(): void
    {
        $client = $this->createPartialMock(Client::class, ['get']);
        $client->expects(self::once())
            ->method('get')
            ->with('/agendas', ['query' => ['limit' => 1, 'slug[]' => 'testing']])
            ->willReturn(new Response(
                200, [],
                '{"agendas": [],"total": 0,"success": true}'
            ));

        $this->oa->setClient($client);
        $agenda = $this->oa->getUidFromSlug('testing');
        $this->assertNull($agenda);
    }

    /**
     * @test
     * @covers ::getUidFromSlug
     */
    public function testGetUidFromSlug(): void
    {
        $client = $this->createPartialMock(Client::class, ['get']);
        $client->expects(self::once())
            ->method('get')
            ->with('/agendas', ['query' => ['limit' => 1, 'slug[]' => 'testing']])
            ->willReturn(new Response(
                200, [],
                '{"agendas": [{"uid": 123,"slug": "agendatrad"}],"total": 1,"success": true}'
            ));

        $this->oa->setClient($client);
        $agenda = $this->oa->getUidFromSlug('testing');
        $this->assertInstanceOf(Agenda::class, $agenda);
        $this->assertSame(123, $agenda->uid);

        $this->assertSame(['testing' => 123], Cache::get('openagenda-id'));
    }

    /**
     * @test
     * @covers ::getAgendaSettings
     */
    public function testGetAgendaSettingsException(): void
    {
        $client = $this->createPartialMock(Client::class, ['get']);
        $client->expects(self::once())
            ->method('get')
            ->willThrowException(new OpenAgendaException('fail'));

        $this->oa->setClient($client);

        $result = $this->oa->getAgendaSettings();
        $this->assertNull($result);
    }

    /**
     * @test
     * @covers ::getAgendaSettings
     */
    public function testGetAgendaSettings(): void
    {
        $client = $this->createPartialMock(Client::class, ['get']);
        $client->expects(self::once())
            ->method('get')
            ->with('/agendas/1')
            ->willReturn(new Response(
                200,
                [],
                '{"uid": 1, "schema": {"fields": []}}'
            ));

        $this->oa->setClient($client)->setAgendaUid(1);

        $result = $this->oa->getAgendaSettings();
        $this->assertSame([
            'uid' => 1,
            'schema' => ['fields' => []],
        ], $result);
    }

    /**
     * @test
     * @covers ::publishEvent
     */
    public function testPublishEventFail(): void
    {
        $this->oa->expects(self::once())
            ->method('getAccessToken')
            ->willReturn('access-token');

        $client = $this->createPartialMock(Client::class, ['post']);
        $client->expects(self::once())
            ->method('post')
            ->willReturn(new Response(
                200,
                [],
                '{"event": {}}'
            ));

        $this->oa->setClient($client)->setAgendaUid(1);

        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Publish event failed');

        $event = new Event([]);
        $this->oa->publishEvent($event);
    }

    /**
     * @test
     * @covers ::publishEvent
     */
    public function testPublishEventSuccess(): void
    {
        $this->oa->expects(self::once())
            ->method('getAccessToken')
            ->willReturn('access-token');

        $client = $this->createPartialMock(Client::class, ['post']);
        $client->expects(self::once())
            ->method('post')
            ->with('/agendas/1/events')
            ->willReturn(new Response(
                200,
                [],
                '{"success": true,"event": {"uid": 123456789}}'
            ));

        $this->oa->setClient($client)->setAgendaUid(1);

        $event = new Event([]);
        $this->oa->publishEvent($event);
        $this->assertSame(123456789, $event->uid);
    }

    /**
     * @test
     * @covers ::updateEvent
     */
    public function testUpdateEventNoUid(): void
    {
        $event = new Event([]);
        $result = $this->oa->updateEvent($event);
        $this->assertFalse($result);
    }

    /**
     * @test
     * @covers ::updateEvent
     */
    public function testUpdateEventException(): void
    {
        $this->oa->expects(self::once())
            ->method('getAccessToken')
            ->willReturn('access-token');

        $client = $this->createPartialMock(Client::class, ['post']);
        $client->expects(self::once())
            ->method('post')
            ->willThrowException(new OpenAgendaException('fail'));

        $this->oa->setClient($client);

        $event = new Event(['uid' => 123456]);
        $result = $this->oa->updateEvent($event);
        $this->assertFalse($result);
    }

    /**
     * @test
     * @covers ::updateEvent
     */
    public function testUpdateEventFail(): void
    {
        $this->oa->expects(self::once())
            ->method('getAccessToken')
            ->willReturn('access-token');

        $client = $this->createPartialMock(Client::class, ['post']);
        $client->expects(self::once())
            ->method('post')
            ->willReturn(new Response(
                200,
                [],
                '{"event": {}}'
            ));

        $this->oa->setClient($client)->setAgendaUid(1);

        $event = new Event(['uid' => 123456]);
        $result = $this->oa->updateEvent($event);
        $this->assertFalse($result);
    }

    /**
     * @test
     * @covers ::updateEvent
     */
    public function testUpdateEventNotDirty(): void
    {
        $event = new Event(['uid' => 123456]);
        $event->setDirty('uid', false);

        $result = $this->oa->updateEvent($event);
        $this->assertTrue($result);
    }

    /**
     * @test
     * @covers ::updateEvent
     */
    public function testUpdateEventSuccess(): void
    {
        $this->oa->expects(self::once())
            ->method('getAccessToken')
            ->willReturn('access-token');

        $client = $this->createPartialMock(Client::class, ['post']);
        $client->expects(self::once())
            ->method('post')
            ->with('/agendas/1/events/123456')
            ->willReturn(new Response(
                200,
                [],
                '{"success": true,"event": {"uid": 123456789}}'
            ));

        $this->oa->setClient($client)->setAgendaUid(1);

        $event = new Event(['uid' => 123456]);
        $result = $this->oa->updateEvent($event);
        $this->assertTrue($result);
    }

    /**
     * @test
     * @covers ::getEvent
     */
    public function testGetEventNotFound(): void
    {
        $client = $this->createPartialMock(Client::class, ['get']);
        $client->expects(self::once())
            ->method('get')
            ->willReturn(new Response(404, [], '{}'));

        $this->oa->setClient($client)->setAgendaUid(1);

        $event = $this->oa->getEvent(1);
        $this->assertNull($event);
    }

    /**
     * @test
     * @covers ::getEvent
     */
    public function testGetEventException(): void
    {
        $client = $this->createPartialMock(Client::class, ['get']);
        $client->expects(self::once())
            ->method('get')
            ->willThrowException(new OpenAgendaException('fail'));

        $this->oa->setClient($client);

        $event = $this->oa->getEvent(123456);
        $this->assertNull($event);
    }

    /**
     * @test
     * @covers ::getEvent
     */
    public function testGetEvent(): void
    {
        $client = $this->createPartialMock(Client::class, ['get']);
        $client->expects(self::once())
            ->method('get')
            ->with('/agendas/1/events/123456')
            ->willReturn(new Response(200, [], <<<JSON
{
  "success": true,
  "event": {
    "uid": 123456,
    "longDescription": {},
    "country": {"code": "FR"},
    "keywords": {},
    "location": {"uid": 123, "latitude": 46.995241, "longitude": 0.527344},
    "status": 1
  }
}
JSON
            ));

        $this->oa->setClient($client)->setAgendaUid(1);

        $event = $this->oa->getEvent(123456);
        $this->assertInstanceOf(Event::class, $event);
        $this->assertInstanceOf(Location::class, $event->location);
        $this->assertSame(123456, $event->id);
        $this->assertSame(123, $event->location->id);
    }

    /**
     * @test
     * @covers ::deleteEvent
     */
    public function testDeleteEventInvalid(): void
    {
        $event = new Event([]);

        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('require valid event');
        $this->oa->deleteEvent($event);
    }

    /**
     * @test
     * @covers ::deleteEvent
     */
    public function testDeleteEventFail(): void
    {
        $this->oa->expects(self::once())
            ->method('getAccessToken')
            ->willReturn('access-token');

        $client = $this->createPartialMock(Client::class, ['delete']);
        $client->expects(self::once())
            ->method('delete')
            ->willReturn(new Response(403, [], '{}'));

        $this->oa->setClient($client)->setAgendaUid(1);

        $event = new Event(['uid' => 123456]);
        $result = $this->oa->deleteEvent($event);

        $this->assertFalse($result);
    }

    /**
     * @test
     * @covers ::deleteEvent
     */
    public function testDeleteEventSuccess(): void
    {
        $this->oa->expects(self::once())
            ->method('getAccessToken')
            ->willReturn('access-token');

        $client = $this->createPartialMock(Client::class, ['get', 'delete']);
        // get event
        $client->expects(self::once())
            ->method('get')
            ->with('/agendas/1/events/123456')
            ->willReturn(new Response(200, [], '{"success": true, "event": {"uid": 123456}}'));

        // delete event
        $client->expects(self::once())
            ->method('delete')
            ->with('/agendas/1/events/123456')
            ->willReturn(new Response(200, [], '{"success": true, "event": {"uid": 123456}}'));

        $this->oa->setClient($client)->setAgendaUid(1);

        $result = $this->oa->deleteEvent(123456);

        $this->assertTrue($result);
    }
}

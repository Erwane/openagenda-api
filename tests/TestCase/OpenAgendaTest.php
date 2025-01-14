<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace OpenAgenda\Test\TestCase;

use GuzzleHttp\Psr7\Response;
use OpenAgenda\Client;
use OpenAgenda\Collection;
use OpenAgenda\Endpoint\Agenda;
use OpenAgenda\Endpoint\Event;
use OpenAgenda\Endpoint\Location;
use OpenAgenda\Entity\Agenda as AgendaEntity;
use OpenAgenda\Entity\Event as EventEntity;
use OpenAgenda\Entity\Location as LocationEntity;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\Utility\FileResource;
use OpenAgenda\Wrapper\HttpWrapper;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use stdClass;

/**
 * @uses   \OpenAgenda\OpenAgenda
 * @covers \OpenAgenda\OpenAgenda
 */
class OpenAgendaTest extends TestCase
{
    /**
     * @var \OpenAgenda\Wrapper\HttpWrapper
     */
    protected $wrapper;

    /**
     * @var \OpenAgenda\OpenAgenda
     */
    protected $oa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wrapper = $this->getMockForAbstractClass(
            HttpWrapper::class,
            [],
            '',
            false,
            true,
            true,
            ['head', 'get', 'post', 'patch', 'delete']
        );

        $this->oa = new OpenAgenda([
            'public_key' => 'publicKey',
            'wrapper' => $this->wrapper,
        ]);
    }

    public function testConstructMissingPublicKey()
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Missing `public_key`.');
        new OpenAgenda();
    }

    public function testConstructMissingHttpClient()
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Invalid or missing `wrapper`.');
        new OpenAgenda(['public_key' => 'publicKey']);
    }

    public function testConstructInvalidHttpClient()
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Invalid or missing `wrapper`.');
        new OpenAgenda(['public_key' => 'publicKey', 'http' => new stdClass()]);
    }

    public function testConstructInvalidCache()
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Cache should implement \Psr\SimpleCache\CacheInterface.');
        new OpenAgenda(['public_key' => 'publicKey', 'wrapper' => $this->wrapper, 'cache' => new stdClass()]);
    }

    public function testConstructInvalidLang()
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Invalid defaultLang.');
        new OpenAgenda(['public_key' => 'publicKey', 'wrapper' => $this->wrapper, 'defaultLang' => 'ac']);
    }

    public function testConstructInvalidUrl()
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Invalid project url.');
        new OpenAgenda(['public_key' => 'publicKey', 'wrapper' => $this->wrapper, 'projectUrl' => 'http://invalid']);
    }

    public function testConstruct()
    {
        $cache = $this->createMock(CacheInterface::class);
        $oa = new OpenAgenda([
            'public_key' => 'publicKey',
            'secret_key' => 'secretKey',
            'wrapper' => $this->wrapper,
            'cache' => $cache,
        ]);
        $this->assertInstanceOf(OpenAgenda::class, $oa);
    }

    public function testSetGetResetClient()
    {
        OpenAgenda::resetClient();
        $this->assertNull(OpenAgenda::getClient());
        OpenAgenda::setClient(new Client(['public_key' => 'publicKey', 'wrapper' => $this->wrapper]));
        $this->assertInstanceOf(Client::class, OpenAgenda::getClient());

        OpenAgenda::resetClient();
        $this->assertNull(OpenAgenda::getClient());
        new OpenAgenda([
            'public_key' => 'publicKey',
            'wrapper' => $this->wrapper,
        ]);
        $this->assertInstanceOf(Client::class, OpenAgenda::getClient());
    }

    public function testDefaultLang()
    {
        $this->assertEquals('fr', OpenAgenda::getDefaultLang());
        new OpenAgenda([
            'public_key' => 'publicKey',
            'wrapper' => $this->wrapper,
            'defaultLang' => 'en',
        ]);
        $this->assertEquals('en', OpenAgenda::getDefaultLang());
    }

    public function testProjectUrl()
    {
        new OpenAgenda([
            'public_key' => 'publicKey',
            'wrapper' => $this->wrapper,
            'projectUrl' => 'https://my-domain.com',
        ]);
        $this->assertEquals('https://my-domain.com', OpenAgenda::getProjectUrl());
        OpenAgenda::setProjectUrl(null);
        $this->assertNull(OpenAgenda::getProjectUrl());
    }

    public function testHead()
    {
        $this->markTestIncomplete();
    }

    public function testGet()
    {
        $this->markTestIncomplete();
    }

    public function testPost()
    {
        $this->markTestIncomplete();
    }

    public function testPatch()
    {
        $this->markTestIncomplete();
    }

    public function testDelete()
    {
        $this->markTestIncomplete();
    }

    public function testAgendas()
    {
        $payload = FileResource::instance($this)
            ->getContent('Response/agendas/agendas.json');
        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas',
                ['headers' => ['key' => 'publicKey']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $results = $this->oa->agendas();
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(AgendaEntity::class, $results->first());
    }

    public function testMyAgendas()
    {
        $payload = FileResource::instance($this)
            ->getContent('Response/agendas/mines.json');
        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/me/agendas',
                ['headers' => ['key' => 'publicKey']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $results = $this->oa->myAgendas();
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertInstanceOf(AgendaEntity::class, $results->first());
    }

    public function testAgenda()
    {
        $endpoint = $this->oa->agenda(['uid' => 12345, 'detailed' => true]);
        $this->assertInstanceOf(Agenda::class, $endpoint);
        $uri = $endpoint->getUri('get');
        $this->assertEquals('/v2/agendas/12345', $uri->getPath());
        $this->assertEquals('detailed=1', $uri->getQuery());
    }

    public function testLocations()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/locations.json');
        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas/123456/locations',
                ['headers' => ['key' => 'publicKey']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $results = $this->oa->locations(['agendaUid' => 123456]);
        $this->assertInstanceOf(LocationEntity::class, $results->first());
    }

    public function testGetLocation()
    {
        $endpoint = $this->oa->location(['uid' => 123, 'agendaUid' => 456]);
        $this->assertInstanceOf(Location::class, $endpoint);
        $uri = $endpoint->getUri('get');
        $this->assertEquals('/v2/agendas/456/locations/123', $uri->getPath());
    }

    public function testEvents()
    {
        $payload = FileResource::instance($this)->getContent('Response/events/events.json');
        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas/123456/events',
                ['headers' => ['key' => 'publicKey']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $results = $this->oa->events(['agendaUid' => 123456]);
        $this->assertInstanceOf(EventEntity::class, $results->first());
    }

    public function testEvent()
    {
        $endpoint = $this->oa->event(['uid' => 123, 'agendaUid' => 456]);
        $this->assertInstanceOf(Event::class, $endpoint);
        $uri = $endpoint->getUri('get');
        $this->assertEquals('/v2/agendas/456/events/123', $uri->getPath());
    }
}

<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace OpenAgenda\Test\TestCase;

use GuzzleHttp\Psr7\Response;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Location;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Test\Utility\FileResource;
use OpenAgenda\Wrapper\HttpWrapper;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Collection\Collection;
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
            'public_key' => 'testing',
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
        new OpenAgenda(['public_key' => 'testing']);
    }

    public function testConstructInvalidHttpClient()
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Invalid or missing `wrapper`.');
        new OpenAgenda(['public_key' => 'testing', 'http' => new stdClass()]);
    }

    public function testConstructInvalidCache()
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage('Cache should implement \Psr\SimpleCache\CacheInterface.');
        new OpenAgenda(['public_key' => 'testing', 'wrapper' => $this->wrapper, 'cache' => new stdClass()]);
    }

    public function testConstruct()
    {
        $cache = $this->createMock(CacheInterface::class);
        $oa = new OpenAgenda([
            'public_key' => 'testing',
            'private_key' => 'private',
            'wrapper' => $this->wrapper,
            'cache' => $cache,
        ]);
        $this->assertInstanceOf(OpenAgenda::class, $oa);
    }

    public function testGet()
    {
        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas',
                ['headers' => ['key' => 'testing']]
            )
            ->willReturn(new Response(200, [], ''));

        $agendas = $this->oa->get('/agendas');
        $this->assertInstanceOf(Collection::class, $agendas);
    }

    public function testGetAgendas()
    {
        $payload = FileResource::instance($this)
            ->getContent('Response/agendas/agendas.json');
        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas',
                ['headers' => ['key' => 'testing']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $agendas = $this->oa->agendas();
        $this->assertInstanceOf(Collection::class, $agendas);
        $this->assertInstanceOf(Agenda::class, $agendas->first());
    }

    public function testGetMyAgendas()
    {
        $payload = FileResource::instance($this)
            ->getContent('Response/agendas/mines.json');
        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/me/agendas',
                ['headers' => ['key' => 'testing']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $agendas = $this->oa->myAgendas();
        $this->assertInstanceOf(Collection::class, $agendas);
        $this->assertInstanceOf(Agenda::class, $agendas->first());
    }

    public function testGetAgenda()
    {
        $payload = FileResource::instance($this)
            ->getContent('Response/agendas/agenda.json');
        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas/12345',
                ['headers' => ['key' => 'testing']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $agenda = $this->oa->agenda(['id' => 12345]);
        $this->assertInstanceOf(Agenda::class, $agenda);
    }

    public function testGetLocations()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/locations.json');
        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas/123456/locations',
                ['headers' => ['key' => 'testing']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $locations = $this->oa->locations(['agenda_id' => 123456]);
        $this->assertInstanceOf(Location::class, $locations->first());
    }
}

<?php
declare(strict_types=1);

namespace OpenAgenda\Test\TestCase\Endpoint;

use OpenAgenda\Endpoint\Agendas;
use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\Endpoint\UnknownEndpointException;
use PHPUnit\Framework\TestCase;

/**
 * @uses   \OpenAgenda\Endpoint\EndpointFactory
 * @covers \OpenAgenda\Endpoint\EndpointFactory
 */
class EndpointFactoryTest extends TestCase
{
    public function testUnknownEndpoint()
    {
        $this->expectException(UnknownEndpointException::class);
        $this->expectExceptionMessage('Path "/testing" is not a valid endpoint.');

        EndpointFactory::make('/testing');
    }

    public static function dataMake(): array
    {
        return [
            ['/agendas', Agendas::class],
            ['/agenda', Agendas::class],
            ['/events', Event::class],
            ['/event', Event::class],
            ['/locations', Location::class],
            ['/location', Location::class],
        ];
    }

    /**
     * @dataProvider dataMake
     */
    public function testMake($path, $expectedClass)
    {
        $endpoint = EndpointFactory::make($path);
        $this->assertInstanceOf($expectedClass, $endpoint);
    }
}

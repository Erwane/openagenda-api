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

use OpenAgenda\Endpoint\Agenda;
use OpenAgenda\Endpoint\Agendas;
use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\Endpoint\Event;
use OpenAgenda\Endpoint\Events;
use OpenAgenda\Endpoint\Location;
use OpenAgenda\Endpoint\Locations;
use OpenAgenda\Endpoint\UnknownEndpointException;
use PHPUnit\Framework\TestCase;

/**
 * Endpoint\EndpointFactor tests
 *
 * @uses   \OpenAgenda\Endpoint\EndpointFactory
 * @covers \OpenAgenda\Endpoint\EndpointFactory
 */
class EndpointFactoryTest extends TestCase
{
    /** @covers \OpenAgenda\Endpoint\UnknownEndpointException */
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
            ['/agenda', Agenda::class],
            ['/events', Events::class],
            ['/event', Event::class],
            ['/locations', Locations::class],
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

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
namespace OpenAgenda\Test\TestCase\Functional;

use GuzzleHttp\Psr7\Response;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Test\FunctionalTestCase;
use OpenAgenda\Test\Utility\FileResource;

class AgendasFunctionalTestCase extends FunctionalTestCase
{
    /**
     * Test getting one agenda fluently
     */
    public function testSearchAgenda(): void
    {
        [$oa, $wrapper] = $this->oa();
        $payload = FileResource::instance($this)->getContent('Response/agendas/agendas.json');
        $wrapper->expects($this->once())
            ->method('get')
            ->willReturn(new Response(200, [], $payload));

        $agenda = $oa->agendas(['search' => 'My Agenda'])->first();
        $this->assertInstanceOf(Agenda::class, $agenda);
    }
}

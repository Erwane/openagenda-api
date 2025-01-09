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

class AgendasFunctionalTest extends FunctionalTestCase
{
    /**
     * Test get agenda from id
     */
    public function testGet(): void
    {
        [$oa, $wrapper] = $this->oa();
        $payload = FileResource::instance($this)->getContent('Response/agendas/agenda.json');
        $wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas/123?detailed=1',
                ['headers' => ['key' => 'publicKey']]
            )
            ->willReturn(new Response(200, [], $payload));

        $agenda = $oa->agenda(['id' => 123, 'detailed' => true])->get();
        $this->assertInstanceOf(Agenda::class, $agenda);
    }
}

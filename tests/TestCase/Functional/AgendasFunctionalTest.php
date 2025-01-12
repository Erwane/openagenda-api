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

use OpenAgenda\Entity\Agenda;
use OpenAgenda\Test\OpenAgendaTestCase;
use OpenAgenda\Test\Utility\FileResource;

/**
 * @coversNothing
 */
class AgendasFunctionalTest extends OpenAgendaTestCase
{
    /**
     * Search agendas
     * // Using OpenAgenda::agenda() method
     * $agendas = $oa->agendas([
     * 'size' => 5,
     * 'uid' => [12, 34, 56],
     * 'sort' => 'recentlyAddedEvents.desc',
     * ]);
     */
    public function testSearch(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/agendas/agendas.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];

        $this->assertClientCall(
            $client,
            $this->once(),
            'get',
            $payload,
            [
                'path' => '/v2/agendas',
                'query' => [
                    'size' => '2',
                    'fields' => ['summary', 'schema'],
                    'search' => 'my agenda',
                    'official' => '1',
                    'slug' => ['slug-1', 'slug-2'],
                    'uid' => ['123', '456'],
                    'network' => '123',
                    'sort' => 'createdAt.desc',
                ],
            ]
        );

        $agendas = $oa->agendas([
            'size' => 2,
            'fields' => ['summary', 'schema'],
            'search' => 'my agenda',
            'official' => true,
            'slug' => ['slug-1', 'slug-2'],
            'uid' => [123, 456],
            'network' => 123,
            'sort' => 'createdAt.desc',
        ]);
        $this->assertCount(2, $agendas);
        $this->assertInstanceOf(Agenda::class, $agendas->first());
    }

    /**
     * Get my agendas
     * $agendas = $oa->myAgendas(['size' => 2]);
     */
    public function testMines(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/agendas/mines.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];

        $this->assertClientCall(
            $client,
            $this->once(),
            'get',
            $payload,
            [
                'path' => '/v2/me/agendas',
                'query' => ['limit' => '2'],
            ]
        );

        $agendas = $oa->myAgendas(['limit' => 2]);
        $this->assertCount(1, $agendas);
        $this->assertInstanceOf(Agenda::class, $agendas->first());
    }

    /**
     * Test agenda exists
     * $exists = $oa->agenda(['uid' => 12345])->exists();
     */
    public function testExists(): void
    {
        [$oa, $client] = $this->oa();

        $this->assertClientCall(
            $client,
            $this->once(),
            'head',
            200,
            ['path' => '/v2/agendas/12345']
        );

        $exists = $oa->agenda(['uid' => 12345])->exists();
        $this->assertTrue($exists);
    }

    /**
     * Test get agenda from uid
     * $agenda = $oa->agenda(['uid' => 12345, 'detailed' => true])->get();
     */
    public function testGet(): void
    {
        [$oa, $client] = $this->oa();

        $payload = json_decode(FileResource::instance($this)->getContent('Response/agendas/agenda.json'), true);
        $payload += [
            '_status' => 200,
            '_success' => true,
        ];
        $this->assertClientCall($client,
            $this->once(),
            'get',
            $payload,
            [
                'path' => '/v2/agendas/12345',
                'query' => ['detailed' => '1'],
            ]
        );

        $agenda = $oa->agenda(['uid' => 12345, 'detailed' => true])->get();
        $this->assertInstanceOf(Agenda::class, $agenda);
    }
}

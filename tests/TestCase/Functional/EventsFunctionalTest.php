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

use OpenAgenda\Test\OpenAgendaTestCase;

class EventsFunctionalTest extends OpenAgendaTestCase
{
    public function testSearch(): void
    {
        // $events = $oa->events($params);
        $this->markTestIncomplete();
    }

    public function testSearchFromAgenda(): void
    {
        // $events = $agenda->events($params);
        $this->markTestIncomplete();
    }

    public function testExists(): void
    {
        // $event = $oa->event($params)->exists();
        $this->markTestIncomplete();
    }

    public function testExistsFromAgenda(): void
    {
        // $event = $agenda->event($options)->exists();
        $this->markTestIncomplete();
    }

    public function testGet(): void
    {
        // $event = $oa->event($params)->get();
        $this->markTestIncomplete();
    }

    public function testGetFromAgenda(): void
    {
        // $event = $agenda->event($options)->get();
        $this->markTestIncomplete();
    }

    public function testCreate(): void
    {
        // $event = $oa->post('/event', $data);
        $this->markTestIncomplete();
    }

    public function testCreateFromAgenda(): void
    {
        // $event = $agenda->event($data)->post();
        $this->markTestIncomplete();
    }

    public function testUpdate(): void
    {
        // $event = $oa->patch('/event', $data);
        $this->markTestIncomplete();
    }

    public function testUpdateFromEvent(): void
    {
        // $event = $event->update(true); // Full update
        $this->markTestIncomplete();
    }

    public function testDelete(): void
    {
        // $event = $oa->$event(['agendaUid' => 123, 'uid' => 456])->delete();
        $this->markTestIncomplete();
    }

    public function testDeleteFromEvent(): void
    {
        // $event = $event($eventUid)->delete();
        $this->markTestIncomplete();
    }
}

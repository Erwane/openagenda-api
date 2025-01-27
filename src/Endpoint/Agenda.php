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
namespace OpenAgenda\Endpoint;

use OpenAgenda\Entity\Agenda as AgendaEntity;
use OpenAgenda\OpenAgenda;

/**
 * Agenda endpoint
 */
class Agenda extends Endpoint
{
    protected array $_schema = [
        'detailed' => ['name' => 'detailed'],
    ];

    /**
     * @inheritDoc
     */
    protected function uriPath(string $method): string
    {
        return sprintf('/agendas/%d', $this->params['uid'] ?? 0);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function exists(): bool
    {
        $status = OpenAgenda::getClient()
            ->head($this->getUrl(__FUNCTION__));

        return $status >= 200 && $status < 300;
    }

    /**
     * {@inheritDoc}
     *
     * @return \OpenAgenda\Entity\Agenda|null
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function get(): ?AgendaEntity
    {
        $agenda = null;

        $response = OpenAgenda::getClient()
            ->get($this->getUrl(__FUNCTION__));

        if ($response['_success'] && !empty($response['uid'])) {
            $agenda = new AgendaEntity($response, ['markClean' => true]);
            $agenda->setNew(false);
        }

        return $agenda;
    }
}

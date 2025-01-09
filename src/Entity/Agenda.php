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
namespace OpenAgenda\Entity;

use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;
use Ramsey\Collection\Collection;

/**
 * @property int $id
 */
class Agenda extends Entity
{
    protected $_aliases = [
        'id' => ['field' => 'uid'],
        'title' => ['field' => 'title'],
        'description' => ['field' => 'description'],
        'slug' => ['field' => 'slug'],
        'url' => ['field' => 'url'],
        'image' => ['field' => 'image'],
        'official' => ['field' => 'official', 'type' => 'boolean'],
        'private' => ['field' => 'private', 'type' => 'boolean'],
        'indexed' => ['field' => 'indexed', 'type' => 'boolean'],
        'network_id' => ['field' => 'network'],
        'location_set_id' => ['field' => 'locationSet'],
        'created_at' => ['field' => 'createdAt', 'type' => 'DateTime'],
        'updated_at' => ['field' => 'updatedAt', 'type' => 'DateTime'],
    ];

    /**
     * A method require client sets.
     *
     * @return void
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function _requireClient(): void
    {
        if (!OpenAgenda::getClient()) {
            throw new OpenAgendaException('OpenAgenda object was not previously created or Client not set.');
        }
    }

    /**
     * Search locations for this agenda.
     *
     * @param array $params Endpoint params
     * @return \Ramsey\Collection\Collection
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function locations(array $params = []): Collection
    {
        $this->_requireClient();

        $params['agenda_id'] = $this->id;

        return EndpointFactory::make('/locations', $params)->get();
    }

    /**
     * Get Location endpoint with params.
     *
     * @param array $params Endpoint params
     * @return \OpenAgenda\Endpoint\Location|\OpenAgenda\Endpoint\Endpoint|
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function location(array $params = [])
    {
        $params['agenda_id'] = $this->id;

        return EndpointFactory::make('/location', $params);
    }
}

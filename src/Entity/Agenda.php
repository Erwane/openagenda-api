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

use OpenAgenda\Collection;
use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;

/**
 * @property int|null $uid
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $description
 * @property string|null $url
 * @property string|null $image
 * @property bool|null $official
 * @property bool|null $private
 * @property bool|null $indexed
 * @property int|null $networkUid
 * @property int|null $locationSetUid
 * @property \Cake\Chronos\Chronos|null $createdAt
 * @property \Cake\Chronos\Chronos|null $updatedAt
 */
class Agenda extends Entity
{
    protected $_schema = [
        'uid' => ['type' => 'int'],
        'title' => ['type' => 'string'],
        'slug' => ['type' => 'string'],
        'description' => ['type' => 'string'],
        'url' => ['type' => 'string'],
        'image' => ['type' => 'string'],
        'official' => ['type' => 'bool'],
        'private' => ['type' => 'bool'],
        'indexed' => ['type' => 'bool'],
        'networkUid' => ['type' => 'int'],
        'locationSetUid' => ['type' => 'int'],
        'createdAt' => ['type' => 'DateTime'],
        'updatedAt' => ['type' => 'DateTime'],
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
     * @return \OpenAgenda\Collection
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function locations(array $params = []): Collection
    {
        $this->_requireClient();

        $params['agendaUid'] = $this->uid;

        return EndpointFactory::make('/locations', $params)->get();
    }

    /**
     * Get Location endpoint with params.
     *
     * @param array $params Endpoint params
     * @return \OpenAgenda\Endpoint\Location|\OpenAgenda\Endpoint\Endpoint
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function location(array $params = [])
    {
        $params['agendaUid'] = $this->uid;

        return EndpointFactory::make('/location', $params);
    }

    /**
     * Search events for this agenda.
     *
     * @param array $params Endpoint params
     * @return \OpenAgenda\Collection
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function events(array $params = []): Collection
    {
        $this->_requireClient();

        $params['agendaUid'] = $this->uid;

        return EndpointFactory::make('/events', $params)->get();
    }

    /**
     * Get Event endpoint with params.
     *
     * @param array $params Endpoint params
     * @return \OpenAgenda\Endpoint\Event|\OpenAgenda\Endpoint\Endpoint
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function event(array $params = [])
    {
        $params['agendaUid'] = $this->uid;

        return EndpointFactory::make('/event', $params);
    }
}

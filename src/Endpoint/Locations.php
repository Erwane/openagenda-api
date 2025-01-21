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

use OpenAgenda\Collection;
use OpenAgenda\Entity\Location;
use OpenAgenda\OpenAgenda;

/**
 * Locations endpoint
 */
class Locations extends Endpoint
{
    protected $_schema = [
        'size' => [],
        'search' => [],
        'detailed' => [],
        'state' => [],
        'createdAt[lte]' => ['type' => 'datetime'],
        'createdAt[gte]' => ['type' => 'datetime'],
        'updatedAt[lte]' => ['type' => 'datetime'],
        'updatedAt[gte]' => ['type' => 'datetime'],
        'order' => [],
    ];

    /**
     * @inheritDoc
     */
    protected function uriPath(string $method): string
    {
        return sprintf('/agendas/%d/locations', $this->params['agendaUid'] ?? 0);
    }

    /**
     * Get locations.
     *
     * @return \OpenAgenda\Entity\Location[]|\OpenAgenda\Collection
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function get(): Collection
    {
        $response = OpenAgenda::getClient()
            ->get($this->getUrl(__FUNCTION__));

        $items = [];
        if ($response['_success'] && !empty($response['locations'])) {
            foreach ($response['locations'] as $item) {
                $entity = new Location($item, ['markClean' => true]);
                $items[] = $entity;
            }
        }

        return new Collection($items);
    }
}

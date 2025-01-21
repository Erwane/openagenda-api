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
use OpenAgenda\Entity\Event as EventEntity;
use OpenAgenda\OpenAgenda;

/**
 * Events endpoint
 */
class Events extends Endpoint
{
    protected array $_schema = [
        'detailed' => ['type' => 'bool'],
        'longDescriptionFormat' => [],
        'size' => ['type' => 'int'],
        'includeLabels' => ['type' => 'bool'],
        'includeFields' => ['type' => 'array'],
        'monolingual' => ['type' => 'string'],
        'removed' => ['type' => 'bool'],
        'city' => ['type' => 'array'],
        'department' => ['type' => 'array'],
        'region' => ['type' => 'string'],
        'timings[gte]' => ['type' => 'datetime'],
        'timings[lte]' => ['type' => 'datetime'],
        'updatedAt[gte]' => ['type' => 'datetime'],
        'updatedAt[lte]' => ['type' => 'datetime'],
        'search' => ['type' => 'string'],
        'uid' => ['type' => 'array'],
        'slug' => ['type' => 'string'],
        'featured' => ['type' => 'bool'],
        'relative' => ['type' => 'array'],
        'state' => ['type' => 'int'],
        'keyword' => ['type' => 'array'],
        'geo' => ['type' => 'array'],
        'locationUid' => ['type' => 'array'],
        'accessibility' => ['type' => 'array'],
        'status' => ['type' => 'array'],
        'sort' => ['type' => 'string'],
    ];

    /**
     * Check query geo.
     *
     * @param array|null $check Geo data
     * @return bool
     */
    public static function checkGeo(?array $check)
    {
        return empty($check) || (
                isset($check['northEast']['lat']) &&
                isset($check['northEast']['lng']) &&
                isset($check['southWest']['lat']) &&
                isset($check['southWest']['lng'])
            );
    }

    /**
     * @inheritDoc
     */
    protected function uriPath(string $method): string
    {
        return sprintf('/agendas/%d/events', $this->params['agendaUid'] ?? 0);
    }

    /**
     * Get locations.
     *
     * @return \OpenAgenda\Collection
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function get(): Collection
    {
        $response = OpenAgenda::getClient()
            ->get($this->getUrl(__FUNCTION__));

        $items = [];
        if ($response['_success'] && !empty($response['events'])) {
            foreach ($response['events'] as $item) {
                $entity = new EventEntity($item, ['markClean' => true]);
                $items[] = $entity;
            }
        }

        return new Collection($items);
    }
}

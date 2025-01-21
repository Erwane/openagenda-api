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

use OpenAgenda\Entity\Location as LocationEntity;
use OpenAgenda\OpenAgenda;

/**
 * Location endpoint
 */
class Location extends Endpoint
{
    /**
     * @inheritDoc
     */
    protected function uriPath(string $method): string
    {
        if ($method === 'create') {
            $path = sprintf('/agendas/%d/locations', $this->params['agendaUid'] ?? 0);
        } elseif (!empty($this->params['uid'])) {
            $path = sprintf('/agendas/%d/locations/%d', $this->params['agendaUid'] ?? 0, $this->params['uid']);
        } else {
            $path = sprintf(
                '/agendas/%d/locations/ext/%s',
                $this->params['agendaUid'] ?? 0,
                $this->params['extId'] ?? ''
            );
        }

        return $path;
    }

    /**
     * Validation check one of id or extId params is present.
     *
     * @param array $context Validation context.
     * @return bool
     */
    public static function presenceIdOrExtId(array $context = []): bool
    {
        $data = $context['data'];
        $isNew = $context['newRecord'] ?? true;

        return !$isNew && empty($data['uid']) && empty($data['extId']);
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
     * Get location.
     *
     * @return \OpenAgenda\Entity\Location|null
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function get(): ?LocationEntity
    {
        $response = OpenAgenda::getClient()
            ->get($this->getUrl(__FUNCTION__));

        return $this->_parseResponse($response);
    }

    /**
     * Create location
     *
     * @return \OpenAgenda\Entity\Location|null
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function create()
    {
        unset($this->params['uid']);

        $entity = new LocationEntity($this->params);

        $url = $this->getUrl(__FUNCTION__);

        $response = OpenAgenda::getClient()
            ->post($url, $entity->toOpenAgenda());

        return $this->_parseResponse($response, true);
    }

    /**
     * Patch location
     *
     * @return \OpenAgenda\Entity\Location|null
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function update()
    {
        $entity = new LocationEntity($this->params);
        $entity->setNew(false);

        // todo: no data to update, skip. Maybe an option ?

        $url = $this->getUrl(__FUNCTION__);
        $response = OpenAgenda::getClient()
            ->patch($url, $entity->toOpenAgenda());

        return $this->_parseResponse($response);
    }

    /**
     * Delete location
     *
     * @return \OpenAgenda\Entity\Location|null
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function delete()
    {
        $entity = new LocationEntity($this->params);
        $entity->setNew(false);

        $response = OpenAgenda::getClient()
            ->delete($this->getUrl(__FUNCTION__));

        return $this->_parseResponse($response);
    }

    /**
     * Parse client response.
     *
     * @param array $response Client response.
     * @param bool $isNew Set entity status
     * @return \OpenAgenda\Entity\Location|null
     */
    protected function _parseResponse(array $response, bool $isNew = false): ?LocationEntity
    {
        $entity = null;
        if ($response['_success'] && !empty($response['location'])) {
            $data = $response['location'];
            $data['agendaUid'] = $this->params['agendaUid'];
            $entity = new LocationEntity($data, ['markClean' => true]);
            $entity->setNew($isNew);
        }

        // todo handle errors and define what to return
        return $entity;
    }
}

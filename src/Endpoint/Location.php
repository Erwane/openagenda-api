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

use Cake\Validation\Validator;
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
    public function validationUriPath(Validator $validator): Validator
    {
        return $validator
            // agenda_id
            ->requirePresence('agenda_id')
            ->integer('agenda_id')

            // id
            ->requirePresence(
                'id',
                [$this, 'checkIdOrExtId'],
                'One of `id` or `ext_id` is required'
            )
            ->integer('id')

            // ext_id
            ->requirePresence(
                'ext_id',
                [$this, 'checkIdOrExtId'],
                'One of `id` or `ext_id` is required'
            )
            ->scalar('ext_id');
    }

    /**
     * @inheritDoc
     */
    public function uriPath(): string
    {
        if (!empty($this->params['id'])) {
            $path = sprintf('/agendas/%s/locations/%s', $this->params['agenda_id'], $this->params['id']);
        } else {
            $path = sprintf('/agendas/%s/locations/ext/%s', $this->params['agenda_id'], $this->params['ext_id']);
        }

        return $path;
    }

    /**
     * Validation check one of id or ext_id params is present.
     *
     * @param array $context Validation context.
     * @return bool
     */
    public function checkIdOrExtId(array $context = [])
    {
        $data = $context['data'];

        return empty($data['id']) && empty($data['ext_id']);
    }

    /**
     * Get location.
     *
     * @return \OpenAgenda\Entity\Location|null
     */
    public function get(): ?LocationEntity
    {
        $entity = null;

        $response = OpenAgenda::getClient()->get($this->getUri());

        if ($response['_success'] && !empty($response['location'])) {
            $entity = new LocationEntity($response['location'], ['markClean' => true]);
        }

        return $entity;
    }
}

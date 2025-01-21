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

use Cake\Validation\Validation;
use OpenAgenda\Validator;
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
    public function validationUriPath(Validator $validator): Validator
    {
        return parent::validationUriPath($validator)
            // agendaUid
            ->requirePresence('agendaUid')
            ->integer('agendaUid');
    }

    /**
     * Validation rules for Uri path GET.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriPathGet(Validator $validator): Validator
    {
        return $this->validationUriPath($validator)
            // limit
            ->allowEmptyString('size')
            ->numeric('size')
            ->greaterThanOrEqual('size', 1)

            // detailed
            ->allowEmptyString('detailed')
            ->boolean('detailed')

            // state
            ->allowEmptyString('state')
            ->boolean('state')

            // search
            ->allowEmptyString('search')
            ->scalar('search')

            // created lte/gte
            ->allowEmptyDateTime('createdAt[lte]')
            ->allowEmptyDateTime('createdAt[gte]')
            ->dateTime('createdAt[lte]', ['ymd', Validation::DATETIME_ISO8601])
            ->dateTime('createdAt[gte]', ['ymd', Validation::DATETIME_ISO8601])

            // updated lte/gte
            ->allowEmptyDateTime('updatedAt[lte]')
            ->allowEmptyDateTime('updatedAt[gte]')
            ->dateTime('updatedAt[lte]', ['ymd', Validation::DATETIME_ISO8601])
            ->dateTime('updatedAt[gte]', ['ymd', Validation::DATETIME_ISO8601])

            // order
            ->allowEmptyString('order')
            ->inList('order', [
                'name.asc',
                'name.desc',
                'createdAt.asc',
                'createdAt.desc',
            ]);
    }

    /**
     * @inheritDoc
     */
    protected function uriPath(string $method, bool $validate = true): string
    {
        parent::uriPath($method);

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

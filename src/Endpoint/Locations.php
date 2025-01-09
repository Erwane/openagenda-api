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
use Cake\Validation\Validator;
use OpenAgenda\Entity\Location;
use OpenAgenda\OpenAgenda;
use Ramsey\Collection\Collection;

/**
 * Locations endpoint
 */
class Locations extends Endpoint
{
    protected $queryFields = [
        'limit' => ['name' => 'size'],
        'search' => ['name' => 'search'],
        'detailed' => ['name' => 'detailed'],
        'state' => ['name' => 'state'],
        'created_lte' => ['name' => 'createdAt[lte]', 'type' => 'datetime'],
        'created_gte' => ['name' => 'createdAt[gte]', 'type' => 'datetime'],
        'updated_lte' => ['name' => 'updatedAt[lte]', 'type' => 'datetime'],
        'updated_gte' => ['name' => 'updatedAt[gte]', 'type' => 'datetime'],
        'sort' => [
            'name' => 'sort',
            'matching' => [
                'created_asc' => 'createdAt.asc',
                'created_desc' => 'createdAt.desc',
                'name_asc' => 'name.asc',
                'name_desc' => 'name.desc',
            ],
        ],
    ];

    /**
     * @inheritDoc
     */
    public function validationUriPath(Validator $validator): Validator
    {
        return parent::validationUriPath($validator)
            // agenda_id
            ->requirePresence('agenda_id')
            ->integer('agenda_id');
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
            ->allowEmptyString('limit')
            ->numeric('limit')
            ->greaterThanOrEqual('limit', 1)

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
            ->allowEmptyDateTime('created_lte')
            ->allowEmptyDateTime('created_gte')
            ->dateTime('created_lte', ['ymd', Validation::DATETIME_ISO8601])
            ->dateTime('created_gte', ['ymd', Validation::DATETIME_ISO8601])

            // updated lte/gte
            ->allowEmptyDateTime('updated_lte')
            ->allowEmptyDateTime('updated_gte')
            ->dateTime('updated_lte', ['ymd', Validation::DATETIME_ISO8601])
            ->dateTime('updated_gte', ['ymd', Validation::DATETIME_ISO8601])

            // sort
            ->allowEmptyString('sort')
            ->scalar('sort')
            ->inList('sort', [
                'name_asc',
                'name_desc',
                'created_asc',
                'created_desc',
            ]);
    }

    /**
     * @inheritDoc
     */
    public function uriPath(string $method, bool $validate = true): string
    {
        parent::uriPath($method);

        return sprintf('/agendas/%d/locations', $this->params['agenda_id'] ?? 0);
    }

    /**
     * Get locations.
     *
     * @return \OpenAgenda\Entity\Location[]|\Ramsey\Collection\Collection
     */
    public function get(): Collection
    {
        $collection = new Collection(Location::class);

        $response = OpenAgenda::getClient()->get($this->getUri(__FUNCTION__));

        if ($response['_success'] && !empty($response['locations'])) {
            foreach ($response['locations'] as $item) {
                $entity = new Location($item, ['markClean' => true]);
                $collection->add($entity);
            }
        }

        return $collection;
    }
}

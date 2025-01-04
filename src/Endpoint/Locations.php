<?php
declare(strict_types=1);

namespace OpenAgenda\Endpoint;

use Cake\Validation\Validation;
use Cake\Validation\Validator;
use InvalidArgumentException;
use League\Uri\Uri;
use OpenAgenda\Entity\Location;
use OpenAgenda\OpenAgenda;
use Ramsey\Collection\Collection;

/**
 * Locations endpoint
 */
class Locations extends Endpoint
{
    protected $fields = [
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
    public function validationDefault(Validator $validator)
    {
        return parent::validationDefault($validator)
            // agenda_id
            ->requirePresence('agenda_id')
            ->integer('agenda_id')

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
    public function getUri(): Uri
    {
        if (!Validation::numeric($this->params['agenda_id'] ?? null)) {
            throw new InvalidArgumentException('Missing valid `agenda_id` param.');
        }

        $path = sprintf('/agendas/%s/locations', $this->params['agenda_id']);

        $components = parse_url($this->baseUrl . $path);
        $query = $this->uriQuery();
        if ($query) {
            $components['query'] = http_build_query($query);
        }

        return Uri::createFromComponents($components);
    }

    /**
     * Get locations.
     *
     * @return \OpenAgenda\Entity\Location[]|\Ramsey\Collection\Collection
     */
    public function get(): Collection
    {
        $collection = new Collection(Location::class);

        $response = OpenAgenda::getClient()->get($this->getUri());

        if ($response['_success'] && !empty($response['locations'])) {
            foreach ($response['locations'] as $item) {
                $agenda = new Location($item, ['markClean' => true]);
                $collection->add($agenda);
            }
        }

        return $collection;
    }
}

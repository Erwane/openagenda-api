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

use Cake\Validation\Validation as CakeValidation;
use Cake\Validation\Validator;
use OpenAgenda\Collection;
use OpenAgenda\Entity\Event as EventEntity;
use OpenAgenda\OpenAgenda;
use OpenAgenda\Validation;

/**
 * Events endpoint
 */
class Events extends Endpoint
{
    protected $_schema = [
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
     * Validation rules for Uri query GET.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriQueryGet(Validator $validator): Validator
    {
        return parent::validationUriPath($validator)
            // detailed
            ->allowEmptyString('detailed')
            ->boolean('detailed')
            // longDescriptionFormat
            ->allowEmptyString('longDescriptionFormat')
            ->inList('longDescriptionFormat', [
                Event::DESC_FORMAT_MD,
                Event::DESC_FORMAT_HTML,
                Event::DESC_FORMAT_EMBEDS,
            ])
            // size
            ->allowEmptyString('size')
            ->greaterThanOrEqual('size', 1)
            ->lessThanOrEqual('size', 300)
            // page
            ->allowEmptyString('page')
            ->integer('page')
            // includeLabels
            ->allowEmptyString('includeLabels')
            ->boolean('includeLabels')
            // includeFields
            ->allowEmptyArray('includeFields')
            ->isArray('includeFields')
            // monolingual
            ->allowEmptyString('monolingual')
            ->add('monolingual', 'monolingual', [
                'rule' => [Validation::class, 'lang'],
            ])
            // removed
            ->allowEmptyString('removed')
            ->boolean('removed')
            // city
            ->allowEmptyArray('city')
            ->isArray('city')
            // department
            ->allowEmptyArray('department')
            ->isArray('department')
            // region
            ->allowEmptyString('region')
            ->scalar('region')
            // timings lte/gte
            ->allowEmptyDateTime('timings[lte]')
            ->allowEmptyDateTime('timings[gte]')
            ->dateTime('timings[lte]', ['ymd', CakeValidation::DATETIME_ISO8601])
            ->dateTime('timings[gte]', ['ymd', CakeValidation::DATETIME_ISO8601])
            // updatedAt lte/gte
            ->allowEmptyDateTime('updatedAt[lte]')
            ->allowEmptyDateTime('updatedAt[gte]')
            ->dateTime('updatedAt[lte]', ['ymd', CakeValidation::DATETIME_ISO8601])
            ->dateTime('updatedAt[gte]', ['ymd', CakeValidation::DATETIME_ISO8601])
            // search
            ->allowEmptyString('search')
            ->scalar('search')
            // uid
            ->allowEmptyArray('uid')
            ->isArray('uid')
            // slug
            ->allowEmptyString('slug')
            ->scalar('slug')
            // featured
            ->allowEmptyString('featured')
            ->boolean('featured')
            // relative
            ->allowEmptyArray('relative')
            ->multipleOptions('relative', ['passed', 'upcoming', 'current'])
            // state
            ->allowEmptyString('state')
            ->inList('state', [
                EventEntity::STATE_REFUSED,
                EventEntity::STATE_MODERATION,
                EventEntity::STATE_READY,
                EventEntity::STATE_PUBLISHED,
            ])
            // keyword
            ->allowEmptyArray('keyword')
            ->isArray('keyword')
            // geo
            ->allowEmptyString('geo')
            ->add('geo', 'geo', [
                'rule' => [$this, 'checkGeo'],
            ])
            // locationUid
            ->allowEmptyArray('locationUid')
            ->isArray('locationUid')
            // accessibility
            ->allowEmptyArray('accessibility')
            ->multipleOptions('accessibility', [
                EventEntity::ACCESS_HI,
                EventEntity::ACCESS_II,
                EventEntity::ACCESS_VI,
                EventEntity::ACCESS_MI,
                EventEntity::ACCESS_PI,
            ])
            // status
            ->allowEmptyArray('status')
            ->multipleOptions('status', [
                EventEntity::STATUS_SCHEDULED,
                EventEntity::STATUS_RESCHEDULED,
                EventEntity::STATUS_ONLINE,
                EventEntity::STATUS_DEFERRED,
                EventEntity::STATUS_FULL,
                EventEntity::STATUS_CANCELED,
            ]);
    }

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
    protected function uriPath(string $method, bool $validate = true): string
    {
        parent::uriPath($method, $validate);

        return sprintf('/agendas/%d/events', $this->params['agendaUid'] ?? 0);
    }

    /**
     * Get locations.
     *
     * @return \OpenAgenda\Entity\Event[]|\OpenAgenda\Collection
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function get(): Collection
    {
        $response = OpenAgenda::getClient()
            ->get($this->getUri(__FUNCTION__));

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

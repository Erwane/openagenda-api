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
            ->integer('agenda_id');
    }

    public function validationUriPathGet(Validator $validator): Validator
    {
        return $this->validationUriPath($validator)
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

    public function validationUriPathHead(Validator $validator): Validator
    {
        return $this->validationUriPathGet($validator);
    }

    public function validationPost(Validator $validator): Validator
    {
        return $this->validationUriPathGet($validator)
            // name
            ->requirePresence('name')
            ->scalar('name')
            ->maxLength('name', 100)
            // address
            ->requirePresence('address')
            ->scalar('address')
            ->maxLength('address', 255)
            // country
            ->requirePresence('country')
            ->scalar('country')
            ->lengthBetween('country', [2, 2])
            // state
            ->allowEmptyString('state')
            ->boolean('state')
            // description
            ->allowEmptyString('description') // todo multilingual
            // access
            ->allowEmptyString('access') // todo multilingual
            // website
            ->allowEmptyString('website')
            ->url('website')
            // email
            ->allowEmptyString('email')
            ->email('email')
            // phone
            ->allowEmptyString('phone')
            ->add('phone', 'phone', ['rule' => 'checkPhone'])
            // links
            ->allowEmptyArray('links')
            ->isArray('links')
            // image
            ->allowEmptyFile('image')
            // image_credits
            ->allowEmptyString('image_credits')
            ->scalar('image_credits')
            // region
            ->allowEmptyString('region')
            ->scalar('region')
            // department
            ->allowEmptyString('department')
            ->scalar('department')
            // district
            ->allowEmptyString('district')
            ->scalar('district')
            // city
            ->allowEmptyString('city')
            ->scalar('city')
            // postal_code
            ->allowEmptyString('postal_code')
            ->scalar('postal_code')
            // insee
            ->allowEmptyString('insee')
            ->scalar('insee')
            // latitude
            ->allowEmptyString('latitude')
            ->numeric('latitude')
            // longitude
            ->allowEmptyString('longitude')
            ->numeric('longitude')
            // timezone
            ->allowEmptyString('timezone')
            ->scalar('timezone')
            ;
    }

    /**
     * @inheritDoc
     */
    public function uriPath(string $method): string
    {
        parent::uriPath($method);

        if ($method === 'get' || $method === 'head') {
            if (!empty($this->params['id'])) {
                $path = sprintf('/agendas/%s/locations/%s', $this->params['agenda_id'], $this->params['id']);
            } else {
                $path = sprintf('/agendas/%s/locations/ext/%s', $this->params['agenda_id'], $this->params['ext_id']);
            }
        } else {
            $path = sprintf('/agendas/%s/locations', $this->params['agenda_id']);
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
     * Check location exists
     *
     * @return bool
     */
    public function head()
    {
        // TODO: Implement head() method.
    }

    /**
     * Get location.
     *
     * @return \OpenAgenda\Entity\Location|null
     */
    public function get(): ?LocationEntity
    {
        $entity = null;

        $response = OpenAgenda::getClient()
            ->get($this->getUri(__FUNCTION__));

        if ($response['_success'] && !empty($response['location'])) {
            $entity = new LocationEntity($response['location'], ['markClean' => true]);
        }

        return $entity;
    }

    /**
     * Create location
     *
     * @return bool
     */
    public function post()
    {
        unset($this->params['id']);
        $uri = $this->getUri(__FUNCTION__);

        $entity = new LocationEntity($this->params);

        $response = OpenAgenda::getClient()
            ->post($uri, $entity->toOpenAgenda());

        if ($response['_success'] && !empty($response['location'])) {
            $entity = new LocationEntity($response['location'], ['markClean' => true]);
        }

        return $entity;
        // TODO: Implement head() method.
    }

    /**
     * Patch location
     *
     * @return bool
     */
    public function patch()
    {
        // TODO: Implement head() method.
    }

    /**
     * Delete location
     *
     * @return bool
     */
    public function delete()
    {
        // TODO: Implement head() method.
    }
}

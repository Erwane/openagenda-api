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
use OpenAgenda\Validation;

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
            // id
            ->requirePresence(
                'uid',
                [$this, 'checkIdOrExtId'],
                'One of `id` or `extId` is required'
            )
            ->integer('uid')

            // extId
            ->requirePresence(
                'extId',
                [$this, 'checkIdOrExtId'],
                'One of `id` or `extId` is required'
            )
            ->scalar('extId');
    }

    /**
     * Validation rules for Uri path HEAD.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriPathExists(Validator $validator): Validator
    {
        return $this->validationUriPathGet($validator);
    }

    /**
     * Validation rules for Uri path DELETE.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriPathDelete(Validator $validator): Validator
    {
        return $this->validationUriPathGet($validator);
    }

    /**
     * Validation rules for POST/PATCH data.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationCreate(Validator $validator): Validator
    {
        return $this->validationUriPathGet($validator)
            // name
            ->requirePresence('name', 'create')
            ->scalar('name')
            ->maxLength('name', 100)
            // address
            ->requirePresence('address', 'create')
            ->scalar('address')
            ->maxLength('address', 255)
            // countryCode
            ->requirePresence('countryCode', 'create')
            ->scalar('countryCode')
            ->lengthBetween('countryCode', [2, 2])
            // state
            ->allowEmptyString('state')
            ->boolean('state')
            // description
            ->allowEmptyArray('description')
            ->add('description', 'multilingual', [
                'rule' => [[Validation::class, 'multilingual'], 5000],
            ])
            // access
            ->allowEmptyArray('access')
            ->add('access', 'multilingual', [
                'rule' => [[Validation::class, 'multilingual'], 1000],
            ])
            // website
            ->allowEmptyString('website')
            ->url('website')
            // email
            ->allowEmptyString('email')
            ->email('email')
            // phone
            ->allowEmptyString('phone')
            ->add('phone', 'phone', ['rule' => [Validation::class, 'phone']])
            // links
            ->allowEmptyArray('links')
            ->isArray('links')
            // image
            ->allowEmptyFile('image')
            // imageCredits
            ->allowEmptyString('imageCredits')
            ->scalar('imageCredits')
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
            // postalCode
            ->allowEmptyString('postalCode')
            ->scalar('postalCode')
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
            ->scalar('timezone');
    }

    /**
     * Validation rules for POST/PATCH data.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUpdate(Validator $validator)
    {
        return $this->validationCreate($validator);
    }

    /**
     * @inheritDoc
     */
    public function uriPath(string $method, bool $validate = true): string
    {
        parent::uriPath($method);

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
    public function checkIdOrExtId(array $context = [])
    {
        $data = $context['data'];

        return empty($data['uid']) && empty($data['extId']);
    }

    /**
     * @inheritDoc
     */
    public function exists(): bool
    {
        $status = OpenAgenda::getClient()
            ->head($this->getUri(__FUNCTION__));

        return $status >= 200 && $status < 300;
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
     * @return \OpenAgenda\Entity\Location|null
     */
    public function create()
    {
        unset($this->params['uid']);

        $entity = new LocationEntity($this->params);

        $uri = $this->getUri(__FUNCTION__);

        $response = OpenAgenda::getClient()
            ->post($uri, $entity->toOpenAgenda());

        $entity = $this->_parseResponse($response);

        return $entity;
    }

    /**
     * Patch location
     *
     * @return \OpenAgenda\Entity\Location|null
     */
    public function update()
    {
        $entity = new LocationEntity($this->params);
        $entity->setNew(false);
        $errors = $this->getValidator('update')->validate($this->params, $entity->isNew());

        if ($errors) {
            $this->throwException($errors);
        }
        $data = $entity->toOpenAgenda();

        // todo: no data to update, skip. Maybe an option ?

        $uri = $this->getUri(__FUNCTION__);
        $client = OpenAgenda::getClient();

        $response = $client->patch($uri, $data);

        return $this->_parseResponse($response);
    }

    /**
     * Delete location
     *
     * @return \OpenAgenda\Entity\Location|null
     */
    public function delete()
    {
        $entity = new LocationEntity($this->params);
        $entity->setNew(false);

        $response = OpenAgenda::getClient()
            ->delete($this->getUri(__FUNCTION__));

        return $this->_parseResponse($response);
    }

    /**
     * Parse client response.
     *
     * @param array $response Client response.
     * @return \OpenAgenda\Entity\Location|null
     */
    protected function _parseResponse(array $response): ?LocationEntity
    {
        $entity = null;
        if ($response['_success'] && !empty($response['location'])) {
            $entity = new LocationEntity($response['location'], ['markClean' => true]);
        }

        // todo handle errors and define what to return
        return $entity;
    }
}

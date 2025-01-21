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
     * Validate URI path contain id
     *
     * @param \Cake\Validation\Validator $validator Validator
     * @return \Cake\Validation\Validator
     */
    public function validationUriPathWithId(Validator $validator): Validator
    {
        return $this->validationUriPath($validator)
            // id
            ->requirePresence(
                'uid',
                [$this, 'presenceIdOrExtId'],
                'One of `id` or `extId` is required'
            )
            ->integer('uid')
            // extId
            ->requirePresence(
                'extId',
                [$this, 'presenceIdOrExtId'],
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
        return $this->validationUriPathWithId($validator);
    }

    /**
     * Validation rules for Uri path GET.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriPathGet(Validator $validator): Validator
    {
        return $this->validationUriPathWithId($validator);
    }

    /**
     * Validation rules for Uri path UPDATE.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriPathUpdate(Validator $validator): Validator
    {
        return $this->validationUriPathWithId($validator);
    }

    /**
     * Validation rules for Uri path DELETE.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriPathDelete(Validator $validator): Validator
    {
        return $this->validationUriPathWithId($validator);
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
            ->maxLength('name', 100)
            // address
            ->requirePresence('address', 'create')
            ->maxLength('address', 255)
            // countryCode
            ->requirePresence('countryCode', 'create')
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
            ->add('phone', 'phone', [
                'rule' => [[Validation::class, 'phone'], 'FR'],
            ])
            // links
            ->allowEmptyArray('links')
            ->isArray('links')
            // image
            ->allowEmptyFile('image')
            ->add('image', 'image', ['rule' => [[Validation::class, 'image'], 10]])
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
    protected function uriPath(string $method, bool $validate = true): string
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
     * @param bool $validate Validate data
     * @return \OpenAgenda\Entity\Location|null
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function create(bool $validate = true)
    {
        unset($this->params['uid']);

        $entity = new LocationEntity($this->params);

        if ($validate) {
            $errors = $this->getValidator('create')
                ->validate($entity->extract([], true));
            if ($errors) {
                $this->throwException($errors);
            }
        }

        $url = $this->getUrl(__FUNCTION__);

        $response = OpenAgenda::getClient()
            ->post($url, $entity->toOpenAgenda());

        return $this->_parseResponse($response, true);
    }

    /**
     * Patch location
     *
     * @param bool $validate Validate data
     * @return \OpenAgenda\Entity\Location|null
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function update(bool $validate = true)
    {
        $entity = new LocationEntity($this->params);
        $entity->setNew(false);

        if ($validate) {
            $errors = $this->getValidator('update')
                ->validate($entity->extract([], true), false);
            if ($errors) {
                $this->throwException($errors);
            }
        }

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

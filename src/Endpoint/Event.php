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
use OpenAgenda\Entity\Event as EventEntity;
use OpenAgenda\OpenAgenda;
use OpenAgenda\Validation;

/**
 * Event endpoint
 */
class Event extends Endpoint
{
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
            // id
            ->requirePresence('id', 'create')
            ->integer('id');
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
     * Validation rules for Uri query GET.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriQueryGet(Validator $validator): Validator
    {
        return $validator
            ->allowEmptyString('long_description_format')
            ->inList('long_description_format', ['markdown', 'HTML', 'HTMLWithEmbeds']);
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
            // title
            ->requirePresence('title', 'create')
            ->add('title', 'multilingual', [
                'rule' => [[Validation::class, 'multilingual'], 140],
            ])
            // description
            ->requirePresence('description', 'create')
            ->add('description', 'multilingual', [
                'rule' => [[Validation::class, 'multilingual'], 200],
            ])
            // longDescription
            ->allowEmptyArray('long_description')
            ->add('long_description', 'multilingual', [
                'rule' => [[Validation::class, 'multilingual'], 10000],
            ])
            // conditions
            ->allowEmptyArray('conditions')
            ->add('conditions', 'multilingual', [
                'rule' => [[Validation::class, 'multilingual'], 255],
            ])
            // keywords
            ->allowEmptyArray('keywords')
            ->add('keywords', 'multilingual', [
                'rule' => [[Validation::class, 'multilingual'], 255],
            ])
            // image
            ->allowEmptyFile('image')
            // image_credits
            ->allowEmptyString('image_credits')
            ->maxLength('image_credits', 255)
            // registration
            ->allowEmptyArray('registration')
            ->isArray('registration')
            // accessibility
            ->allowEmptyArray('accessibility')
            ->add('accessibility', 'accessibility', [
                'rule' => [[Validation::class, 'accessibility']],
            ])
            // timings
            ->requirePresence('timings', 'create')
            ->add('timings', 'timings', [
                'rule' => [[Validation::class, 'timings']],
            ])
            // timings
            ->allowEmptyArray('age')
            ->add('age', 'age', [
                'rule' => [[Validation::class, 'age']],
            ])
            // locationUid
            ->requirePresence('location_id', [$this, 'checkLocationId'])
            ->integer('location_id')
            // attendanceMode
            ->allowEmptyString('attendance_mode')
            ->inList('attendance_mode', [
                EventEntity::ATTENDANCE_OFFLINE,
                EventEntity::ATTENDANCE_ONLINE,
                EventEntity::ATTENDANCE_MIXED,
            ])
            // onlineAccessLink
            ->requirePresence('online_access_link', [$this, 'checkOnlineAccessLink'])
            ->url('online_access_link')
            // status
            ->allowEmptyString('status')
            ->inList('status', [
                EventEntity::STATUS_SCHEDULED,
                EventEntity::STATUS_RESCHEDULED,
                EventEntity::STATUS_ONLINE,
                EventEntity::STATUS_DEFERRED,
                EventEntity::STATUS_FULL,
                EventEntity::STATUS_CANCELED,
            ])
            // state
            ->allowEmptyString('state')
            ->inList('state', [
                EventEntity::STATE_REFUSED,
                EventEntity::STATE_MODERATION,
                EventEntity::STATE_READY,
                EventEntity::STATE_PUBLISHED,
            ]);
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
     * Location is required only for offline and mixed events.
     *
     * @param array $context Validation context
     * @return false|string
     */
    public function checkLocationId(array $context)
    {
        $data = $context['data'];

        $locationId = $data['locationUid'] ?? null;
        $mode = $data['attendanceMode'] ?? null;
        $modes = [EventEntity::ATTENDANCE_OFFLINE, EventEntity::ATTENDANCE_MIXED];

        if (in_array($mode, $modes) && !$locationId) {
            return 'locationUid required if attendanceMode is offline or mixed';
        }

        return false;
    }

    /**
     * Online access link only for online and mixed.
     *
     * @param array $context Validation context
     * @return false|string
     */
    public function checkOnlineAccessLink(array $context)
    {
        $data = $context['data'];

        $link = $data['onlineAccessLink'] ?? null;
        $mode = $data['attendanceMode'] ?? null;
        $modes = [EventEntity::ATTENDANCE_ONLINE, EventEntity::ATTENDANCE_MIXED];

        if (in_array($mode, $modes) && !$link) {
            return 'onlineAccessLink required if attendanceMode is online';
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function uriPath(string $method, bool $validate = true): string
    {
        parent::uriPath($method);

        if ($method === 'create') {
            $path = sprintf('/agendas/%d/events', $this->params['agenda_id'] ?? 0);
        } else {
            $path = sprintf('/agendas/%d/events/%d', $this->params['agenda_id'] ?? 0, $this->params['id']);
        }

        return $path;
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
    public function get(): ?EventEntity
    {
        $entity = null;

        $response = OpenAgenda::getClient()
            ->get($this->getUri(__FUNCTION__));

        if ($response['_success'] && !empty($response['event'])) {
            $entity = new EventEntity($response['event'], ['markClean' => true]);
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
        unset($this->params['id']);

        $entity = new EventEntity($this->params);

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
     * @throws \OpenAgenda\OpenAgendaException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function update()
    {
        $entity = new EventEntity($this->params);
        $entity->setNew(false);
        $errors = $this->getValidator('update')
            ->validate($this->params, $entity->isNew());

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
        $entity = new EventEntity($this->params);
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
    protected function _parseResponse(array $response): ?EventEntity
    {
        $entity = null;
        if ($response['_success'] && !empty($response['event'])) {
            $entity = new EventEntity($response['event'], ['markClean' => true]);
        }

        // todo handle errors and define what to return
        return $entity;
    }
}

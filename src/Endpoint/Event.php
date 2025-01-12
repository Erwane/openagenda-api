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

use Cake\Chronos\Chronos;
use Cake\Validation\Validator;
use Exception;
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
            ->requirePresence('uid')
            ->integer('uid');
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
     * Validation rules for Uri path POST.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriPathCreate(Validator $validator): Validator
    {
        return $this->validationUriPath($validator);
    }

    /**
     * Validation rules for Uri path UPDATE.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriPathUpdate(Validator $validator): Validator
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
            ->allowEmptyString('longDescriptionFormat')
            ->inList('longDescriptionFormat', ['markdown', 'HTML', 'HTMLWithEmbeds']);
    }

    /**
     * Validation rules for POST/PATCH data.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationCreate(Validator $validator): Validator
    {
        return $this->validationUriPath($validator)
            // id
            ->requirePresence('uid', 'update')
            ->integer('uid')
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
            ->allowEmptyArray('longDescription')
            ->add('longDescription', 'multilingual', [
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
            // imageCredits
            ->allowEmptyString('imageCredits')
            ->maxLength('imageCredits', 255)
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
            ->add('timings', 'timings', ['rule' => [$this, 'checkTimings']])
            // age
            ->allowEmptyArray('age')
            ->add('age', 'age', ['rule' => [$this, 'checkAge']])
            // locationUid
            ->requirePresence('locationUid', [$this, 'presenceLocationId'])
            ->integer('locationUid')
            // attendanceMode
            ->allowEmptyString('attendanceMode')
            ->inList('attendanceMode', [
                EventEntity::ATTENDANCE_OFFLINE,
                EventEntity::ATTENDANCE_ONLINE,
                EventEntity::ATTENDANCE_MIXED,
            ])
            // onlineAccessLink
            ->requirePresence('onlineAccessLink', [$this, 'presenceOnlineAccessLink'])
            ->url('onlineAccessLink')
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
     * Check event timings
     *
     * @param array $check Timings
     * @return bool
     */
    public static function checkTimings(array $check): bool
    {
        foreach ($check as $item) {
            if (!array_key_exists('begin', $item) || !array_key_exists('end', $item)) {
                return false;
            }

            try {
                $begin = Chronos::parse($item['begin']);
                $end = Chronos::parse($item['end']);
            } catch (Exception $e) {
                return false;
            }

            if ($begin->greaterThanOrEquals($end)) {
                return false;
            }
        }

        return !empty($check);
    }

    /**
     * Check event ages
     *
     * @param array $check Ages
     * @return bool
     */
    public static function checkAge(array $check): bool
    {
        if ($check) {
            if (!array_key_exists('min', $check) || !array_key_exists('max', $check)) {
                return false;
            }

            $min = $check['min'];
            $max = $check['max'];

            if ($min === null && $max === null) {
                return true;
            }

            if ($min > $max) {
                return false;
            }

            if ($min === null && $max !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Location is required only for offline and mixed events.
     *
     * @param array $context Validation context
     * @return false|string
     */
    public static function presenceLocationId(array $context)
    {
        $data = $context['data'];
        $isNew = $context['newRecord'] ?? true;

        $mode = $data['attendanceMode'] ?? null;
        $modes = [EventEntity::ATTENDANCE_OFFLINE, EventEntity::ATTENDANCE_MIXED];

        if (
            ($isNew && !$mode)
            || (in_array($mode, $modes))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Online access link only for online and mixed.
     *
     * @param array $context Validation context
     * @return false|string
     */
    public static function presenceOnlineAccessLink(array $context)
    {
        $data = $context['data'];

        $mode = $data['attendanceMode'] ?? null;

        return $mode === EventEntity::ATTENDANCE_MIXED || $mode === EventEntity::ATTENDANCE_ONLINE;
    }

    /**
     * @inheritDoc
     */
    protected function uriPath(string $method, bool $validate = true): string
    {
        parent::uriPath($method);

        if ($method === 'create') {
            $path = sprintf('/agendas/%d/events', $this->params['agendaUid'] ?? 0);
        } else {
            $path = sprintf('/agendas/%d/events/%d', $this->params['agendaUid'] ?? 0, $this->params['uid']);
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
     * Get event.
     *
     * @return \OpenAgenda\Entity\Event|null
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
     * Create event
     *
     * @param bool $validate Validate data
     * @return \OpenAgenda\Entity\Event|null
     * @throws \OpenAgenda\OpenAgendaException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function create(bool $validate = true)
    {
        unset($this->params['uid']);

        $entity = new EventEntity($this->params);

        if ($validate) {
            $errors = $this->getValidator('create')
                ->validate($entity->toArray());
            if ($errors) {
                $this->throwException($errors);
            }
        }

        $uri = $this->getUri(__FUNCTION__);

        $response = OpenAgenda::getClient()
            ->post($uri, $entity->toOpenAgenda());

        return $this->_parseResponse($response);
    }

    /**
     * Patch event
     *
     * @param bool $validate Validate data
     * @return \OpenAgenda\Entity\Event|null
     * @throws \OpenAgenda\OpenAgendaException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function update(bool $validate = true)
    {
        $entity = new EventEntity($this->params);
        $entity->setNew(false);

        if ($validate) {
            $errors = $this->getValidator('update')
                ->validate($entity->toArray(), false);
            if ($errors) {
                $this->throwException($errors);
            }
        }

        // todo: no data to update, skip. Maybe an option ?

        $uri = $this->getUri(__FUNCTION__);
        $response = OpenAgenda::getClient()
            ->patch($uri, $entity->toOpenAgenda());

        return $this->_parseResponse($response);
    }

    /**
     * Delete event
     *
     * @return \OpenAgenda\Entity\Event|null
     * @throws \OpenAgenda\OpenAgendaException
     * @throws \Psr\SimpleCache\InvalidArgumentException
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
     * @return \OpenAgenda\Entity\Event|null
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

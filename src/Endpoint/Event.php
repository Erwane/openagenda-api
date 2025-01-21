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

use OpenAgenda\DateTime;
use OpenAgenda\Entity\Event as EventEntity;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;
use OpenAgenda\Validation;
use OpenAgenda\Wrapper\HttpWrapperException;

/**
 * Event endpoint
 */
class Event extends Endpoint
{
    public const DESC_FORMAT_MD = 'markdown';
    public const DESC_FORMAT_HTML = 'HTML';
    public const DESC_FORMAT_EMBEDS = 'HTMLWithEmbeds';

    protected array $_schema = [
        'longDescriptionFormat' => [],
    ];

    /**
     * Check image and URL images too.
     *
     * @param string|resource $check Absolute path, url or file resource
     * @param float $max Maximum size in MegaBytes (MB)
     * @return bool
     * @throws \OpenAgenda\OpenAgendaException
     */
    public static function checkImage($check, float $max = 10): bool
    {
        $success = Validation::image($check, $max);
        if (!$success && Validation::url($check)) {
            if (!OpenAgenda::getClient()) {
                throw new OpenAgendaException('OpenAgenda object was not previously created or Client not set.');
            }
            $wrapper = OpenAgenda::getClient()->getWrapper();
            try {
                $response = $wrapper->head($check);

                $max = $max * 1024 * 1024;
                $type = $response->getHeaderLine('Content-Type');
                $size = $response->getHeaderLine('Content-Length');
                $success = $type && $size
                    && in_array($type, Validation::IMAGE_TYPES)
                    && $size <= $max;
            } catch (HttpWrapperException $e) {
                $success = false;
            }
        }

        return $success;
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
            if (!is_array($item)) {
                return false;
            }
            if (!array_key_exists('begin', $item) || !array_key_exists('end', $item)) {
                return false;
            }

            /**
             * @var \DateTimeInterface|string $begin
             * @var \DateTimeInterface|string $end
             */
            extract($item);
            if (is_string($begin)) {
                $begin = DateTime::parse($begin);
            }
            if (is_string($end)) {
                $end = DateTime::parse($end);
            }

            if (!$begin || !$end || $begin >= $end) {
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
     * Check event accessibility
     *
     * @param array $check Accessibility
     * @return bool
     */
    public static function checkAccessibility(array $check)
    {
        $success = true;
        if ($check) {
            $diff = array_diff_key($check, [
                EventEntity::ACCESS_HI => null,
                EventEntity::ACCESS_II => null,
                EventEntity::ACCESS_MI => null,
                EventEntity::ACCESS_PI => null,
                EventEntity::ACCESS_VI => null,
            ]);
            if ($diff) {
                $success = false;
            }
        }

        return $success;
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
    protected function uriPath(string $method): string
    {
        if ($method === 'create') {
            $path = sprintf('/agendas/%d/events', $this->params['agendaUid'] ?? 0);
        } else {
            $path = sprintf('/agendas/%d/events/%d', $this->params['agendaUid'] ?? 0, $this->params['uid']);
        }

        return $path;
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
     * Get event.
     *
     * @return \OpenAgenda\Entity\Event|null
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function get(): ?EventEntity
    {
        $response = OpenAgenda::getClient()
            ->get($this->getUrl(__FUNCTION__));

        return $this->_parseResponse($response);
    }

    /**
     * Create event
     *
     * @return \OpenAgenda\Entity\Event|null
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function create()
    {
        unset($this->params['uid']);

        $entity = new EventEntity($this->params);

        $url = $this->getUrl(__FUNCTION__);

        $response = OpenAgenda::getClient()
            ->post($url, $entity->toOpenAgenda());

        return $this->_parseResponse($response, true);
    }

    /**
     * Patch event
     *
     * @return \OpenAgenda\Entity\Event|null
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function update()
    {
        $entity = new EventEntity($this->params);
        $entity->setNew(false);

        // todo: no data to update, skip. Maybe an option ?

        $url = $this->getUrl(__FUNCTION__);
        $response = OpenAgenda::getClient()
            ->patch($url, $entity->toOpenAgenda());

        return $this->_parseResponse($response);
    }

    /**
     * Delete event
     *
     * @return \OpenAgenda\Entity\Event|null
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function delete()
    {
        $entity = new EventEntity($this->params);
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
     * @return \OpenAgenda\Entity\Event|null
     */
    protected function _parseResponse(array $response, bool $isNew = false): ?EventEntity
    {
        $entity = null;
        if ($response['_success'] && !empty($response['event'])) {
            $data = $response['event'];
            $entity = new EventEntity($data, ['markClean' => true]);
            $entity->setNew($isNew);
        }

        // todo handle errors and define what to return
        return $entity;
    }
}

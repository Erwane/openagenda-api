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
namespace OpenAgenda\Entity;

use Cake\Validation\Validation;
use DateTimeInterface;
use OpenAgenda\DateTime;
use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;

/**
 * @property int|null $uid
 * @property string|null $slug
 * @property int|null $state
 * @property int|null $status
 * @property bool|null $featured
 * @property int|null $agendaUid
 * @property \OpenAgenda\Entity\Agenda|null $agenda
 * @property int|null $locationUid
 * @property \OpenAgenda\Entity\Location|null $location
 * @property string|null $type
 * @property string|null $image
 * @property string|null $imageCredits
 * @property array<string, string>|null $title
 * @property array<string, string>|null $description
 * @property array<string, string>|null $longDescription
 * @property array<string, string>|null $keywords
 * @property array<string, string>|null $conditions
 * @property array<string, int|null>|null $age
 * @property array<string, string>|null $registration
 * @property array<string, bool>|null $accessibility
 * @property string[]|null $links
 * @property int|null $attendanceMode
 * @property string|null $onlineAccessLink
 * @property array|null $timings
 * @property string|null $timezone
 * @property \OpenAgenda\DateTime|null $createdAt
 * @property \OpenAgenda\DateTime|null $updatedAt
 */
class Event extends Entity
{
    public const STATE_REFUSED = -1; // Refused.
    public const STATE_MODERATION = 0; // To moderate.
    public const STATE_READY = 1; // Ready to published.
    public const STATE_PUBLISHED = 2; // Published. Event has public visibility.

    public const STATUS_SCHEDULED = 1; // Event scheduled (default).
    public const STATUS_RESCHEDULED = 2; // The time slots changed and event is re-scheduled.
    public const STATUS_ONLINE = 3; // The face-to-face event switched to an online event.
    public const STATUS_DEFERRED = 4; // Event deferred, new timings unknowns.
    public const STATUS_FULL = 5; // Event is full.
    public const STATUS_CANCELED = 6; // Event canceled and not re-scheduled.

    public const ACCESS_HI = 'hi'; // Hearing impairment.
    public const ACCESS_II = 'ii'; // Visual impairment.
    public const ACCESS_MI = 'mi'; // Motor impairment.
    public const ACCESS_PI = 'pi'; // Intellectual impairment.
    public const ACCESS_VI = 'vi'; // Psychic impairment.

    public const ATTENDANCE_OFFLINE = 1; // (default): Offline, face-to-face.
    public const ATTENDANCE_ONLINE = 2; // Online event, `onlineAccessLink` is required.
    public const ATTENDANCE_MIXED = 3; // Mixed.

    protected $_schema = [
        'uid' => [],
        'agendaUid' => [],
        'locationUid' => [],
        'slug' => [],
        'title' => ['required' => true],
        'description' => ['type' => 'multilingual', 'required' => true],
        'longDescription' => ['type' => 'multilingual'],
        'conditions' => ['type' => 'multilingual'],
        'keywords' => ['type' => 'multilingual'],
        'image' => ['type' => 'file'],
        'imageCredits' => [],
        'registration' => [],
        'accessibility' => [],
        'timings' => ['required' => true],
        'type' => [],
        'age' => [],
        'attendanceMode' => [],
        'onlineAccessLink' => [],
        'links' => [],
        'timezone' => [],
        'status' => [],
        'state' => [],
        'featured' => ['type' => 'bool'],
        'createdAt' => ['type' => 'datetime'],
        'updatedAt' => ['type' => 'datetime'],
        'originAgenda' => ['type' => Agenda::class],
        'location' => ['type' => Location::class],
    ];

    /**
     * A method require client sets.
     *
     * @return void
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function _requireClient(): void
    {
        if (!OpenAgenda::getClient()) {
            throw new OpenAgendaException('OpenAgenda object was not previously created or Client not set.');
        }
    }

    /**
     * Update this location.
     *
     * @return self
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function update(): self
    {
        $this->_requireClient();

        $data = $this->extract(array_keys($this->_schema), true);
        $data = array_filter($data, function ($value) {
            return $value !== null;
        });

        if ($this->uid) {
            $data['uid'] = $this->uid;
        }
        $data['agendaUid'] = $this->agendaUid;

        /** @uses \OpenAgenda\Endpoint\Event::update() */
        return EndpointFactory::make('/event', $data)
            ->update();
    }

    /**
     * Delete this location.
     *
     * @return self
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function delete(): self
    {
        $this->_requireClient();

        /** @uses \OpenAgenda\Endpoint\Event::delete() */
        return EndpointFactory::make('/event', $this->extract(['agendaUid', 'uid']))->delete();
    }

    /**
     * Get Agenda endpoint with params.
     *
     * @param array $params Endpoint params
     * @return \OpenAgenda\Endpoint\Location|\OpenAgenda\Endpoint\Endpoint
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function agenda(array $params = [])
    {
        $params['uid'] = $this->agendaUid;

        return EndpointFactory::make('/agenda', $params);
    }

    /**
     * Get Location endpoint with params.
     *
     * @param array $params Endpoint params
     * @return \OpenAgenda\Endpoint\Location|\OpenAgenda\Endpoint\Endpoint
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function location(array $params = [])
    {
        $params['agendaUid'] = $this->agendaUid;

        return EndpointFactory::make('/location', $params);
    }

    /**
     * Set timings.
     *
     * @param array $timings Array of event timings.
     * @return array
     */
    protected function _setTimings(array $timings): array
    {
        foreach ($timings as $key => $timing) {
            if (isset($timing['begin']) && is_string($timing['begin'])) {
                $timing['begin'] = DateTime::parse($timing['begin']);
            }
            if (isset($timing['end']) && is_string($timing['end'])) {
                $timing['end'] = DateTime::parse($timing['end']);
            }
            $timings[$key] = $timing;
        }

        return $timings;
    }

    /**
     * Set ages.
     *
     * @param array $value Array of event ages.
     * @return array
     */
    protected function _setAge(array $value): array
    {
        $min = null;
        $max = null;
        if (isset($value[0]) && isset($value[1])) {
            [$min, $max] = $value;
        } elseif (isset($value['min']) && isset($value['max'])) {
            extract($value);
        }

        return ['min' => $min, 'max' => $max];
    }

    /**
     * Set accessibility.
     *
     * @param array|string $value Array of event ages.
     * @return array
     */
    protected function _setAccessibility($value): array
    {
        $out = [
            self::ACCESS_HI => false,
            self::ACCESS_II => false,
            self::ACCESS_MI => false,
            self::ACCESS_PI => false,
            self::ACCESS_VI => false,
        ];

        if (is_string($value) && isset($out[$value])) {
            $out[$value] = true;
        } elseif (is_array($value)) {
            $stringKeys = array_filter(array_keys($value), function ($value) {
                return !is_int($value);
            });
            if (!$stringKeys) {
                $value = array_combine($value, array_fill(0, count($value), true));
            }
            $out = array_merge($out, $value);
        }

        return $out;
    }

    /**
     * Get agenda.
     *
     * @return \OpenAgenda\Entity\Agenda|null
     */
    protected function _getAgenda()
    {
        $agenda = null;
        if (isset($this->_fields['agenda'])) {
            $agenda = $this->_fields['agenda'];
        } elseif (isset($this->_fields['originAgenda'])) {
            $agenda = $this->_fields['originAgenda'];
        }

        return $agenda;
    }

    /**
     * Get agenda uid.
     *
     * @return int|null
     */
    protected function _getAgendaUid(): ?int
    {
        $value = isset($this->_fields['agendaUid']) ? (int)$this->_fields['agendaUid'] : null;
        if (!$value && $this->agenda instanceof Agenda) {
            $value = $this->agenda->uid;
        }

        return $value;
    }

    /**
     * Get location.
     *
     * @return \OpenAgenda\Entity\Location|null
     */
    protected function _getLocation(): ?Location
    {
        return $this->_fields['location'] ?? null;
    }

    /**
     * Get location uid.
     *
     * @return int|null
     */
    protected function _getLocationUid(): ?int
    {
        $value = $this->_fields['locationUid'] ?? null;
        if (!$value && $this->location instanceof Location) {
            $value = $this->location->uid;
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function toOpenAgenda(bool $onlyChanged = false): array
    {
        $data = parent::toOpenAgenda($onlyChanged);

        if (isset($data['location']) && $data['location'] instanceof Location) {
            $data['locationUid'] = $data['location']['uid'];
        }

        // Timings
        $timings = $data['timings'] ?? null;
        if (is_array($timings)) {
            foreach ($timings as &$timing) {
                if ($timing['begin'] instanceof DateTimeInterface) {
                    $timing['begin'] = $timing['begin']->toAtomString();
                }
                if ($timing['end'] instanceof DateTimeInterface) {
                    $timing['end'] = $timing['end']->toAtomString();
                }
            }
            $data['timings'] = $timings;
        }

        // image
        if (Validation::url($this->image)) {
            $data['image'] = ['url' => $this->image];
        }

        unset(
            $data['uid'],
            $data['agendaId'],
            $data['agendaUid'],
            $data['originAgenda'],
            $data['location']
        );

        return $data;
    }

    /**
     * Event titles is multilingual
     *
     * @param array|string|null $value Event title
     * @return array<string, string>|null
     */
    protected function _setTitle($value): ?array
    {
        return static::setMultilingual($value, true, 140);
    }

    /**
     * Event descriptions is multilingual
     *
     * @param array|string|null $value Event description
     * @return array<string, string>|null
     */
    protected function _setDescription($value): ?array
    {
        return static::setMultilingual($value, true, 200);
    }

    /**
     * Event long description is multilingual
     *
     * @param array|string|null $value Event long description
     * @return array<string, string>|null
     */
    protected function _setLongDescription($value): ?array
    {
        if (is_string($value)) {
            $value = [OpenAgenda::getDefaultLang() => $value];
        }

        if (is_array($value)) {
            foreach ($value as $lang => $text) {
                if (mb_strlen($text) > 10000) {
                    $text = mb_substr($text, 0, 9996) . ' ...';
                }

                $value[$lang] = $text;
            }
        }

        return $value;
    }

    /**
     * Event conditions is multilingual
     *
     * @param array|string|null $value Event conditions
     * @return array<string, string>|null
     */
    protected function _setConditions($value): ?array
    {
        return static::setMultilingual($value, true, 255);
    }

    /**
     * Event keywords is multilingual
     *
     * @param array|string|null $keywords Event keywords
     * @return array<string, string>|null
     */
    protected function _setKeywords($keywords): ?array
    {
        if (is_string($keywords)) {
            $keywords = [OpenAgenda::getDefaultLang() => [$keywords]];
        }

        if (is_array($keywords)) {
            // Has lang keys ?
            $hasLang = array_filter(array_keys($keywords), function ($value) {
                return !is_int($value);
            });
            if (!$hasLang) {
                $keywords = [OpenAgenda::getDefaultLang() => $keywords];
            }

            /** @var array<string, array> $keywords */
            foreach ($keywords as $lang => $items) {
                $items = array_map([$this, 'noHtml'], $items);

                $keywords[$lang] = $items;
            }
        }

        return $keywords;
    }
}

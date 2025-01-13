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

use Exception;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;

/**
 * @property int|null $uid
 * @property int|null $agendaUid
 * @property int|null $locationSetId
 * @property int|string|null $extId
 * @property string|null $slug
 * @property string|null $name
 * @property array|null $description
 * @property bool|null $state
 * @property array|null $access
 * @property string|null $address
 * @property string|null $city
 * @property string|null $postalCode
 * @property string|null $district
 * @property string|null $department
 * @property string|null $region
 * @property string|null $countryCode
 * @property string|null $insee
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $timezone
 * @property \Cake\Chronos\Chronos|null $createdAt
 * @property \Cake\Chronos\Chronos|null $updatedAt
 */
class Location extends Entity
{
    protected $_schema = [
        'uid' => [],
        'agendaUid' => [],
        'name' => ['required' => true],
        'address' => [], 'required' => true,
        'access' => [],
        'description' => [],
        'image' => ['type' => 'file'],
        'imageCredits' => [],
        'slug' => [],
        'location_set_id' => [],
        'city' => [],
        'department' => [],
        'region' => [],
        'postalCode' => [],
        'insee' => [],
        'countryCode' => ['required' => true],
        'district' => [],
        'latitude' => [],
        'longitude' => [],
        'createdAt' => ['type' => 'datetime'],
        'updatedAt' => ['type' => 'datetime'],
        'website' => [],
        'email' => [],
        'phone' => [],
        'links' => [],
        'timezone' => [],
        'extId' => [],
        'state' => ['type' => 'bool'],
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
    public function update(): Location
    {
        $this->_requireClient();

        $data = $this->extract(array_keys($this->_schema), true);
        $data = array_filter($data, function ($value) {
            return $value !== null;
        });

        if ($this->uid) {
            $data['uid'] = $this->uid;
        } elseif ($this->extId) {
            $data['extId'] = $this->extId;
        }
        $data['agendaUid'] = $this->agendaUid;

        /** @uses \OpenAgenda\Endpoint\Location::update() */
        return EndpointFactory::make('/location', $data)
            ->update();
    }

    /**
     * Delete this location.
     *
     * @return self
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function delete(): Location
    {
        $this->_requireClient();

        return EndpointFactory::make('/location', $this->toArray())->delete();
    }

    /**
     * Get Agenda endpoint with params.
     *
     * @param array $params Endpoint params
     * @return \OpenAgenda\Endpoint\Location|\OpenAgenda\Endpoint\Endpoint|
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function agenda(array $params = [])
    {
        $params['uid'] = $this->agendaUid;

        return EndpointFactory::make('/agenda', $params);
    }

    /**
     * Set multilingual description clean and truncate to 5000.
     *
     * @param string|array $value Descriptions
     * @return string[]
     */
    protected function _setDescription($value)
    {
        return static::setMultilingual($value, true, 5000);
    }

    /**
     * Set multilingual access clean and truncate to 5000.
     *
     * @param string|array $value Access
     * @return string[]
     */
    protected function _setAccess($value)
    {
        return static::setMultilingual($value, true, 1000);
    }

    /**
     * Country code is uppercase.
     *
     * @param string|null $value Country code.
     * @return string
     */
    protected function _setCountryCode(?string $value): ?string
    {
        if (is_string($value)) {
            $value = strtoupper($value);
        }

        return $value;
    }

    /**
     * Set latitude
     *
     * @param string|float $value coordinate
     * @return float
     */
    protected function _setLatitude($value)
    {
        return (float)$value;
    }

    /**
     * Set longitude
     *
     * @param string|float $value coordinate
     * @return float
     */
    protected function _setLongitude($value)
    {
        return (float)$value;
    }

    /**
     * Phone value
     *
     * @param string|null $value Phone value
     * @return string|null
     */
    protected function _setPhone(?string $value)
    {
        if ($value) {
            $instance = PhoneNumberUtil::getInstance();
            try {
                $phone = $instance->parse($value, OpenAgenda::getDefaultLang());
                $value = $instance->format($phone, PhoneNumberFormat::E164);
            } catch (Exception $e) {
                $value = null;
            }
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function toOpenAgenda(bool $onlyChanged = false): array
    {
        $data = parent::toOpenAgenda($onlyChanged);
        unset($data['uid'], $data['agendaUid']);

        return $data;
    }
}

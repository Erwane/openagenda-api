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

use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;

/**
 * @property int|null $id
 * @property int|null $agenda_id
 * @property int|null $location_set_id
 * @property int|string|null $ext_id
 * @property string|null $slug
 * @property string|null $name
 * @property array|null $description
 * @property bool|null $state
 * @property array|null $access
 * @property string|null $address
 * @property string|null $city
 * @property string|null $postal_code
 * @property string|null $district
 * @property string|null $department
 * @property string|null $region
 * @property string|null $country
 * @property string|null $insee
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $timezone
 * @property \Cake\Chronos\Chronos|null $created_at
 * @property \Cake\Chronos\Chronos|null $updated_at
 */
class Location extends Entity
{
    protected $_aliases = [
        'id' => ['field' => 'uid'],
        'agenda_id' => ['field' => 'agendaId'],
        'name' => ['field' => 'name', 'required' => true],
        'address' => ['field' => 'address'], 'required' => true,
        'access' => ['field' => 'access'],
        'description' => ['field' => 'description'],
        'image' => ['field' => 'image'],
        'image_credits' => ['field' => 'imageCredits'],
        'slug' => ['field' => 'slug'],
        'location_set_id' => ['field' => 'setUid'],
        'city' => ['field' => 'city'],
        'department' => ['field' => 'department'],
        'region' => ['field' => 'region'],
        'postal_code' => ['field' => 'postalCode'],
        'insee' => ['field' => 'insee'],
        'country' => ['field' => 'countryCode', 'required' => true],
        'district' => ['field' => 'district'],
        'latitude' => ['field' => 'latitude'],
        'longitude' => ['field' => 'longitude'],
        'created_at' => ['field' => 'createdAt', 'type' => 'DateTime'],
        'updated_at' => ['field' => 'updatedAt', 'type' => 'DateTime'],
        // website exists in doc but not in API payload
        // 'website' => ['field' => 'website'],
        'email' => ['field' => 'email'],
        'phone' => ['field' => 'phone'],
        'links' => ['field' => 'links'],
        'timezone' => ['field' => 'timezone'],
        'ext_id' => ['field' => 'extId'],
        'state' => ['field' => 'state', 'type' => 'boolean'],
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
     * @param array $params Endpoint params
     * @return self
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function update(): Location
    {
        $this->_requireClient();

        return EndpointFactory::make('/location', $this->toArray())->update();
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
        $params['id'] = $this->agenda_id;

        return EndpointFactory::make('/agenda', $params);
    }

    /**
     * Import data from openagenda
     *
     * @param array $locationData Location data
     * @return void
     * @deprecated Automatically sets from Entity::fromOpenAgenda()
     */
    public function import(array $locationData): void
    {
        $this->id = $locationData['uid'];
        $this->uid = $this->id;
        $this->latitude = $locationData['latitude'];
        $this->longitude = $locationData['longitude'];

        // Pricing
        if (isset($locationData['pricingInfo'])) {
            $this->pricing = $locationData['pricingInfo'];
        }

        // Dates
        if (!empty($locationData['dates']) && is_array($locationData['dates'])) {
            $this->dates = [];
            foreach ($locationData['dates'] as $date) {
                $this->dates[] = [
                    'date' => $date['date'],
                    'begin' => $date['timeStart'],
                    'end' => $date['timeEnd'],
                ];
            }
        }

        // remove dirty state
        foreach ($this->getDirty() as $key) {
            $this->setDirty($key, false);
        }
    }

    /**
     * Country code is uppercase.
     *
     * @param string|null $value Country code.
     * @return string
     */
    protected function _setCountry(?string $value): string
    {
        return strtoupper($value);
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
     * @inheritDoc
     */
    public function toOpenAgenda(bool $onlyChanged = false): array
    {
        $data = parent::toOpenAgenda($onlyChanged);
        unset($data['uid'], $data['agendaId']);

        return $data;
    }
}

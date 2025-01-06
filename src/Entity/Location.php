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

/**
 * @property int $id
 * @property int $uid
 * @property float $latitude
 * @property float $longitude
 * @property string $pricing
 * @property string $pricingInfo
 * @property array $dates
 */
class Location extends Entity
{
    protected $_aliases = [
        'id' => ['field' => 'uid'],
        'name' => ['field' => 'name'],
        'address' => ['field' => 'address'],
        'access' => ['field' => 'access'],
        'description' => ['field' => 'description'],
        'image' => ['field' => 'image'],
        'image_credits' => ['field' => 'imageCredits'],
        'slug' => ['field' => 'slug'],
        'set_id' => ['field' => 'setUid'],
        'city' => ['field' => 'city'],
        'department' => ['field' => 'department'],
        'region' => ['field' => 'region'],
        'postal_code' => ['field' => 'postalCode'],
        'insee' => ['field' => 'insee'],
        'country' => ['field' => 'countryCode'],
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
        'state' => ['field' => 'state'],
    ];

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
}

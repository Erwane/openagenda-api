<?php
namespace OpenAgenda\Entity;

use Exception;

class Location extends Entity
{
    public function import($locationData)
    {
        $this->uid = $locationData['uid'];
        $this->latitude = $locationData['latitude'];
        $this->longitude = $locationData['longitude'];

        // Pricing
        if (isset($locationData['pricingInfo'])) {
            $this->pricing = $locationData['pricingInfo'];
        }

        // remove dirty state
        foreach ($this->getDirty() as $key) {
            $this->setDirty($key, false);
        }
    }

    /**
     * mutator for latitude
     * @param string|float $value coordinate
     */
    protected function _setLatitude($value)
    {
        return (float)$value;
    }

    /**
     * mutator for longitude
     * @param string|float $value coordinate
     */
    protected function _setLongitude($value)
    {
        return (float)$value;
    }

    /**
     * @inheritDoc
     */
    public function toDatas()
    {
        $datas = [];

        return $datas;
    }

    /**
     * export location to array
     * @return array
     */
    public function toArray()
    {
        $data = [
            'uid' => $this->id,
        ];

        if (!is_null($this->pricingInfo)) {
            $data['pricingInfo'] = $this->pricingInfo;
        }

        return $data;
    }
}

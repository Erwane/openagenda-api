<?php
declare(strict_types=1);

namespace OpenAgenda\Entity;

/**
 * @property int $id
 * @property int $uid
 * @property float $latitude
 * @property float $longitude
 * @property string $pricing
 * @property string $pricingInfo
 * @property array $dates
 * @deprecated 2.0.4 Rewrited in ^3.0 Check README.
 */
class Location extends Entity
{
    public function import($locationData)
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
     * mutator for latitude
     *
     * @param string|float $value coordinate
     */
    protected function _setLatitude($value)
    {
        return (float)$value;
    }

    /**
     * mutator for longitude
     *
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
        return [];
    }

    /**
     * export location to array
     *
     * @return array
     * @noinspection PhpMissingParentCallCommonInspection
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

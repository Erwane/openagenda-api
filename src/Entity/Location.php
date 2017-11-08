<?php
namespace OpenAgenda\Entity;

use DateTime;
use Exception;

class Location extends Entity
{
    public function import($locationData)
    {
        $eventLocationDate = $locationData['dates'][0];

        $this->uid = $locationData['uid'];
        $this->latitude = $locationData['latitude'];
        $this->longitude = $locationData['longitude'];

        // Pricing
        if (isset($locationData['pricingInfo'])) {
            $this->pricing = $locationData['pricingInfo'];
        }

        $this->addDate([
            'date' => $eventLocationDate['date'],
            'start' => $eventLocationDate['timeStart'],
            'end' => $eventLocationDate['timeEnd'],
        ]);

        // remove dirty state
        foreach ($this->getDirty() as $key) {
            $this->setDirty($key, false);
        }
    }
    /**
     * add date with time to location
     * @param array $options date options
     */
    public function addDate($options)
    {
        if (!isset($options['date'])) {
            throw new Exception("missing date field", 1);
        }
        if (!isset($options['start'])) {
            throw new Exception("missing start field", 1);
        }
        if (!isset($options['end'])) {
            throw new Exception("missing end field", 1);
        }

        // use instance of DateTime only
        if (!($options['date'] instanceof DateTime)) {
            $options['date'] = new DateTime($options['date']);
        }
        if (!($options['start'] instanceof DateTime)) {
            $options['start'] = new DateTime($options['start']);
        }
        if (!($options['end'] instanceof DateTime)) {
            $options['end'] = new DateTime($options['end']);
        }

        $this->_properties['dates'][] = [
            'date' => $options['date']->format('Y-m-d'),
            'timeStart' => $options['start']->format('H:i'),
            'timeEnd' => $options['end']->format('H:i'),
        ];

        $this->setDirty('dates', true);

        return $this;
    }

    /**
     * set location pricing infos
     * @param string $value property value
     * @return self
     */
    public function setPricing($value, $lang = null)
    {
        $value = $this->_i18nValue($value, $lang);

        $this->setI18nProperty('pricingInfo', $value);

        return $this;
    }

    protected function _setLatitude($value)
    {
        return (float)$value;
    }

    protected function _setLongitude($value)
    {
        return (float)$value;
    }

    /**
     * export location to array
     * @return array
     */
    public function toArray()
    {
        $data = [
            'uid' => $this->id,
            'dates' => $this->dates,
        ];

        if (!is_null($this->pricingInfo)) {
            $data['pricingInfo'] = $this->pricingInfo;
        }

        return $data;
    }
}

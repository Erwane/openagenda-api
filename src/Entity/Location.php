<?php
namespace OpenAgenda\Entity;

use DateTime;
use Exception;

class Location
{
    use EntityTrait;

    /**
     * constructor
     * @param array $options array of datas
     */
    public function __construct($datas = [])
    {
        if (!empty($datas)) {
            foreach ($datas as $key => $value) {
                $method = 'set' . ucfirst($key);
                if (method_exists($this, $method)) {
                    $this->{$method}($value);
                }
            }
        }
    }

    /**
     * set location id
     * @param int $value location id
     */
    public function setId($value)
    {
        $this->_properties['uid'] = (int)$value;

        return $this;
    }

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
            'timeStart' => $options['start']->format('h:i'),
            'timeEnd' => $options['end']->format('h:i'),
        ];

        return $this;
    }

    /**
     * export location to array
     * @return array
     */
    public function toArray()
    {
        return [
            'uid' => $this->id,
            'dates' => $this->dates,
        ];
    }
}
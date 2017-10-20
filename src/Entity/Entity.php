<?php
namespace OpenAgenda\Entity;

class Entity
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
     * set event uid (or id)
     * @param int $value property value
     * @return self
     */
    public function setUid($value)
    {
        $this->_properties['uid'] = (int)$value;

        return $this;
    }

    /**
     * setUid alias
     * @param int $value property value
     */
    public function setId($value)
    {
        return $this->setUid($value);
    }
}
<?php
declare(strict_types=1);

namespace OpenAgenda\Entity;

use InvalidArgumentException;
use OpenAgenda\OpenAgendaException;

trait EntityTrait
{
    /**
     * Returns the value of a property by name
     *
     * @param string $property the name of the property to retrieve
     * @return mixed
     * @throws \InvalidArgumentException if an empty property name is passed
     */
    public function &get(string $property)
    {
        if (!strlen($property)) {
            throw new InvalidArgumentException('Cannot get an empty property');
        }

        $value = null;
        $method = static::_accessor($property, 'get');

        if (isset($this->_fields[$property])) {
            $value =& $this->_fields[$property];
        }

        if ($method) {
            $value = $this->{$method}($value);
        }

        return $value;
    }

    /**
     * set property with i18n datas
     *
     * @param string $name property name
     * @param array|\Traversable $object property value by lang
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setI18nProperty(string $name, $object)
    {
        if (!is_object($object) && !is_array($object)) {
            throw new OpenAgendaException('invalid property object');
        }

        foreach ($object as $lang => $value) {
            // create i18n array
            if (!isset($this->_fields[$name][$this->_getLang($lang)])) {
                $this->_fields[$name] = [$lang => null];
            }

            if ($value !== $this->_fields[$name][$this->_getLang($lang)]) {
                $this->setDirty($name . '.' . $lang, true);
            }

            $this->_fields[$name][$this->_getLang($lang)] = $value;
        }

        return $this;
    }
}

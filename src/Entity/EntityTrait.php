<?php
namespace OpenAgenda\Entity;

trait EntityTrait
{
    /**
     * Holds all properties and their values for this entity
     *
     * @var array
     */
    protected $_properties = [];

    /**
     * Holds a cached list of getters/setters per class
     *
     * @var array
     */
    protected static $_accessors = [];

    /**
     * Magic getter to access properties that have been set in this entity
     *
     * @param string $property Name of the property to access
     * @return mixed
     */
    public function &__get($property)
    {
        return $this->get($property);
    }

    protected function _getId()
    {
        if (isset($this->_properties['id'])) {
            return $this->_properties['id'];
        } elseif (isset($this->_properties['uid'])) {
            return $this->_properties['uid'];
        }

        return null;
    }

    /**
     * Returns the value of a property by name
     *
     * @param string $property the name of the property to retrieve
     * @return mixed
     * @throws \InvalidArgumentException if an empty property name is passed
     */
    public function &get($property)
    {
        if (!strlen((string)$property)) {
            throw new InvalidArgumentException('Cannot get an empty property');
        }

        $value = null;
        $method = static::_accessor($property, 'get');

        if (isset($this->_properties[$property])) {
            $value =& $this->_properties[$property];
        }

        if ($method) {
            $result = $this->{$method}($value);

            return $result;
        }

        return $value;
    }

    /**
     * Fetch accessor method name
     * Accessor methods (available or not) are cached in $_accessors
     *
     * @param string $property the field name to derive getter name from
     * @param string $type the accessor type ('get' or 'set')
     * @return string method name or empty string (no method available)
     */
    protected static function _accessor($property, $type)
    {
        $class = static::class;

        if (isset(static::$_accessors[$class][$type][$property])) {
            return static::$_accessors[$class][$type][$property];
        }

        if (!empty(static::$_accessors[$class])) {
            return static::$_accessors[$class][$type][$property] = '';
        }

        if ($class === 'OpenAgenda\Entity\Entity') {
            return '';
        }

        foreach (get_class_methods($class) as $method) {
            $prefix = substr($method, 1, 3);
            if ($method[0] !== '_' || ($prefix !== 'get' && $prefix !== 'set')) {
                continue;
            }

            $field = lcfirst(substr($method, 4));
            $titleField = ucfirst($field);
            static::$_accessors[$class][$prefix][$field] = $method;
            static::$_accessors[$class][$prefix][$titleField] = $method;
        }

        if (!isset(static::$_accessors[$class][$type][$property])) {
            static::$_accessors[$class][$type][$property] = '';
        }

        return static::$_accessors[$class][$type][$property];
    }
}
<?php
namespace OpenAgenda\Entity;

use Exception;

trait EntityTrait
{
    /**
     * Holds all properties and their values for this entity
     *
     * @var array
     */
    protected $_properties = [];

    /**
     * Holds a list of the properties that were modified or added after this object
     * was originally created.
     *
     * @var array
     */
    protected $_dirty = [];

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

    /**
     * Magic setter to add or edit a property in this entity
     *
     * @param string $property The name of the property to set
     * @param mixed $value The value to set to the property
     * @return void
     */
    public function __set($property, $value)
    {
        $this->set($property, $value);
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
     * Sets a single property inside this entity.
     *
     * ### Example:
     *
     * ```
     * $entity->set('name', 'Andrew');
     * ```
     *
     * It is also possible to mass-assign multiple properties to this entity
     * with one call by passing a hashed array as properties in the form of
     * property => value pairs
     *
     * ### Example:
     *
     * ```
     * $entity->set(['name' => 'andrew', 'id' => 1]);
     * echo $entity->name // prints andrew
     * echo $entity->id // prints 1
     * ```
     *
     * Some times it is handy to bypass setter functions in this entity when assigning
     * properties. You can achieve this by disabling the `setter` option using the
     * `$options` parameter:
     *
     * ```
     * $entity->set('name', 'Andrew', ['setter' => false]);
     * $entity->set(['name' => 'Andrew', 'id' => 1], ['setter' => false]);
     * ```
     *
     * Mass assignment should be treated carefully when accepting user input, by default
     * entities will guard all fields when properties are assigned in bulk. You can disable
     * the guarding for a single set call with the `guard` option:
     *
     * ```
     * $entity->set(['name' => 'Andrew', 'id' => 1], ['guard' => true]);
     * ```
     *
     * You do not need to use the guard option when assigning properties individually:
     *
     * ```
     * // No need to use the guard option.
     * $entity->set('name', 'Andrew');
     * ```
     *
     * @param string|array $property the name of property to set or a list of
     * properties with their respective values
     * @param mixed $value The value to set to the property or an array if the
     * first argument is also an array, in which case will be treated as $options
     * @param array $options options to be used for setting the property. Allowed option
     * keys are `setter` and `guard`
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set($property, $value = null, array $options = [])
    {
        if (is_string($property) && $property !== '') {
            $guard = false;
            $property = [$property => $value];
        } else {
            $guard = true;
            $options = (array)$value;
        }

        if (!is_array($property)) {
            throw new InvalidArgumentException('Cannot set an empty property');
        }
        $options += ['setter' => true];

        foreach ($property as $p => $value) {
            $this->setDirty($p, true);

            if (!$options['setter']) {
                $this->_properties[$p] = $value;

                continue;
            }

            $setter = static::_accessor($p, 'set');
            if ($setter) {
                $value = $this->{$setter}($value);
            }

            $this->_properties[$p] = $value;
        }

        return $this;
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

    /**
     * Sets the dirty status of a single property.
     *
     * @param string $property the field to set or check status for
     * @param bool $isDirty true means the property was changed, false means
     * it was not changed
     * @return $this
     */
    public function setDirty($property, $isDirty)
    {
        if ($isDirty === false) {
            unset($this->_dirty[$property]);

            return $this;
        }

        $this->_dirty[$property] = true;

        return $this;
    }

    /**
     * Gets the dirty properties keys
     *
     * @return array
     */
    public function getDirty()
    {
        return array_keys($this->_dirty);
    }

    /**
     * Gets the dirty properties with values
     *
     * @return array
     */
    public function getDirtyArray()
    {
        $result = [];

        foreach ($this->getDirty() as $key) {
            if (strpos($key, '.')) {
                $path = explode('.', $key);
            } else {
                $path = [$key];
            }

            $result[$path[0]] = $this->_properties[$path[0]];
        }

        return $result;
    }

    /**
     * Returns an array with the requested properties
     * stored in this entity, indexed by property name
     *
     * @param array $properties list of properties to be returned
     * @param bool $onlyDirty Return the requested property only if it is dirty
     * @return array
     */
    public function extract(array $properties, $onlyDirty = false)
    {
        $result = [];
        foreach ($properties as $property) {
            if (!$onlyDirty || $this->isDirty($property)) {
                $result[$property] = $this->get($property);
            }
        }

        return $result;
    }

    /**
     * set property with i18n datas
     * @param string $name   property name
     * @param object $object property value by lang
     * @throws Exception
     * @return self
     */
    public function setI18nProperty($name, $object)
    {
        if (!is_object($object) && !is_array($object)) {
            throw new Exception("invalid property object");
        }

        foreach ($object as $lang => $value) {
            // create i18n array
            if (!isset($this->_properties[$name][$this->_getLang($lang)])) {
                $this->_properties[$name] = [$lang => null];
            }

            if ($value !== $this->_properties[$name][$this->_getLang($lang)]) {
                $this->setDirty($name . '.' . $lang, true);
            }

            $this->_properties[$name][$this->_getLang($lang)] = $value;
        }

        return $this;
    }

    /**
     * Returns an array with all the properties that have been set
     * to this entity
     *
     * This method will recursively transform entities assigned to properties
     * into arrays as well.
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach (array_keys($this->_properties) as $property) {
            $value = $this->get($property);
            if (is_array($value)) {
                $result[$property] = [];
                foreach ($value as $k => $entity) {
                    if ($entity instanceof EntityInterface) {
                        $result[$property][$k] = $entity->toArray();
                    } else {
                        $result[$property][$k] = $entity;
                    }
                }
            } elseif ($value instanceof EntityInterface) {
                $result[$property] = $value->toArray();
            } else {
                $result[$property] = $value;
            }
        }

        return $result;
    }
}

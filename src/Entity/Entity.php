<?php
declare(strict_types=1);

namespace OpenAgenda\Entity;

use InvalidArgumentException;
use OpenAgenda\OpenAgendaException;

/**
 * Inspired by cakephp Entity.
 *
 * @copyright   Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link        https://cakephp.org CakePHP(tm) Project
 * @see         https://github.com/cakephp/cakephp/blob/5.x/src/ORM/Entity.php
 * @since       3.0.0
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
abstract class Entity
{
    /**
     * Holds all fields and their values for this entity
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * Holds a list of the properties that were modified or added after this object
     * was originally created.
     *
     * @var array
     */
    protected $_dirty = [];

    /**
     * @var bool
     */
    protected $_new = false;

    /**
     * constructor
     *
     * @param array $fields
     * @param array $options list of options to use when creating this entity
     */
    public function __construct(array $fields = [], array $options = [])
    {
        $options += [
            'useSetters' => true,
            'markClean' => false,
        ];

        if (!empty($fields)) {
            if ($options['markClean'] && !$options['useSetters']) {
                $this->_fields = $fields;

                return;
            }

            $this->set($fields, [
                'setter' => $options['useSetters'],
            ]);
        }
    }

    /**
     * @param $field
     * @param $value
     * @param array $options
     * @return $this
     */
    public function set($field, $value = null, array $options = [])
    {
        if (is_string($field) && $field !== '') {
            $field = [$field => $value];
        } else {
            $options = (array)$value;
        }

        if (!is_array($field)) {
            throw new InvalidArgumentException('Cannot set an empty field');
        }
        $options += ['setter' => true];

        foreach ($field as $name => $value) {
            //$this->setDirty($name, true);

            if ($options['setter']) {
                $setter = static::_accessor($name, 'set');
                if ($setter) {
                    $value = $this->{$setter}($value);
                }
            }

            $this->_fields[$name] = $value;
        }

        return $this;
    }

    /**
     * Fetch accessor method name
     * Accessor methods (available or not) are cached in $_accessors
     *
     * @param string $property the field name to derive getter name from
     * @param string $type the accessor type ('get' or 'set')
     * @return string method name or empty string (no method available)
     */
    protected static function _accessor(string $property, string $type): string
    {
        $class = static::class;
        if ($class === Entity::class) {
            return '';
        }

        $method = sprintf('_%s%s', $type, ucfirst($property));

        if (!method_exists($class, $method)) {
            $method = '';
        }

        return $method;
    }

    /**
     * Set id (uid)
     *
     * @param int|string $value Field value
     * @return int
     */
    protected function _setUid($value): int
    {
        return $this->_setId($value);
    }

    /**
     * Set id (uid)
     *
     * @param int|string $value Field value
     * @return int
     */
    protected function _setId($value): int
    {
        $value = (int)$value;
        $this->_fields['id'] = $value;

        return $value;
    }

    /**
     * set global event language
     *
     * @param string $value property value
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setLang(string $value)
    {
        if ($this->_isValidLanguage($value)) {
            $this->_fields['lang'] = $value;
        }

        return $this;
    }

    /**
     * setLang alias
     *
     * @param string $value property value
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setLanguage(string $value)
    {
        return $this->setLang($value);
    }

    /**
     * return true if valide language code
     *
     * @param string $lang code
     * @return bool
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function _isValidLanguage(string $lang)
    {
        if (!preg_match('/^(en|fr|es|de|it|ne|pt|ar|is)$/', $lang)) {
            throw new OpenAgendaException('invalid language code', 1);
        }

        return true;
    }

    /**
     * return lang $lang or default
     *
     * @param string|null $lang lang information
     * @return string lang
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function _getLang(?string $lang = null): string
    {
        // Throw exception if no lang set
        if ($lang === null && !isset($this->_fields['lang'])) {
            throw new OpenAgendaException('default lang not set. Use setLang()', 1);
        }

        // chech if lang is valid
        if ($lang !== null) {
            $this->_isValidLanguage($lang);
        }

        // return right lang
        return $lang ?? $this->_fields['lang'];
    }

    /**
     * @param string|mixed $data
     * @param string|null $lang
     * @return object|array|false
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function _i18nValue($data, ?string $lang = null)
    {
        if (is_string($data)) {
            $ary = [
                $this->_getLang($lang) => $data,
            ];

            $value = json_decode(json_encode($ary));
        } else {
            $value = $data;
        }

        return $value;
    }
}

<?php
declare(strict_types=1);

namespace OpenAgenda\Entity;

use Cake\Chronos\Chronos;
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
 * @property int $id
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

    protected $_aliases = [];

    /**
     * @var array
     */
    private $_oaToEntity = [];

    /**
     * @var array
     */
    private $_entityToOa = [];

    /**
     * constructor
     *
     * @param array $properties Entity properties
     * @param array $options list of options to use when creating this entity
     */
    public function __construct(array $properties = [], array $options = [])
    {
        $options += [
            'useSetters' => true,
            'markClean' => false,
        ];

        $this->_buildAliasesMaps();

        if (!empty($properties) && $options['markClean'] && !$options['useSetters']) {
            $this->_fields = $this->fromOpenAgenda($properties);

            return;
        }

        if (!empty($properties)) {
            $this->set($properties, [
                'setter' => $options['useSetters'],
            ]);
        }

        if ($options['markClean']) {
            $this->clean();
        }
    }

    /**
     * Build aliases maps (cache)
     *
     * @return void
     */
    protected function _buildAliasesMaps(): void
    {
        $this->_oaToEntity = [];
        $this->_entityToOa = [];
        foreach ($this->_aliases as $field => $info) {
            $this->_oaToEntity[$info['field']] = $field;
            $this->_entityToOa[$field] = $info['field'];
        }
    }

    /**
     * Import data from OpenAgenda to this entity.
     * Doing alias mapping from OpenAgenda to Entity
     *
     * @param array $data OpenAgenda data
     * @return array
     */
    protected function fromOpenAgenda($data): array
    {
        $out = [];
        foreach ($data as $name => $value) {
            $field = $this->_oaToEntity[$name] ?? $name;
            if (isset($this->_aliases[$field]['type'])) {
                switch ($this->_aliases[$field]['type']) {
                    case 'DateTime':
                        $value = Chronos::parse($value);
                        break;
                    case 'json':
                        if (is_string($value)) {
                            $value = json_decode($value, true);
                        }
                        break;
                }
            }

            $out[$field] = $value;
        }

        return $out;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_intersect_key($this->_fields, $this->_aliases);
    }

    /**
     * @return array
     */
    public function toOpenAgenda(): array
    {
        $out = [];
        foreach ($this->_fields as $field => $value) {
            $name = $this->_entityToOa[$field] ?? $field;
            if (isset($this->_aliases[$field]['type'])) {
                switch ($this->_aliases[$field]['type']) {
                    case 'DateTime':
                        $value = $value->format('Y-m-d\TH:i:s');
                        break;
                }
            }

            $out[$name] = $value;
        }

        return array_intersect_key($out, $this->_oaToEntity);
    }

    /**
     * Set property⋅ies value⋅s.
     *
     * @param string|array $fields Property name.
     * @param mixed $value Property value.
     * @param array $options Set options.
     * @return $this
     */
    public function set($fields, $value = null, array $options = [])
    {
        if (is_string($fields) && $fields !== '') {
            $fields = [$fields => $value];
        } else {
            $options = (array)$value;
        }

        if (!is_array($fields)) {
            throw new InvalidArgumentException('Cannot set an empty field');
        }
        $options += ['setter' => true];

        $fields = $this->fromOpenAgenda($fields);

        foreach ($fields as $name => $value) {
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
     * Set property/field.
     *
     * @param string $name Property (field) name.
     * @param mixed $value Property (field) value.
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Get property/field.
     *
     * @param string $name Property (field) name.
     * @return mixed|null
     */
    public function __get(string $name)
    {
        if ($name === '') {
            throw new InvalidArgumentException('Cannot get an empty field');
        }

        if (isset($this->_fields[$name])) {
            return $this->_fields[$name];
        }

        return null;
    }

    /**
     * Sets the entire entity as clean, which means that it will appear as
     * no fields being modified or added at all. This is an useful call
     * for an initial object hydration
     *
     * @return void
     */
    public function clean(): void
    {
        $this->_dirty = [];
    }

    /**
     * Set id (uid)
     *
     * @param int|string $value Field value
     * @return int
     */
    protected function _setId($value): int
    {
        return (int)$value;
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
     * Get i18n value.
     *
     * @param string|mixed $data I18n value.
     * @param string|null $lang Language code.
     * @return array
     */
    protected function _i18nValue($data, ?string $lang = null)
    {
        // todo: tests that
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

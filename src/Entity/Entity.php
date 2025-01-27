<?php
declare(strict_types=1);

namespace OpenAgenda\Entity;

use ArrayAccess;
use InvalidArgumentException;
use OpenAgenda\DateTime;
use OpenAgenda\OpenAgenda;
use OpenAgenda\Validation;

/**
 * Inspired by CakePHP Entity.
 *
 * @copyright   Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link        https://cakephp.org CakePHP(tm) Project
 * @see         https://github.com/cakephp/cakephp/blob/5.x/src/ORM/Entity.php
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 * @property int $uid
 */
abstract class Entity implements ArrayAccess
{
    /**
     * Holds all fields and their values for this entity
     *
     * @var array
     */
    protected array $_fields = [];

    /**
     * Holds a list of the properties that were modified or added after this object
     * was originally created.
     *
     * @var array
     */
    protected array $_dirty = [];

    /**
     * @var bool
     */
    protected bool $_new = true;

    protected array $_schema = [];

    /**
     * Entity required fields for post/patch.
     *
     * @var array
     */
    protected array $_required;

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
     * Set property/field.
     *
     * @param string $name Property (field) name.
     * @param mixed $value Property (field) value.
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Magic getter to access fields that have been set in this entity
     *
     * @param string $field Name of the field to access
     * @return mixed
     */
    public function &__get(string $field)
    {
        return $this->get($field);
    }

    /**
     * Build aliases maps (cache)
     *
     * @return void
     */
    protected function _buildAliasesMaps(): void
    {
        $this->_required = [];
        foreach ($this->_schema as $field => $info) {
            if (!empty($info['required'])) {
                $this->_required[$field] = true;
            }
        }
    }

    /**
     * Import data from OpenAgenda to this entity.
     * Doing alias mapping from OpenAgenda to Entity
     *
     * @param array $data OpenAgenda data
     * @return array
     */
    protected function fromOpenAgenda(array $data): array
    {
        foreach ($data as $field => &$value) {
            if (isset($this->_schema[$field]['type'])) {
                switch ($this->_schema[$field]['type']) {
                    case 'datetime':
                    case 'DateTime':
                        $value = DateTime::parse($value);
                        break;
                    case 'array':
                    case 'json':
                        if (is_string($value)) {
                            $value = json_decode($value, true);
                        }
                        break;
                    case 'bool':
                        $value = (bool)$value;
                        break;
                    case Agenda::class:
                    case Location::class:
                    case Event::class:
                        if (is_array($value)) {
                            $value = new $this->_schema[$field]['type']($value);
                        } elseif ($value instanceof Entity) {
                            $value = new $this->_schema[$field]['type']($value->toArray());
                        }
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->_schema as $field => $info) {
            $value = $this->get($field);
            if (is_array($value)) {
                $result[$field] = [];
                foreach ($value as $k => $entity) {
                    if ($entity instanceof Entity) {
                        $result[$field][$k] = $entity->toArray();
                    } else {
                        $result[$field][$k] = $entity;
                    }
                }
            } elseif ($value instanceof Entity) {
                $result[$field] = $value->toArray();
            } else {
                $result[$field] = $value;
            }
        }

        return $result;
    }

    /**
     * Return OpenAgenda fields array.
     *
     * @param bool $onlyChanged Only required and dirty fields if true.
     * @return array
     */
    public function toOpenAgenda(bool $onlyChanged = false): array
    {
        if ($onlyChanged) {
            $fields = array_intersect_key($this->_fields, $this->_required);
            $fields += array_intersect_key($this->_fields, $this->_dirty);
        } else {
            $fields = $this->_fields;
        }

        foreach ($fields as $field => &$value) {
            if (isset($this->_schema[$field]['type'])) {
                switch ($this->_schema[$field]['type']) {
                    case 'datetime':
                    case 'DateTime':
                        $value = $value->format('Y-m-d\TH:i:s');
                        break;
                    case 'bool':
                        $value = $value ? 1 : 0;
                        break;
                    case 'file':
                        if (is_string($value) && !Validation::url($value)) {
                            $value = fopen($value, 'r');
                        }
                        break;
                }
            }
        }

        return array_intersect_key($fields, $this->_schema);
    }

    /**
     * Implements isset($entity);
     *
     * @param string $offset The offset to check.
     * @return bool Success
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Implements $entity[$offset];
     *
     * @param string $offset The offset to get.
     * @return mixed
     */
    public function &offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Implements $entity[$offset] = $value;
     *
     * @param string $offset The offset to set.
     * @param mixed $value The value to set.
     * @return void
     */
    public function offsetSet($offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Implements unset($result[$offset])
     *
     * @param string $offset The offset to remove.
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->unset($offset);
    }

    /**
     * Set property⋅ies value⋅s.
     *
     * @param array|string $fields Property name.
     * @param mixed $value Property value.
     * @param array $options Set options.
     * @return $this
     */
    public function set(array|string $fields, mixed $value = null, array $options = [])
    {
        if (is_string($fields) && $fields !== '') {
            $fields = [$fields => $value];
        } else {
            $options = (array)$value;
        }

        $options += ['setter' => true];

        $fields = $this->fromOpenAgenda($fields);

        foreach ($fields as $name => $value) {
            if ($options['setter']) {
                $setter = static::_accessor($name, 'set');
                if ($setter) {
                    $value = $this->{$setter}($value);
                }
            }
            if (!isset($this->_fields[$name]) || $this->_fields[$name] !== $value) {
                $this->setDirty($name);
            }

            $this->_fields[$name] = $value;
        }

        return $this;
    }

    /**
     * Returns the value of a field by name
     *
     * @param string $field the name of the field to retrieve
     * @return mixed
     * @throws \InvalidArgumentException if an empty field name is passed
     */
    public function &get(string $field)
    {
        if ($field === '') {
            throw new InvalidArgumentException('Cannot get an empty field');
        }

        $value = null;

        $getter = static::_accessor($field, 'get');

        if ($getter) {
            $value = $this->$getter();
        } elseif (isset($this->_fields[$field])) {
            $value = &$this->_fields[$field];
        }

        return $value;
    }

    /**
     * Check field exists.
     *
     * @param array<string>|string $field The field or fields to check.
     * @return bool
     */
    public function has(array|string $field): bool
    {
        foreach ((array)$field as $prop) {
            if ($this->get($prop) === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Unset field
     *
     * @param array<string>|string $field The field to unset.
     * @return $this
     */
    public function unset(array|string $field)
    {
        $field = (array)$field;
        foreach ($field as $p) {
            unset($this->_fields[$p], $this->_dirty[$p]);
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

        $method = sprintf('_%s%s', $type, ucfirst($property));

        if (!method_exists($class, $method)) {
            $method = '';
        }

        return $method;
    }

    /**
     * Returns an array with the requested fields
     * stored in this entity, indexed by field name
     *
     * @param array<string> $fields list of fields to be returned
     * @param bool $onlyDirty Return the requested field only if it is dirty
     * @return array
     */
    public function extract(array $fields, bool $onlyDirty = false): array
    {
        if (empty($fields)) {
            $fields = array_keys($this->_schema);
        }
        $result = [];
        foreach ($fields as $field) {
            if (!$onlyDirty || $this->isDirty($field)) {
                $result[$field] = $this->get($field);
            }
        }

        return $result;
    }

    /**
     * Sets the dirty status of a single field.
     *
     * @param string $field the field to set or check status for
     * @param bool $isDirty true means the field was changed, false means
     * it was not changed. Defaults to true.
     * @return $this
     */
    public function setDirty(string $field, bool $isDirty = true)
    {
        $this->_dirty[$field] = true;

        return $this;
    }

    /**
     * Checks if the entity is dirty or if a single field of it is dirty.
     *
     * @param string|null $field The field to check the status for. Null for the whole entity.
     * @return bool Whether the field was changed or not
     */
    public function isDirty(?string $field = null): bool
    {
        return $field === null
            ? $this->_dirty !== []
            : isset($this->_dirty[$field]);
    }

    /**
     * Sets the entire entity as clean, which means that it will appear as
     * no fields being modified or added at all. This is a useful call
     * for an initial object hydration
     *
     * @return void
     */
    public function clean(): void
    {
        $this->_dirty = [];
    }

    /**
     * Set the status of this entity.
     * Using `true` means that the entity has not been persisted in the database,
     * `false` that it already is.
     *
     * @param bool $new Indicate whether this entity has been persisted.
     * @return $this
     */
    public function setNew(bool $new)
    {
        if ($new) {
            foreach ($this->_fields as $k => $p) {
                $this->_dirty[$k] = true;
            }
        }

        $this->_new = $new;

        return $this;
    }

    /**
     * Returns whether this entity has already been persisted.
     *
     * @return bool Whether the entity has been persisted.
     */
    public function isNew(): bool
    {
        return $this->_new;
    }

    /**
     * Set id (uid)
     *
     * @param int|string|null $value Field value
     * @return int|null
     */
    protected function _setUid(int|string|null $value): ?int
    {
        if ($value !== null) {
            $value = (int)$value;
        }

        return $value;
    }

    /**
     * Set entity image.
     *
     * @param string|resource|null $file Absolute path, resource file or null
     * @return string|resource|null
     */
    protected function _setImage($file)
    {
        $value = null;
        if ((is_string($file) && $file) || is_resource($file)) {
            $value = $file;
        } elseif ($file === false) {
            $value = false;
        }

        return $value;
    }

    /**
     * Remove html from sentence and keep only words.
     *
     * @param string|null $html HTML
     * @param bool $keepNewLine Should keep new lines
     * @return string
     */
    public static function noHtml(?string $html, bool $keepNewLine = true)
    {
        $text = strip_tags($html);

        // decode html
        $text = html_entity_decode($text, ENT_QUOTES);

        // remove new lines
        if (!$keepNewLine) {
            $text = str_replace(["\r", "\n"], ' ', $text);
        }

        // remove unused white spaces
        $text = preg_replace('/\pZ+/u', ' ', $text);

        return trim($text);
    }

    /**
     * Return a multilingual value, clean and truncate.
     *
     * @param string|array|null $value Input value
     * @param bool $clean Remove html tags and extra spaces.
     * @param int|null $truncate Truncate value to this length
     * @return array<string, string>
     */
    public static function setMultilingual(string|array|null $value, bool $clean, ?int $truncate = null)
    {
        if (is_string($value)) {
            $value = [OpenAgenda::getDefaultLang() => $value];
        }

        if (is_array($value)) {
            foreach ($value as $lang => $text) {
                if ($clean) {
                    $text = static::noHtml($text, false);
                }
                if ($truncate) {
                    if (mb_strlen($text) > $truncate) {
                        $text = mb_substr($text, 0, $truncate - 4) . ' ...';
                    }
                }

                $value[$lang] = $text;
            }
        }

        return $value;
    }
}

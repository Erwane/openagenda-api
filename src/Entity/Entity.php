<?php
declare(strict_types=1);

namespace OpenAgenda\Entity;

use ArrayAccess;
use Cake\Chronos\Chronos;
use Cake\Validation\Validation;
use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_TagTransform_Simple;
use InvalidArgumentException;
use League\HTMLToMarkdown\HtmlConverter;
use OpenAgenda\OpenAgenda;

/**
 * Inspired by CakePHP Entity.
 *
 * @copyright   Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link        https://cakephp.org CakePHP(tm) Project
 * @see         https://github.com/cakephp/cakephp/blob/5.x/src/ORM/Entity.php
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 * @property int $id
 */
abstract class Entity implements ArrayAccess
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
    protected $_new = true;

    protected $_schema = [];

    /**
     * Entity required fields for post/patch.
     *
     * @var array
     */
    protected $_required;

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
    public function __set(string $name, $value): void
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
                        $value = Chronos::parse($value);
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
        return array_intersect_key($this->_fields, $this->_schema);
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
                            $value = fopen($value, 'ro');
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
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Implements unset($result[$offset]);
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
    public function has($field): bool
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
    public function unset($field)
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
     * @param int|string $value Field value
     * @return int
     */
    protected function _setUid($value): int
    {
        return (int)$value;
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
     * clean description html tags
     *
     * @param string $value worse html ever
     * @return string
     */
    public static function cleanupHtml(string $value)
    {
        $projectUrl = OpenAgenda::getProjectUrl();
        $config = HTMLPurifier_Config::createDefault();

        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.AllowedElements', [
            'a', 'b', 'strong', 'i', 'em', 'u', 'p', 'img', 'hr', 'span',
            'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5',
        ]);
        $config->set('HTML.AllowedAttributes', ['a.href', 'a.target', 'img.src', 'img.alt', 'img.width', 'img.height']);
        $config->set('HTML.TargetBlank', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self']);
        $config->set('Attr.AllowedRel', ['noopener', 'noreferrer']);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
        $config->set('URI.AllowedSchemes', ['http', 'https']);
        if ($projectUrl) {
            $config->set('URI.Base', $projectUrl);
            $config->set('URI.MakeAbsolute', true);
        }

        // tag transformation
        $def = $config->getHTMLDefinition(true);
        $def->info_tag_transform['h1'] = new HTMLPurifier_TagTransform_Simple('h3');
        $def->info_tag_transform['h2'] = new HTMLPurifier_TagTransform_Simple('h3');

        $purifier = new HTMLPurifier($config);

        return trim($purifier->purify($value));
    }

    /**
     * html to markdown converter
     *
     * @param string $html html input
     * @return string
     */
    public static function htmlToMarkdown(string $html)
    {
        if ($html === strip_tags($html)) {
            return $html;
        }

        $converter = new HtmlConverter(['strip_tags' => true]);

        return $converter->convert($html);
    }

    /**
     * Return a multilingual value, clean and truncate.
     *
     * @param string|array $value Input value
     * @param bool $clean Remove html tags and extra spaces.
     * @param int|null $truncate Truncate value to this length
     * @return array<string, string>
     */
    public static function setMultilingual($value, bool $clean, ?int $truncate = null)
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

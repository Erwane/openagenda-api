<?php
declare(strict_types=1);

namespace OpenAgenda\Entity;

use OpenAgenda\OpenAgendaException;

/**
 * @deprecated 2.0.4 Rewrited in ^3.0 Check README.
 */
abstract class Entity
{
    use EntityTrait;

    /**
     * @var bool
     */
    protected $_new = false;

    /**
     * constructor
     *
     * @param array $properties
     * @param array $options list of options to use when creating this entity
     */
    public function __construct(array $properties = [], array $options = [])
    {
        $options += [
            'useSetters' => true,
            'markClean' => false,
        ];

        if (!empty($properties) && $options['markClean'] && !$options['useSetters']) {
            $this->_properties = $properties;

            return;
        }

        if (!empty($properties)) {
            $this->set($properties, [
                'setter' => $options['useSetters'],
            ]);
        }
    }

    /**
     * get entity datas for API
     *
     * @return array
     */
    abstract public function toDatas();

    /**
     * set event uid (or id)
     *
     * @param int|string $value property value
     * @return int
     */
    protected function _setUid($value)
    {
        $this->_properties['id'] = (int)$value;

        return $this->_properties['id'];
    }

    /**
     * setUid alias
     *
     * @param int|string $value property value
     */
    protected function _setId($value)
    {
        $this->_properties['uid'] = (int)$value;

        return $this->_properties['uid'];
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
            $this->_properties['lang'] = $value;
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
        if ($lang === null && !isset($this->_properties['lang'])) {
            throw new OpenAgendaException('default lang not set. Use setLang()', 1);
        }

        // chech if lang is valid
        if ($lang !== null) {
            $this->_isValidLanguage($lang);
        }

        // return right lang
        return $lang === null ? $this->_properties['lang'] : $lang;
    }

    /**
     * @param string|mixed $data
     * @param string|null $lang
     * @return object|array|false
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function _i18nValue($data, string $lang = null)
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

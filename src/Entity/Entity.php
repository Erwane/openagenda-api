<?php
namespace OpenAgenda\Entity;

use Exception;

abstract class Entity
{
    use EntityTrait;

    /**
     * constructor
     * @param array $options array of datas
     * @param array $options list of options to use when creating this entity
     */
    public function __construct($properties = [], $options = [])
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
     * @return array
     */
    public abstract function toDatas();

    /**
     * set event uid (or id)
     * @param int $value property value
     * @return self
     */
    protected function _setUid($value)
    {
        $this->_properties['id'] = (int)$value;

        return $this->_properties['id'];
    }

    /**
     * setUid alias
     * @param int $value property value
     */
    protected function _setId($value)
    {
        $this->_properties['uid'] = (int)$value;

        return $this->_properties['uid'];
    }

    /**
     * set global event language
     * @param string $value property value
     * @return self
     */
    public function setLang($value)
    {
        $value = (string)$value;

        if ($this->_isValidLanguage($value)) {
            $this->_properties['lang'] = $value;
        }

        return $this;
    }

    /**
     * setLang alias
     * @param string $value property value
     * @return self
     */
    public function setLanguage($value)
    {
        return $this->setLang($value);
    }

    /**
     * return true if valide language code
     * @param  string  $lang code
     * @return bool
     * @throws Exception if invalid
     */
    protected function _isValidLanguage($lang)
    {
        if (!preg_match('/^(en|fr|es|de|it|ne|pt|ar|is)$/', $lang)) {
            throw new Exception("invalid language code", 1);
        }

        return true;
    }

    /**
     * return lang $lang or default
     * @param string $lang lang information
     * @return string lang
     * @throws Exception
     */
    protected function _getLang($lang)
    {
        // Throw exception if no lang set
        if (is_null($lang) && is_null($this->_properties['lang'])) {
            throw new Exception("default lang not set. Use setLang()", 1);
        }

        // chech if lang is valid
        if (!is_null($lang)) {
            $this->_isValidLanguage($lang);
        }

        // return right lang
        return is_null($lang) ? $this->_properties['lang'] : (string)$lang;
    }

    protected function _i18nValue($data, $lang = null)
    {
        if (is_string($data)) {
            $ary = [
                $this->_getLang($lang) => $data
            ];

            $value = json_decode(json_encode($ary));
        } else {
            $value = $data;
        }

        return $value;
    }
}

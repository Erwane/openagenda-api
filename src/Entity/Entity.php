<?php
namespace OpenAgenda\Entity;

use Exception;

class Entity
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
        if (is_null($lang) && is_null($this->lang)) {
            throw new Exception("default lang not set. Use setLang()", 1);
        }

        // chech if lang is valid
        if (!is_null($lang)) {
            $this->_isValidLanguage($lang);
        }

        // return right lang
        return is_null($lang) ? $this->lang : (string)$lang;
    }

    protected function _i18nValue($value, $lang = null)
    {
        if (is_array($value)) {
            $value = json_decode(json_encode($value));
        } elseif (is_string($value) && $this->_isValidLanguage($lang)) {
            $value = json_decode(json_encode([
                $lang => $value
            ]));
        }

        return $value;
    }
}

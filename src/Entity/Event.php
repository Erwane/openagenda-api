<?php
namespace OpenAgenda\Entity;

use Exception;
use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_TagTransform_Simple;
use League\HTMLToMarkdown\HtmlConverter;

class Event extends Entity
{
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

    /**
     * set event title
     * @param bool $value property value
     * @return self
     */
    public function setState($value)
    {
        $this->_properties['state'] = (bool)$value;

        return $this;
    }

    /**
     * set event title
     * @param string $value property value
     * @param string $lang lang information
     * @return self
     */
    public function setTitle($value, $lang = null)
    {
        $this->_properties['title'][$this->_getLang($lang)] = $value;

        return $this;
    }

    /**
     * set event keywords (old tags)
     * @param string $value property value
     * @param string $lang lang information
     * @return self
     */
    public function setKeywords($keywords, $lang = null)
    {
        if (!is_array($keywords)) {
            $keywords = array_map('trim', explode(',', $keywords));
        }

        $this->_properties['keywords'][$this->_getLang($lang)] = implode(', ', $keywords);

        return $this;
    }

    /**
     * set event description. 200 max length and no html
     * @param string $value property value
     * @param string $lang lang information
     * @return self
     */
    public function setDescription($value, $lang = null)
    {
        // remove tags
        $text = strip_tags($value);

        // decode html
        $text = html_entity_decode($text, ENT_QUOTES);

        // remove new lines
        $text = preg_replace(['/\\r?\\n/', '/^\\r?\\n$/', '/^$/'], ' ', $text);

        // remove unused white spaces
        $text = preg_replace('/[\pZ\pC]+/u', ' ', $text);

        $this->_properties['description'][$this->_getLang($lang)] = mb_substr($text, 0, 190) . ' ...';

        return $this;
    }

    /**
     * set free text
     * @param string $text property value
     * @param string $lang lang information
     * @return self
     */
    public function setFreeText($text, $lang = null)
    {
        $text = $this->_cleanHtml($text);

        $text = $this->_toMarkDown($text);

        $this->_properties['freeText'][$this->_getLang($lang)] = mb_substr($text, 0, 5800);

        return $this;
    }

    public function setLocation(Location $location)
    {
        $this->_properties['locations'][] = $location->toArray();

        return $this;
    }

    /**
     * set event picture
     * @param string $file absolute path
     * @return self
     */
    public function setPicture($file)
    {
        if (!file_exists($file)) {
            throw new Exception("picture file does not exists", 1);
        }

        $this->_properties['image'] = fopen($file, 'r');

        return $this;
    }

    /**
     * set picture alias
     * @param string $file absolute path
     * @return self
     */
    public function setImage($file)
    {
        return $this->setPicture($file);
    }

    public function toArray()
    {
        // Tests
        foreach (['title', 'description', 'freeText', 'locations'] as $key) {
            if (is_null($this->{$key}) || $this->{$key} == '') {
                throw new Exception("missing event {$key}", 1);
            }
        }
        // No default language ?
        if (is_null($this->lang) && !is_array($this->title)) {
            throw new Exception("missing event global lang", 1);
        }

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'freeText' => $this->freeText,
            'locations' => $this->locations,
        ];

        if (!is_null($this->keywords)) {
            $data['tags'] = $this->keywords;
        }

        $return = [
            'publish' => $this->state,
            'data' => json_encode($data),
        ];

        // picture
        if (!is_null($this->image)) {
            $return[] = ['name' => 'image', 'contents' => $this->image, 'Content-type' => 'multipart/form-data'];
        }

        return $return;
    }

    /**
     * clean description html tags
     * @param html $value worse html ever
     */
    protected function _cleanHtml($value)
    {
        $config = HTMLPurifier_Config::createDefault();

        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.AllowedElements', ['a', 'b', 'strong', 'i', 'em', 'u', 'p', 'img', 'hr', 'ul', 'ol', 'li', 'span', 'h1', 'h2', 'h3', 'h4', 'h5']);
        $config->set('HTML.AllowedAttributes', ['a.href', 'a.target', 'img.src', 'img.alt', 'img.width', 'img.height']);
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self']);
        $config->set('Attr.AllowedRel', []);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
        $config->set('URI.AllowedSchemes', ['http', 'https']);

        // tag transformation
        $def = $config->getHTMLDefinition(true);
        $def->info_tag_transform['h1'] = new HTMLPurifier_TagTransform_Simple('h3');
        $def->info_tag_transform['h2'] = new HTMLPurifier_TagTransform_Simple('h3');

        $purifier = new HTMLPurifier($config);
        $firstPass = trim($purifier->purify($value));

        if ($this->baseUrl === null) {
            return $firstPass;
        }

        // second pass with url
        $config = HTMLPurifier_Config::createDefault();
        $config->set('URI.Base', $this->baseUrl);
        $config->set('HTML.TargetBlank', true);
        $purifier = new HTMLPurifier($config);

        return trim($purifier->purify($firstPass));
    }

    /**
     * html to markdown converter
     * @param  string $html html input
     * @return string
     */
    protected function _toMarkDown($html)
    {
        $converter = new HtmlConverter(['strip_tags' => true]);

        return $converter->convert($html);
    }
}

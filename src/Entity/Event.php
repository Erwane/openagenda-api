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
        $value = $this->_i18nValue($value, $lang);

        $this->setI18nProperty('title', $value);

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
        if (is_string($keywords)) {
            $keywords = array_map('trim', explode(',', $keywords));
        }

        $keywords = implode(', ', $keywords);

        $value = $this->_i18nValue($keywords, $lang);

        $this->setI18nProperty('keywords', $value);

        return $this;
    }

    public function setTags($keywords, $lang = null)
    {
        return $this->setKeywords($keywords, $lang);
    }

    /**
     * set event description. 200 max length and no html
     * @param string $value property value
     * @param string $lang lang information
     * @return self
     */
    public function setDescription($value, $lang = null)
    {
        $lang = $this->_getLang($lang);

        $values = $this->_i18nValue($value, $lang);

        foreach ($values as $lang => $value) {
            // remove tags
            $text = strip_tags($value);

            // decode html
            $text = html_entity_decode($text, ENT_QUOTES);

            // remove new lines
            $text = preg_replace(['/\\r?\\n/', '/^\\r?\\n$/', '/^$/'], ' ', $text);

            // remove unused white spaces
            $text = preg_replace('/[\pZ\pC]+/u', ' ', $text);

            if (mb_strlen($text) > 194) {
                $text = mb_substr($text, 0, 190) . ' ...';
            }

            if (!isset($this->_properties['description'][$lang]) || $value !== $this->_properties['description'][$lang]) {
                $this->setDirty('description.' . $lang, true);
            }

            $this->_properties['description'][$this->_getLang($lang)] = $text;
        }

        return $this;
    }

    /**
     * set free text
     * @param string $text property value
     * @param string $lang lang information
     * @deprecated 1.1 use setLongDescription
     * @return self
     */
    public function setFreeText($text, $lang = null)
    {
        return $this->setLongDescription($text, $lang);
    }

    /**
     * set event long description (mark down)
     * @param string $text text or html or markdown
     * @param string $lang lang information
     * @return self
     */
    public function setLongDescription($text, $lang = null)
    {
        $lang = $this->_getLang($lang);

        $values = $this->_i18nValue($text, $lang);

        foreach ($values as $lang => $value) {

            $value = $this->_cleanHtml($value);

            $value = $this->_toMarkDown($value);

            if (!isset($this->_properties['longDescription'][$lang]) || $value !== $this->_properties['longDescription'][$lang]) {
                $this->setDirty('longDescription.' . $lang, true);
            }

            $this->_properties['longDescription'][$this->_getLang($lang)] = mb_substr($value, 0, 5800);
        }

        return $this;
    }

    /**
     * attach the location object to event and set locationUid
     * @param Location $location entity
     */
    public function setLocation(Location $location)
    {
        $this->locationUid = $location->uid;

        $this->_properties['location'] = $location;

        return $this;
    }

    /**
     * set event picture
     * @param string $file absolute path
     * @deprecated 1.1 use setImage
     * @return self
     */
    public function setPicture($file)
    {
        return $this->setImage($file);
    }

    /**
     * set event image
     * @param string $file absolute path
     * @return self
     */
    public function setImage($file)
    {
        if (empty($file)) {
            return;
        }

        if (!file_exists($file)) {
            throw new Exception("image file does not exists", 1);
        }

        $this->_properties['image'] = fopen($file, 'r');

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function toDatas()
    {
        $keys = ['title', 'keywords', 'description', 'longDescription', 'locationUid', 'image'];
        $dirties = $this->getDirtyArray();

        $datas = array_intersect_key($dirties, array_flip($keys));

        $return = [
            'publish' => $this->state,
            'data' => json_encode($datas),
        ];

        // picture
        if (!is_null($this->image)) {
            $return[] = ['name' => 'image', 'contents' => $this->image, 'Content-type' => 'multipart/form-data'];
        }

        return $return;
    }

    public function toArray()
    {
        $datas = $this->getDirtyArray();

        $return = [
            'data' => json_encode($datas),
        ];

        // picture
        if (!is_null($this->image)) {
            $return[] = ['name' => 'image', 'contents' => $this->image, 'Content-type' => 'multipart/form-data'];
        }

        return $return;

        debug($datas);

        return parent::toArray();

        // Tests
        foreach (['title', 'description', 'freeText', 'locations'] as $key) {
            if (empty($this->uid) && (is_null($this->{$key}) || $this->{$key} == '')) {
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
        if ($html === strip_tags($html)) {
            return $html;
        }

        $converter = new HtmlConverter(['strip_tags' => true]);

        return $converter->convert($html);
    }
}

<?php
declare(strict_types=1);

namespace OpenAgenda\Entity;

use DateTime;
use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_TagTransform_Simple;
use League\HTMLToMarkdown\HtmlConverter;
use OpenAgenda\OpenAgendaException;

/**
 * @property int $id
 * @property int $uid
 * @property int $locationUid
 * @property \OpenAgenda\Entity\Location $location
 * @property int $state
 * @property string $image
 * @property string|null $baseUrl
 * @deprecated 2.0.4 Rewrited in ^3.0 Check README.
 */
class Event extends Entity
{

    /**
     * set event title
     *
     * @param bool $value property value
     * @return $this
     */
    public function setState(bool $value)
    {
        $this->_properties['state'] = $value;

        return $this;
    }

    /**
     * set event title
     *
     * @param string $value property value
     * @param string|null $lang lang information
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setTitle(string $value, string $lang = null)
    {
        $value = $this->_i18nValue($value, $lang);

        $this->setI18nProperty('title', $value);

        return $this;
    }

    /**
     * set event keywords (old tags)
     *
     * @param $keywords
     * @param null $lang lang information
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
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

    /**
     * @param $keywords
     * @param null $lang
     * @return \OpenAgenda\Entity\Event
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setTags($keywords, $lang = null)
    {
        return $this->setKeywords($keywords, $lang);
    }

    /**
     * set event description. 200 max length and no html
     *
     * @param string $value property value
     * @param null $lang lang information
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setDescription(string $value, $lang = null)
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
     *
     * @param string $text property value
     * @param null $lang lang information
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     * @deprecated 1.1 use setLongDescription
     */
    public function setFreeText(string $text, $lang = null)
    {
        return $this->setLongDescription($text, $lang);
    }

    /**
     * set event long description (mark down)
     *
     * @param string $text text or html or markdown
     * @param null $lang lang information
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setLongDescription(string $text, $lang = null)
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
     *
     * @param Location $location entity
     * @return \OpenAgenda\Entity\Event
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setLocation(Location $location)
    {
        $this->locationUid = $location->uid;

        $this->_properties['location'] = $location;

        if (is_array($location->dates)) {
            foreach ($location->dates as $date) {
                $this->addTiming($date);
            }
        }

        return $this;
    }

    /**
     * add timing to event, only if don't exists
     *
     * @param array $datas timings : ['date' => '2017-11-15', 'begin' => '08:30', 'end' => '19:00']
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     * @throws \Exception
     */
    public function addTiming(array $datas)
    {
        if (!isset($this->_properties['timings'])) {
            $this->_properties['timings'] = [];
        }
        if (!isset($datas['begin'])) {
            throw new OpenAgendaException('missing begin field', 1);
        }
        if (!isset($datas['end'])) {
            throw new OpenAgendaException('missing end field', 1);
        }

        // use instance of DateTime only
        if (!($datas['begin'] instanceof DateTime)) {
            $datas['begin'] = new DateTime($datas['begin']);
        }
        if (!($datas['end'] instanceof DateTime)) {
            $datas['end'] = new DateTime($datas['end']);
        }

        $timing = [
            'begin' => $datas['begin']->format('c'),
            'end' => $datas['end']->format('c'),
        ];

        // check if timing exists
        $exists = false;
        foreach ($this->_properties['timings'] as $t) {
            if ($timing['date'] == $t['date']
                && $timing['begin'] == $t['begin']
                && $timing['end'] == $t['end']
            ) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $this->_properties['timings'][] = $timing;
        }

        $this->setDirty('timings', true);

        return $this;
    }

    /**
     * remove all timings and set to $timings
     *
     * @param array $timings array of timing
     * @return \OpenAgenda\Entity\Event
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setTimings(array $timings = [])
    {
        $this->_properties['timings'] = [];

        foreach ($timings as $timing) {
            $this->addTiming($timing);
        }

        return $this;
    }

    /**
     * set event picture
     *
     * @param string $file absolute path
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     * @deprecated 1.1 use setImage
     */
    public function setPicture(string $file)
    {
        return $this->setImage($file);
    }

    /**
     * set event image
     *
     * @param string $file Absolute path
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setImage(string $file)
    {
        if (empty($file)) {
            return $this;
        }

        if (!file_exists($file)) {
            throw new OpenAgendaException('image file does not exists', 1);
        }

        // set properties, not image to skip auto setDirty
        $this->_properties['image'] = fopen($file, 'r');

        return $this;
    }

    /**
     * set event entrance conditions
     *
     * @param string $value property value
     * @param string|null $lang language
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setConditions(string $value, ?string $lang = null)
    {
        $value = $this->_i18nValue($value, $lang);

        $this->setI18nProperty('conditions', $value);

        return $this;
    }

    /**
     * setConditions alias
     *
     * @param string $value property value
     * @param string|null $lang language
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function setPricing(string $value, ?string $lang = null)
    {
        return $this->setConditions($value, $lang);
    }

    /**
     * @inheritDoc
     */
    public function toDatas()
    {
        $requiredKeys = ['title', 'description', 'locationUid', 'timings'];
        $requiredDatas = array_intersect_key($this->toArray(), array_flip($requiredKeys));

        $datas = array_merge($requiredDatas, $this->getDirtyArray());

        // $keys = ['title', 'keywords', 'description', 'longDescription', 'locationUid', 'image', 'timings', 'conditions', 'age'];
        // $dirties = $this->getDirtyArray();

        // $datas = array_intersect_key($dirties, array_flip($keys));

        // picture
        if (!is_null($this->image)) {
            $datas['image'] = $this->image;
        }

        return [
            'publish' => $this->state,
            'data' => $datas,
        ];
    }

    /**
     * clean description html tags
     *
     * @param string $value worse html ever
     */
    protected function _cleanHtml(string $value)
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
     *
     * @param string $html html input
     * @return string
     */
    protected function _toMarkDown(string $html)
    {
        if ($html === strip_tags($html)) {
            return $html;
        }

        $converter = new HtmlConverter(['strip_tags' => true]);

        return $converter->convert($html);
    }

    /**
     * @param int|string $value
     * @return int
     */
    protected function _setAgendaUid($value)
    {
        return (int)$value;
    }

    /**
     * set event age
     *
     * @param int $min min age
     * @param int $max max age
     * @retur self
     */
    public function setAge(int $min = 0, int $max = 120)
    {
        $this->_properties['age'] = [
            'min' => $min,
            'max' => $max,
        ];

        $this->setDirty('age', true);

        return $this;
    }
}

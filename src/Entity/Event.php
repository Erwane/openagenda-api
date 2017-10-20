<?php
namespace OpenAgenda\Entity;

class Event
{
    use EntityTrait;

    /**
     * set event title
     * @param string $value property value
     * @return self
     */
    public function setTitle($value)
    {
        $this->_properties['title'] = $value;

        return $this;
    }

    /**
     * set event pricing infos
     * @param string $value property value
     * @return self
     */
    public function setPricing($value)
    {
        $this->_properties['pricingInfo'] = $value;

        return $this;
    }

    /**
     * set event keywords (old tags)
     * @param string $value property value
     * @return self
     */
    public function setKeywords($keywords)
    {
        if (!is_array($keywords)) {
            $keywords = array_map('trim', explode(',', $keywords));
        }

        $this->_properties['tags'] = implode(', ', $keywords);

        return $this;
    }

    /**
     * set event description. 200 max length and no html
     * @param string $value property value
     * @return self
     */
    public function setDescription($value)
    {
        // remove tags
        $text = strip_tags($value);

        // decode html
        $text = html_entity_decode($text, ENT_QUOTES);

        // remove new lines
        $text = preg_replace(['/\\r?\\n/', '/^\\r?\\n$/', '/^$/'], ' ', $text);

        // remove unused white spaces
        $text = preg_replace('/[\pZ\pC]+/u', ' ', $text);

        $this->_properties['description'] = mb_substr($text, 0, 190) . ' ...';

        return $this;
    }

    /**
     * set free text
     * @param string $value property value
     * @return self
     */
    public function setFreeText($text)
    {
        $this->_properties['freeText'] = mb_substr($text, 0, 5800);

        return $this;
    }

    public function setLocation(Location $location)
    {
        $this->_properties['locations'] = $location->toArray();

        return $this;
    }
}
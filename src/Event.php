<?php
namespace OpenAgenda;

class Event
{
    protected $_properties = [];

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

    public function setKeywords($keywords)
    {
        if (!is_array($keywords)) {
            $keywords = array_map('trim', explode(',', $keywords));
        }

        $this->_properties['tags'] = implode(', ', $keywords);

        return $this;
    }
}
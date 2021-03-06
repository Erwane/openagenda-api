<?php
namespace OpenAgenda\Entity;

class Agenda extends Entity
{
    /**
     * set event keywords (old tags)
     * @param string $value property value
     * @return self
     */
    public function setCategory($category)
    {
        if (is_array($category)) {
            $category = implode(', ', array_map('trim', $category));
        }

        $this->_properties['category'] = $category;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function toDatas()
    {
        return [];
    }
}

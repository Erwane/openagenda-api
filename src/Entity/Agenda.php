<?php
declare(strict_types=1);

namespace OpenAgenda\Entity;

/**
 * @property int $uid
 */
class Agenda extends Entity
{
    /**
     * set event keywords (old tags)
     *
     * @param $category
     * @return $this
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

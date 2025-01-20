<?php
declare(strict_types=1);

/**
 * OpenAgenda API client.
 * Copyright (c) Erwane BRETON
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Erwane BRETON
 * @see         https://github.com/Erwane/openagenda-api
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace OpenAgenda;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorIterator;
use JsonSerializable;
use LimitIterator;

/**
 * Basic Collection class.
 */
class Collection extends IteratorIterator implements Countable, Iterator, JsonSerializable
{
    /**
     * @inheritDoc
     */
    public function __construct(iterable $items)
    {
        if (is_array($items)) {
            $items = new ArrayIterator($items);
        }

        parent::__construct($items);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return iterator_count($this);
    }

    /**
     * Get first element.
     *
     * @return mixed|null
     */
    public function first()
    {
        $result = null;
        $iterator = new LimitIterator($this, 0, 1);
        foreach ($iterator as $result) {
            // $result sets
        }

        return $result;
    }

    /**
     * Get first element.
     *
     * @return mixed|null
     */
    public function last()
    {
        $count = $this->count();

        $result = null;
        if ($count) {
            $iterator = new LimitIterator($this, $count - 1, 1);
            $iterator->rewind();
            $result = $iterator->current();
        }

        return $result;
    }

    /**
     * Collection as array.
     * All object with `toArray` will also been returned as array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this as $k => $item) {
            if (is_object($item) && method_exists($item, 'toArray')) {
                $items[$k] = $item->toArray();
            } else {
                $items[$k] = $item;
            }
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}

<?php

namespace Framework\Database\ORM;

use Framework\Support\Collections\Collection as BaseCollection;

/**
 * Class Collection
 *
 * This class extends the base collection class and adds ORM-specific functionality.
 * It provides methods for converting the collection to an array, setting visible attributes, and setting hidden attributes.
 *
 * @package Framework\Database\ORM
 */
class Collection extends BaseCollection
{
    /**
     * Convert the collection of model instances to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->map(function ($item) {
            return $item->toArray();
        })->all();
    }

    /**
     * Set the visible attributes for all items in the collection.
     *
     * @param array|string $visible
     * @return $this
     */
    public function setVisible($visible)
    {
        return $this->each->setVisible($visible);
    }

    /**
     * Set the hidden attributes for all items in the collection.
     *
     * @param array|string $hidden
     * @return $this
     */
    public function setHidden($hidden)
    {
        return $this->each->setHidden($hidden);
    }
}
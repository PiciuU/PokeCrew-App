<?php

namespace Framework\Support\Collections;

/**
 * Trait CollectionIterator
 *
 * The CollectionIterator trait provides basic Iterator functionality to classes using it.
 * It implements the Iterator interface, allowing objects to be iterated using a foreach loop.
 *
 * @package Framework\Support\Collections
 */
trait CollectionIterator
{
    /**
     * The current position in the collection.
     *
     * @var int
     */
    private $position = 0;

    /**
     * Get the current item in the collection.
     *
     * @return mixed The current item.
     */
    public function current(): mixed
    {
        return $this->items[$this->position];
    }

    /**
     * Get the key of the current item in the collection.
     *
     * @return mixed The key of the current item.
     */
    public function key(): mixed
    {
        return $this->position;
    }

    /**
     * Move to the next item in the collection.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Rewind the iterator to the first item in the collection.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Check if the current position in the collection is valid.
     *
     * @return bool True if the current position is valid; otherwise, false.
     */
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }
}
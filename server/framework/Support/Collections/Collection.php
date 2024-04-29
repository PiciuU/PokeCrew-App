<?php

namespace Framework\Support\Collections;

use ArrayAccess;
use JsonSerializable;

use Framework\Support\Arr;

/**
 * Class Collection
 *
 * The Collection class provides a set of methods to work with arrays or iterable items
 * in a convenient and functional way.
 *
 * @package Framework\Support\Collections
 */
class Collection implements \Iterator, ArrayAccess, JsonSerializable
{
    use CollectionIterator;

    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Collection constructor.
     *
     * @param array $items Initial items for the collection.
     */
    public function __construct(array $items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Get the arrayable items from a given input.
     *
     * @param mixed $items The input items.
     * @return array The arrayable items.
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        }

        return match (true) {
            default => (array) $items,
        };
    }

    /**
     * Add an item to the collection.
     *
     * @param mixed $item The item to add.
     */
    public function add($item)
    {
        $this->items[] = $item;
    }

    /**
     * Get all items from the collection.
     *
     * @return array The collection items.
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get the number of items in the collection.
     *
     * @return int The number of items.
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Check if the collection is empty.
     *
     * @return bool True if the collection is empty, false otherwise.
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Check if the collection is not empty.
     *
     * @return bool True if the collection is not empty, false otherwise.
     */
    public function isNotEmpty()
    {
        return !empty($this->items);
    }

    /**
     * Get an item from the collection by key.
     *
     * @param mixed $key The key of the item.
     * @param mixed $default The default value if the key is not found.
     * @return mixed The item value.
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return 0;
    }

    /**
     * Implode the values of the collection.
     *
     * @param mixed $value The value to implode.
     * @param string|null $glue The glue between values.
     * @return string The imploded string.
     */
    public function implode($value, $glue = null)
    {
        if ($this->useAsCallable($value)) {
            return implode($glue ?? '', $this->map($value)->all());
        }

        $first = $this->first();

        if (is_array($first) || (is_object($first) && ! $first instanceof Stringable)) {
            return implode($glue ?? '', $this->pluck($value)->all());
        }

        return implode($value ?? '', $this->items);
    }

    /**
     * Run a filter over each of the items.
     *
     * @param  (callable(TValue, TKey): bool)|null  $callback
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(Arr::where($this->items, $callback));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Get the first item from the collection.
     *
     * @param callable|null $callback A callback for filtering the items.
     * @param mixed $default The default value if no item is found.
     * @return mixed The first item.
     */
    public function first(callable $callback = null, $default = null)
    {
        return Arr::first($this->items, $callback, $default);
    }

    /**
     * Get the keys of the collection items.
     *
     * @return static<int, TKey>
     */
    public function keys()
    {
        return new static(array_keys($this->items));
    }

    /**
     * Get the last item from the collection.
     *
     * @template TLastDefault
     *
     * @param  (callable(TValue, TKey): bool)|null  $callback
     * @param  TLastDefault|(\Closure(): TLastDefault)  $default
     * @return TValue|TLastDefault
     */
    public function last(callable $callback = null, $default = null)
    {
        return Arr::last($this->items, $callback, $default);
    }

    /**
     * Pluck a specific value from each item in the collection.
     *
     * @param string $value The value to pluck.
     * @param string|null $key The key to use as the array key.
     * @return static A new collection with the plucked values.
     */
    public function pluck($value, $key = null)
    {
        return new static(Arr::pluck($this->items, $value, $key));
    }

    /**
     * Map each item in the collection to a new value.
     *
     * @param callable $callback The callback for mapping.
     * @return static A new collection with the mapped values.
     */
    public function map(callable $callback)
    {
        return new static(Arr::map($this->items, $callback));
    }

    /**
     * Get all the values of the collection.
     *
     * @return static A new collection with the values.
     */
    public function values()
    {
        return new static(array_values($this->items));
    }

    /**
     * Check if the given value should be used as a callable.
     *
     * @param mixed $value The value to check.
     * @return bool True if the value should be used as a callable, false otherwise.
     */
    protected function useAsCallable($value)
    {
        return ! is_string($value) && is_callable($value);
    }

    /**
     * Convert collection to an array.
     *
     * @return array An array of all items from collection
     */
    public function toArray()
    {
        return $this->map(fn ($value) => $value)->all();
    }

        /**
     * Determine if an item exists at an offset.
     *
     * @param  TKey  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  TKey  $key
     * @return TValue
     */
    public function offsetGet($key): mixed
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  TKey|null  $key
     * @param  TValue  $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  TKey  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array<TKey, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, $this->all());
    }

}
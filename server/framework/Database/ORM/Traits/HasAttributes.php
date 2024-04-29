<?php

namespace Framework\Database\ORM\Traits;

use Framework\Support\Arr;

/**
 * Trait HasAttributes
 *
 * This trait provides methods for managing attributes in a model.
 *
 * @package Framework\Database\ORM\Traits
 */
trait HasAttributes
{
    /**
     * The original attributes of the model.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The current attributes of the model.
     *
     * @var array
     */
    protected $original = [];

    /**
     * The changes made to the model.
     *
     * @var array
     */
    protected $changes = [];

    /**
     * Set the raw attributes for the model.
     *
     * @param  array  $attributes
     * @param  bool  $sync
     * @return $this
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $this->attributes = $attributes;

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }

    /**
     * Sync the original attributes with the current.
     *
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original = $this->getAttributes();

        return $this;
    }

    /**
     * Get the current attributes of the model.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get the attributes that should be inserted into the database.
     *
     * @return array
     */
    protected function getAttributesForInsert()
    {
        return $this->getAttributes();
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get a given attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!$key) return;

        return $this->attributes[$key];
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = $this->getArrayableItems($this->getAttributes());

        return $attributes;
    }

    /**
     * Get the arrayable items of a given array.
     *
     * @param  array  $values
     * @return array
     */
    protected function getArrayableItems(array $values)
    {
        if (count($this->getVisible()) > 0) {
            $values = array_intersect_key($values, array_flip($this->getVisible()));
        }

        if (count($this->getHidden()) > 0) {
            $values = array_diff_key($values, array_flip($this->getHidden()));
        }

        return $values;
    }

    /**
     * Sync the changes on the model.
     *
     * @return $this
     */
    public function syncChanges()
    {
        $this->changes = $this->getDirty();

        return $this;
    }

    /**
     * Determine if the model or given attributes have changes.
     *
     * @param  array|string|null  $attributes
     * @return bool
     */
    public function isDirty($attributes = null)
    {
        return $this->hasChanges(
            $this->getDirty(), is_array($attributes) ? $attributes : func_get_args()
        );
    }

    /**
     * Get the attributes that have been changed.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (!$this->originalIsEquivalent($key)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Determine if an attribute is equivalent to its original state.
     *
     * @param  string  $key
     * @return bool
     */
    public function originalIsEquivalent($key)
    {
        if (!array_key_exists($key, $this->original)) {
            return false;
        }

        $attribute = Arr::get($this->attributes, $key);
        $original = Arr::get($this->original, $key);

        if ($attribute === $original) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the model or given attributes have changes.
     *
     * @param  array  $changes
     * @param  array|string|null  $attributes
     * @return bool
     */
    protected function hasChanges($changes, $attributes = null)
    {
        // If no specific attributes were provided, we will just see if the dirty array
        // already contains any attributes. If it does we will just return that this
        // count is greater than zero. Else, we need to check specific attributes.
        if (empty($attributes)) {
            return count($changes) > 0;
        }

        // Here we will spin through every attribute and see if this is in the array of
        // dirty attributes. If it is, we will return true and if we make it through
        // all of the attributes for the entire array we will return false at end.
        foreach (Arr::wrap($attributes) as $attribute) {
            if (array_key_exists($attribute, $changes)) {
                return true;
            }
        }

        return false;
    }


}
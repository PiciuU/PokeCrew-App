<?php

namespace Framework\Database\ORM\Traits;

/**
 * Trait GuardsAttributes
 *
 * This trait provides methods for managing fillable and guarded attributes in a model.
 * Fillable attributes are those that are mass assignable, while guarded attributes are protected from mass assignment.
 *
 * @package Framework\Database\ORM\Traits
 */
trait GuardsAttributes
{
    /**
     * The fillable attributes for the model.
     *
     * @var array<string>
     */
    protected $fillable = [];

    /**
     * Indicates whether all attributes are guarded.
     *
     * @var bool
     */
    protected $guarded = true;

    /**
     * Get the fillable attributes for the model.
     *
     * @return array<string>
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * Set the fillable attributes for the model.
     *
     * @param  array<string>  $fillable
     * @return $this
     */
    public function fillable(array $fillable)
    {
        $this->fillable = $fillable;

        return $this;
    }

    /**
     * Merge new fillable attributes with existing fillable attributes on the model.
     *
     * @param  array<string>  $fillable
     * @return $this
     */
    public function mergeFillable(array $fillable)
    {
        $this->fillable = array_values(array_unique(array_merge($this->fillable, $fillable)));

        return $this;
    }

    /**
     * Check if all attributes are guarded.
     *
     * @return bool
     */
    public function isGuarded()
    {
        return $this->guarded;
    }

    /**
     * Set the guarded state for the model.
     *
     * @param  array<string>  $guarded
     * @return $this
     */
    public function guard(array $guarded)
    {
        $this->guarded = true;

        return $this;
    }

    /**
     * Disable guarding for the model.
     *
     * @param  bool  $state
     * @return $this
     */
    public function unguard($state = true)
    {
        $this->guarded = false;
    }

    /**
     * Determine if the given attribute may be mass assigned.
     *
     * @param  string  $key
     * @return bool
     */
    public function isFillable($key)
    {
        if ($this->guarded === false) {
            return true;
        }

        if (in_array($key, $this->getFillable())) {
            return true;
        }

        return false;
    }


    /**
     * Get the fillable attributes of a given array.
     *
     * @param  array  $attributes
     * @return array
     */
    protected function fillableFromArray(array $attributes)
    {
        if ($this->guarded === false) {
            return $attributes;
        } else if (count($this->getFillable()) > 0) {
            return array_intersect_key($attributes, array_flip($this->getFillable()));
        } else {
            return [];
        }
    }
}

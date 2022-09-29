<?php

namespace Pharaonic\Laravel\Basket;

use Pharaonic\Laravel\Basket\Models\BasketItem;

class Basket
{
    protected $model;

    public function __construct()
    {
        // 
    }

    /**
     * Add a new basket item.
     *
     * @return BasketItem
     */
    public function add()
    {
        // 
    }

    /**
     * Remove a basket item.
     *
     * @param \Illuminate\Database\Eloquent\Model|int $identifier
     * @return bool
     */
    public function remove($identifier)
    {
        // 
    }

    /**
     * Clear all basket items.
     *
     * @return bool
     */
    public function clear()
    {
        // 
    }

    /**
     * Get a basket item/Items.
     *
     * @param \Illuminate\Database\Eloquent\Model|int $identifier
     * @return BasketItem|null
     */
    public function find($identifier)
    {
        // 
    }

    /**
     * Get all basket items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        // 
    }
}

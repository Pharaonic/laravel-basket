<?php

namespace Pharaonic\Laravel\Basket;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Basket
{
    protected $model;

    public function __construct()
    {
        // 
    }

    /**
     * Create a new basket.
     *
     * @param string $currency
     * @param string|null $user_agent
     * @return static
     */
    public function create(string $currency, string $user_agent = null)
    {
        // 
    }

    /**
     * Assign a user to the basket.
     *
     * @param Authenticatable $user
     * @return static
     */
    public function assignUser(Authenticatable $user)
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

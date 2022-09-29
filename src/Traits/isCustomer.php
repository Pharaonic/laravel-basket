<?php

namespace Pharaonic\Laravel\Basket\Traits;

use Pharaonic\Laravel\Basket\Models\Basket;
use Pharaonic\Laravel\Basket\Models\Order;

trait isCustomer
{
    /**
     * Get all of the user's baskets.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function baskets()
    {
        return $this->morphMany(Basket::class, 'user');
    }

    /**
     * Get all of the user's orders.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function orders()
    {
        return $this->morphMany(Order::class, 'user');
    }
}

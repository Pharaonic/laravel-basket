<?php

namespace Pharaonic\Laravel\Basket;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Pharaonic\Laravel\Basket\Exceptions\BasketNotFoundException;
use Pharaonic\Laravel\Basket\Exceptions\BasketUnauthorizedException;
use Pharaonic\Laravel\Basket\Models\Basket;

class BasketManager
{
    /**
     * Basket Model
     *
     * @var Basket|null
     */
    private $basket;

    /**
     * Basket Items
     *
     * @var Collection
     */
    private $items;

    public function __construct()
    {
        // Use the current basket automatically (Web Only)
        // TODO : Enable through config.
        if (!request()->wantsJson()) {
            if ($id = Session::isStarted() ? Session::get('basket_id') : Cookie::get('basket_id')) {
                $this->use($id);
            }
        }
    }

    /**
     * Use a specific basket through id.
     *
     * @param string $id
     * @return $this
     */
    public function use(string $id)
    {
        if (!($basket = Basket::with(['items.modelable'])->find($id))) {
            throw new BasketNotFoundException('Basket has not found');
        }

        // Check Authorization
        if (
            $basket->user_agent && $basket->user_agent != request()->server('HTTP_USER_AGENT') ||
            (($user = auth()->user()) && !$basket->user_agent && ($user::class != $basket->user_type || $user->id != $basket->user_id))
        ) {
            throw new BasketUnauthorizedException('You are not authorized to use this basket.');
        }

        // Assign the user to the basket automatically.
        if ($user && $basket->user_agent) {
            $basket->user_agent = null;
            $basket->user()->associate($user);
            $basket->save();
        }

        $this->basket = $basket;
        return $this;
    }

    /**
     * Create a new basket.
     *
     * @param string|null $currency
     * @param string|null $user_agent
     * @return $this
     */
    public function create(string $currency = null, string $user_agent = null)
    {
        // 
    }

    /**
     * Destroy the current basket.
     *
     * @return void
     */
    public function destroy()
    {
        if (!$this->basket) return;

        // TODO : Depends on it's status
        $this->basket->delete();
        $this->basket = null;
    }

    /**
     * Assign a user to the basket.
     *
     * @param Authenticatable $user
     * @return $this
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

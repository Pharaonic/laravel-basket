<?php

namespace Pharaonic\Laravel\Basket;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Pharaonic\Laravel\Basket\Exceptions\BasketNotFoundException;
use Pharaonic\Laravel\Basket\Exceptions\BasketUnauthorizedException;
use Pharaonic\Laravel\Basket\Models\Basket;
use Pharaonic\Laravel\Basket\Models\BasketItem;
use Pharaonic\Laravel\Basket\Traits\isCustomer;

class BasketManager
{
    /**
     * Basket Model
     *
     * @var Basket|null
     */
    private $basket;

    /**
     * Basket Configurations
     *
     * @var array
     */
    private $config;

    public function __construct()
    {
        $this->config = config('Pharaonic.basket');

        // Use the current basket automatically (Web Only)
        if ($this->config['auto_detect'] && !request()->wantsJson()) {
            if ($id = Session::isStarted() ? Session::get('basket_id') : Cookie::get('basket_id')) {
                $this->use($id);
            }
        }
    }

    public function __get($name)
    {
        $name = 'get' . Str::studly($name) . 'Attribute';

        if (method_exists($this, $name)) return $this->{$name};

        return null;
    }

    /**
     * Get items count
     *
     * @return int
     */
    public function getCountAttribute()
    {
        if (!$this->basket) return 0;

        return $this->basket->items->count();
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

        $this->basket = $basket;

        // Assign the user to the basket automatically.
        if ($user && in_array(isCustomer::class, class_uses($user)) && $basket->user_agent) {
            $this->assignUser($user);
        }

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
        if ($user = auth()->user()) {
            $this->basket = Basket::create(['currency' => $currency ?? $this->config['currency']]);
            $this->basket->user()->associate($user)->save();
        } else {
            $this->basket = Basket::create([
                'currency'      => $currency ?? $this->config['currency'],
                'user_agent'    => $user_agent ?? request()->server('HTTP_USER_AGENT')
            ]);
        }

        // Store For Web ONLY
        if ($this->config['auto_detect'] && !request()->wantsJson()) {
            if (Session::isStarted()) {
                Session::put('basket_id', $this->basket->id);
            } else {
                Cookie::queue(cookie()->forever('basket_id', $this->basket->id));
            }
        }

        return $this;
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
     * @param isCustomer $user
     * @return $this
     */
    public function assignUser(isCustomer $user)
    {
        $basket->user_agent = null;
        $basket->user()->associate($user);
        $basket->save();

        return $this;
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
     * @param  int $identifier
     * @return bool
     */
    public function remove(int $identifier)
    {
        // Remove by basket-item index
        if ($item = $this->find($identifier)) {
            // TODO : Depends on status
            $item->delete();
            $this->basket->items->forget($identifier);

            return true;
        }

        return false;
    }

    /**
     * Clear all basket items.
     *
     * @return bool
     */
    public function clear()
    {
        if (!$this->basket) return false;

        $this->basket->items()->delete();
        $this->basket->setRelation('items', new EloquentCollection([]));

        return true;
    }

    /**
     * Get a basket item/Items.
     *
     * @param int $identifier
     * @return BasketItem|null
     */
    public function find(int $identifier)
    {
        if (!$this->basket)
            throw new BasketNotFoundException('Basket has not found');

        if (!isset($this->basket->items[$identifier]))
            return;

        return $this->basket->items[$identifier];
    }

    /**
     * Get all basket items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        if (!$this->basket)
            throw new BasketNotFoundException('Basket has not found');

        return $this->basket->items;
    }
}

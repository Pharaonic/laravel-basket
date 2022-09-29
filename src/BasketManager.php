<?php

namespace Pharaonic\Laravel\Basket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
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
	 * Items List
	 *
	 * @var Collection
	 */
	private $items;

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

	/**
	 * Use a specific basket through id.
	 *
	 * @param string $id
	 * @return $this
	 */
	public function use(string $id)
	{
		if (!($basket = Basket::find($id))) {
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

		// Publish basket items
		$this->items = collect([]);
		foreach ($basket->items()->with('modelable')->get() as $index => $item) {
			$this->items->push(new BasketItemManager($index, $item));
		}

		return $this;
	}

	/**
	 * Assign a user to the basket.
	 *
	 * @param isCustomer $user
	 * @return $this
	 */
	public function assignUser(isCustomer $user)
	{
		if (!$this->basket)
			throw new BasketNotFoundException('Basket has not found');

		$basket->user_agent = null;
		$basket->user()->associate($user);
		$basket->save();

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
		if (!$this->basket)
			throw new BasketNotFoundException('Basket has not found');

		// TODO : Depends on it's status
		$this->basket->delete();
		$this->basket = null;
		$this->items = collect([]);

		// Web ONLY
		if ($this->config['auto_detect'] && !request()->wantsJson()) {
			if (Session::isStarted()) {
				Session::forget('basket_id');
			} else {
				Cookie::queue(cookie()->forget('basket_id'));
			}
		}
	}

	/**
	 * Add a new basket item.
	 *
	 * @param mixed ...$items
	 * @return BasketItem|boolean|null
	 */
	public function add(...$items)
	{
		if (!$this->basket)
			throw new BasketNotFoundException('Basket has not found');

		if (is_array($items[0])) {
			$items = $items[0];

			if (isset($item[0]) && is_array($item[0])) {
				// Multiple Items
				foreach ($items as $item) {
					return $this->insert($item['name'], $item['price'], $item['quantity'] ?? 1, $item['attributes'] ?? null, $item['product'] ?? null);
				}
			} else {
				// Single Item
				return $this->insert($items['name'], $items['price'], $items['quantity'] ?? 1, $items['attributes'] ?? null, $items['product'] ?? null);
			}
		} else {
			return $this->insert($items[0], $items[1], $items[2] ?? 1, $items[3] ?? null, $items[4] ?? null);
		}
	}

	/**
	 * Insert a new basket item.
	 *
	 * @param string $name
	 * @param float $price
	 * @param int $quantity
	 * @param array|null $attributes
	 * @param Model|null $product
	 * @return BasketItem
	 */
	private function insert(string $name, float $price, int $quantity = 1, array $attributes = null, Model $product = null)
	{
		if ($product && $basketItem = $this->findByModel($product, $attributes)) {
			$basketItem->increment();
		} else {
			$item = $this->basket->items()->create([
				'name' => $name,
				'price' => $price,
				'quantity' => $quantity,
				'attributes' => $attributes,
			]);

			if ($product) {
				$item->modelable()->associate($product)->save();
			}

			$basketItem = new BasketItemManager($this->count(), $item);
			$this->items->append($basketItem);
		}

		return $basketItem;
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
			return $item->remove();
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
		if (!$this->basket)
			throw new BasketNotFoundException('Basket has not found');

		$this->basket->items()->delete();
		$this->items = collect([]);

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

		if (!isset($this->items[$identifier]))
			return;

		return $this->items[$identifier];
	}

	/**
	 * Find a basket item through the model and it's attributes.
	 *
	 * @param Model $model
	 * @param array|null $attributes
	 * @return BasketItemManager|null
	 */
	private function findByModel(Model $model, array $attributes = null)
	{
		return $this->items->filter(function ($item) use ($model, $attributes) {
			return $item->product == $model && $item->attributes == $attributes;
		})->first();
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

		return $this->items;
	}

	/**
	 * Get items count
	 *
	 * @return int
	 */
	public function count()
	{
		if (!$this->basket)
			throw new BasketNotFoundException('Basket has not found');

		return $this->items->count();
	}

	/**
	 * Check if items list is empty.
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		if (!$this->basket)
			throw new BasketNotFoundException('Basket has not found');

		return $this->items->isEmpty();
	}

	/**
	 * Check if items list is not empty.
	 *
	 * @return boolean
	 */
	public function isNotEmpty()
	{
		if (!$this->basket)
			throw new BasketNotFoundException('Basket has not found');

		return $this->items->isNotEmpty();
	}
}

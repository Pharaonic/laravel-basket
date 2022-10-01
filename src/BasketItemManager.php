<?php

namespace Pharaonic\Laravel\Basket;

use Illuminate\Database\Eloquent\Model;
use Pharaonic\Laravel\Basket\Facades\Basket;

class BasketItemManager
{
    /**
     * Basket Item index
     *
     * @var int
     */
    private $index;

    /**
     * Basket Item Fields
     *
     * @var array
     */
    private $fields;

    /**
     * Basket Item Model
     *
     * @var Model|null
     */
    private $model;

    public function __construct(int $index, Model $model)
    {
        $this->index = $index;
        $this->model = $model;

        $this->fields = [
            'name'          => $model->name,
            'price'         => $model->price,
            'quantity'      => $model->quantity,
            'attributes'    => $model->attributes,
            'product'       => $model->modelable,
        ];

        return $this;
    }


    /**
     * Get attribute value
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (!isset($this->fields[$name])) return;

        return $this->fields[$name];
    }

    /**
     * Get total price
     *
     * @return float
     */
    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Delete the basket item.
     *
     * @return boolean
     */
    public function delete()
    {
        if ($this->model->delete()) {
            Basket::all()->forget($this->index);
            return true;
        }

        return false;
    }

    /**
     * Increment quantity.
     *
     * @param integer $value
     * @return $this
     */
    public function increment(int $value = 1)
    {
        return $this->quantity($this->fields['quantity'] + $value);
    }

    /**
     * Decrement quantity.
     *
     * @param integer $value
     * @return $this
     */
    public function decrement(int $value = 1)
    {
        return $this->quantity($this->fields['quantity'] - $value);
    }

    /**
     * Set quantity value.
     *
     * @param integer $value
     * @return $this
     */
    public function quantity(int $value)
    {
        $this->fields['quantity'] = $value;
        $this->model->update(['quantity' => $this->quantity]);

        return $this;
    }
}

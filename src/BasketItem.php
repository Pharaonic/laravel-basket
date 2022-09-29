<?php

namespace Pharaonic\Laravel\Basket;

use Illuminate\Database\Eloquent\Model;

class BasketItem
{
    private $model;

    private $index;
    private $name;
    private $price;
    private $quantity;
    private $attributes;

    public function __construct(int $index, string $name, float $price, int $quantity, array $attributes = null, Model $model = null)
    {
        $this->index = $index;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->attributes = $attributes;

        $this->model = $model;
    }
}

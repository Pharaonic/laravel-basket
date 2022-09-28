<?php

namespace Pharaonic\Laravel\Basket\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'modelable_type',
        'modelable_id',
        'order_id',
        'name',
        'price',
        'quantity',
        'attributes',
    ];
}

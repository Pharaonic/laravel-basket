<?php

namespace Pharaonic\Laravel\Basket\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BasketItem extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['basket_id', 'name', 'price', 'quantity', 'attributes'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price'         => 'float',
        'quantity'      => 'integer',
        'attributes'    => 'array'
    ];

    /**
     * Get the parent basket model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function basket()
    {
        return $this->belongsTo(Basket::class);
    }

    /**
     * Get the parent modelable model (product).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function modelable()
    {
        return $this->morphTo();
    }
}

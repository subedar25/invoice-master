<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantPriceRange extends Model
{
    use SoftDeletes;

    protected $table = 'restaurant_price_ranges';

    protected $fillable = ['name', 'descriptions'];

    public function clients()
    {
        return $this->hasMany(Client::class, 'restaurant_price_range_id');
    }
}

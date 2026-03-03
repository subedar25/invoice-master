<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantMeal extends Model
{
    use SoftDeletes;

    protected $table = 'restaurant_meals';

    protected $fillable = ['name', 'descriptions', 'parent_meal'];

    public function parent()
    {
        return $this->belongsTo(RestaurantMeal::class, 'parent_meal');
    }

    public function children()
    {
        return $this->hasMany(RestaurantMeal::class, 'parent_meal');
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_restaurant_meal', 'restaurant_meal_id', 'client_id');
    }
}

<?php

namespace Database\Seeders;

use App\Models\RestaurantMeal;
use Illuminate\Database\Seeder;

class RestaurantMealSeeder extends Seeder
{
    public function run(): void
    {
        $meals = [
            'Breakfast',
            'Lunch',
            'Dinner',
        ];

        foreach ($meals as $name) {
            RestaurantMeal::updateOrCreate(
                ['name' => $name],
                ['descriptions' => null, 'parent_meal' => null]
            );
        }
    }
}

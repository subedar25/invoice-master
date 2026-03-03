<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleExpense;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;   

class VehicleExpenseFactory extends Factory
{
    protected $model = VehicleExpense::class;

    public function definition(): array
    {
       return [
            'vehicle_id' => Vehicle::factory(),
            'user_id'    => User::factory(),   
            'date'       => now()->toDateString(),
            'total'      => $this->faker->randomFloat(2, 100, 5000),
            'mileage'    => $this->faker->numberBetween(1000, 2000),
            'type'       => 'fuel',
            'notes'      => $this->faker->sentence(),
        ];
    }
}

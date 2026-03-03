<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'driver_id' => User::factory(), // auto-create driver user
            'vin' => strtoupper($this->faker->bothify('1GCWCFF0H13#####')),
            'description' => $this->faker->sentence(6),
            'active' => $this->faker->boolean(80), // mostly active
            'hitch' => $this->faker->boolean(40),
            'driver_side_sponsor' => $this->faker->company(),
            'passenger_side_sponsor' => $this->faker->company(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    //  * STATES 

    public function inactive(): static
    {
        return $this->state(fn () => [
            'active' => 0,
        ]);
    }

    public function withHitch(): static
    {
        return $this->state(fn () => [
            'hitch' => 1,
        ]);
    }

    public function withoutDriver(): static
    {
        return $this->state(fn () => [
            'driver_id' => null,
        ]);
    }
}

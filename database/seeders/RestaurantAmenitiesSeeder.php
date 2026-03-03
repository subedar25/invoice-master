<?php

namespace Database\Seeders;

use App\Models\ClientAmenity;
use App\Models\ClientTypes;
use Illuminate\Database\Seeder;

class RestaurantAmenitiesSeeder extends Seeder
{
    public function run(): void
    {
        $restaurantType = ClientTypes::where('name', 'Restaurants')->whereNull('parent_id')->first();
        if (!$restaurantType) {
            $this->command->warn('Restaurants client type not found. Run ClientTypeSeeder first.');
            return;
        }

        $amenities = [
            'Full Bar',
            'Outdoor Seating Available',
            'Water View',
            'Reservations Accepted',
            'Beer & Wine Only',
            'Kids Menu',
            'Open for the winter',
            'Beer Only',
            'Appetizers & Snacks',
            'Pet Friendly',
        ];

        foreach ($amenities as $name) {
            ClientAmenity::updateOrCreate(
                ['name' => $name],
                ['client_type_id' => $restaurantType->id]
            );
        }
    }
}

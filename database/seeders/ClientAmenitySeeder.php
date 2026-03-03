<?php

namespace Database\Seeders;

use App\Models\ClientAmenity;
use Illuminate\Database\Seeder;

class ClientAmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            'WiFi',
            'Parking',
            'Pet Friendly',
            'Wheelchair Accessible',
            'Outdoor Seating',
            'Live Music',
            'Full Bar',
            'Breakfast',
            'Lunch',
            'Dinner',
            'Takeout',
            'Delivery',
            'Reservations',
            'Credit Cards',
            'Garden',
            'Pool',
            'Gym',
            'Spa',
        ];

        foreach ($amenities as $name) {
            ClientAmenity::firstOrCreate(['name' => $name]);
        }
    }
}

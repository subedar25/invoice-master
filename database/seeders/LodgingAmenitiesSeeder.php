<?php

namespace Database\Seeders;

use App\Models\ClientAmenity;
use App\Models\ClientTypes;
use Illuminate\Database\Seeder;

class LodgingAmenitiesSeeder extends Seeder
{
    public function run(): void
    {
        $lodgingType = ClientTypes::where('name', 'Lodging')->whereNull('parent_id')->first();
        if (!$lodgingType) {
            $this->command->warn('Lodging client type not found. Run ClientTypeSeeder first.');
            return;
        }

        $amenities = [
            'Adults Only',
            'Air Conditioning',
            'WiFi',
            'Indoor Pool',
            'Outdoor Pool',
            'Waterfront',
            'Golf',
            'Meeting Room',
            'Playground',
            'Fitness Center',
            'Laundry',
            'Pet Friendly',
            'Restaurant',
            'Wheelchair Accessible',
        ];

        foreach ($amenities as $name) {
            ClientAmenity::updateOrCreate(
                ['name' => $name],
                ['client_type_id' => $lodgingType->id]
            );
        }
    }
}

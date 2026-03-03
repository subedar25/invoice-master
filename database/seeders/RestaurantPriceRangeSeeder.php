<?php

namespace Database\Seeders;

use App\Models\RestaurantPriceRange;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestaurantPriceRangeSeeder extends Seeder
{
    public function run(): void
    {
        $ranges = [
            ['name' => '$($5-$10)', 'descriptions' => ''],
            ['name' => '$$($10-$15)', 'descriptions' => ''],
            ['name' => '$$$($15-$20)', 'descriptions' => ''],
            ['name' => '$$$$($20 and up)', 'descriptions' => ''],
        ];

        // Avoid FK constraint errors: null the referencing column on clients before
        // clearing restaurant_price_ranges (required if anything truncates this table).
        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'restaurant_price_range_id')) {
            DB::table('clients')->update(['restaurant_price_range_id' => null]);
        }
        RestaurantPriceRange::query()->delete();

        foreach ($ranges as $range) {
            RestaurantPriceRange::create([
                'name' => $range['name'],
                'descriptions' => $range['descriptions'],
            ]);
        }
    }
}

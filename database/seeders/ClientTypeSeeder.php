<?php

namespace Database\Seeders;

use App\Models\ClientTypes;
use Illuminate\Database\Seeder;

class ClientTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Restaurants',
            'Support Groups',
            'Tours',
            'Classes',
            'ALL',
            'Coffee Shops',
            'Lodging',
            'Shopping',
            'Gallery',
            'Non-Profit',
            'Recreation',
            'Services',
            'Museum',
            'Conservation & Public Reserves',
            'Municipality',
            'Media',
            'Education',
            'Church',
        ];

        foreach ($types as $index => $name) {
            ClientTypes::updateOrCreate(
                ['name' => $name],
                [
                    'parent_id' => null,
                    'status' => 'active',
                    'display_order' => $index,
                ]
            );
        }

        $restaurantSubtypes = [
            'Brewery',
            'Distillery',
            'Casual Dining',
            'Café',
            'Fast Food',
            'Fine Dining',
            'Fish Boil',
            'Confectionery/Ice Cream',
            'Pub/Tavern',
            'Supper Club',
            'Winery',
            'Cider House',
            'Food Truck',
        ];

        $restaurants = ClientTypes::where('name', 'Restaurants')->whereNull('parent_id')->first();
        if ($restaurants) {
            foreach ($restaurantSubtypes as $index => $name) {
                ClientTypes::updateOrCreate(
                    ['name' => $name, 'parent_id' => $restaurants->id],
                    ['status' => 'active', 'display_order' => $index]
                );
            }
        }

        $allSubtypes = [
            'Bakery/Deli',
            'Antiques',
            'Apparel (Children\'s)',
            'Apparel (Men\'s)',
            'Apparel (Women\'s)',
            'Beer/Wine/Liquor',
            'Art',
            'Adventure Rafting',
            'Beauty & Personal Care',
            'Assisted Living/Nursing Home',
            'Bike Rentals',
            'Arcade',
            'Auto/RV',
            'Appliances',
            'Airplane Rides',
            'Beach',
            'Bed & Breakfast',
        ];

        $allType = ClientTypes::where('name', 'ALL')->whereNull('parent_id')->first();
        if ($allType) {
            foreach ($allSubtypes as $index => $name) {
                ClientTypes::updateOrCreate(
                    ['name' => $name, 'parent_id' => $allType->id],
                    ['status' => 'active', 'display_order' => $index]
                );
            }
        }

        $lodgingSubtypes = [
            'Vacation Rentals',
            'Hotel/Motel',
            'Resort',
            'Campground',
        ];

        $lodging = ClientTypes::where('name', 'Lodging')->whereNull('parent_id')->first();
        if ($lodging) {
            foreach ($lodgingSubtypes as $index => $name) {
                ClientTypes::updateOrCreate(
                    ['name' => $name, 'parent_id' => $lodging->id],
                    ['status' => 'active', 'display_order' => $index]
                );
            }
        }

        $shoppingSubtypes = [
            'Books',
            'Bookstore',
            'Boutique',
            'Custom Framing',
            'Department Store',
            'Fine Art/Fine Craft',
            'Furniture',
            'Garden Accents & Supplies',
            'Gifts',
            'Grocery',
            'Convenience',
            'Specialty Foods',
            'Hardware',
            'Hobby/Craft',
            'Holiday',
            'Collectibles',
            'Home Furnishings',
            'Jewelry',
            'Scrapbooking',
            'Sporting Goods',
            'Wellness Items',
            'Boats',
            'Resale Shop',
            'Outdoor Equipment',
            'Home Decor',
            'Toys',
            'Electronics',
            'Kitchen Supplies',
            'Farm Market',
            'Pet Store/Supplies',
            'Music/Instruments',
        ];

        $shopping = ClientTypes::where('name', 'Shopping')->whereNull('parent_id')->first();
        if ($shopping) {
            foreach ($shoppingSubtypes as $index => $name) {
                ClientTypes::updateOrCreate(
                    ['name' => $name, 'parent_id' => $shopping->id],
                    ['status' => 'active', 'display_order' => $index]
                );
            }
        }

        $gallerySubtypes = [
            'Studio Gallery',
            'Pottery',
            'Painting',
            'Sculpture',
            'Jewelry',
            'Mixed Media',
            'Glass',
            'Fabrics/Leather',
            'Demonstrations',
            'Open to the Public',
            'Photography',
            'Wood Working',
            'Home Decor',
            'Outdoor Decor',
            'Paper Arts',
        ];

        $gallery = ClientTypes::where('name', 'Gallery')->whereNull('parent_id')->first();
        if ($gallery) {
            foreach ($gallerySubtypes as $index => $name) {
                ClientTypes::updateOrCreate(
                    ['name' => $name, 'parent_id' => $gallery->id],
                    ['status' => 'active', 'display_order' => $index]
                );
            }
        }

        $servicesSubtypes = [
            'Auto',
            'Youth Groups',
            'Fabrication',
            'Veterinarian',
            'Bank',
            'Pharmacy/Medical Equipment',
            'Cellphone',
            'Landscaping',
            'Carpeting/Flooring/Roofing',
            'Laundromat/Dry Cleaners',
            'Event Planning',
            'Optometrist',
            'Catering',
            'Car Wash',
            'Florist',
            'Waste Management',
            'Boat/Marina Services',
            'Cleaning',
            'Senior Care',
            'Child Care',
            'Fire Station/Department',
            'Police Department',
            'Rental',
            'Construction',
            'Excavating',
            'Gym/Fitness Center',
            'Physical Therapy',
            'Marketing',
            'Photography',
            'Funeral',
            'Wedding Venue',
            'Chiropractic Care',
            'Publishing/Editing',
            'Counseling',
            'Event Venue',
            'Tech Services',
            'Architecture',
            'Wellness',
            'Business Association',
            'Carpentry/Building',
            'Civic Group',
            'Dental',
            'Design Services',
            'Electrical',
            'Environment',
            'Financial Services',
            'Gas',
            'Health Care',
            'Hospital',
            'Human Services',
            'Insurance',
            'Internet',
            'Law',
            'Lawn Care',
            'Library',
            'Manufacturing',
            'Packaged Goods',
            'Plumbing',
            'Post Office',
            'Real Estate',
            'Repair',
            'Salon/Spa',
            'Support Group',
            'Travel/Concierge',
            'Utilities',
        ];

        $services = ClientTypes::where('name', 'Services')->whereNull('parent_id')->first();
        if ($services) {
            foreach ($servicesSubtypes as $index => $name) {
                ClientTypes::updateOrCreate(
                    ['name' => $name, 'parent_id' => $services->id],
                    ['status' => 'active', 'display_order' => $index]
                );
            }
        }

        $mediaSubtypes = [
            'Internet',
            'Magazine',
            'Newspaper',
            'Radio',
            'Television',
        ];

        $media = ClientTypes::where('name', 'Media')->whereNull('parent_id')->first();
        if ($media) {
            foreach ($mediaSubtypes as $index => $name) {
                ClientTypes::updateOrCreate(
                    ['name' => $name, 'parent_id' => $media->id],
                    ['status' => 'active', 'display_order' => $index]
                );
            }
        }

        $educationSubtypes = [
            'Continuing Education',
            'Environment',
            'Literature',
            'School',
        ];

        $education = ClientTypes::where('name', 'Education')->whereNull('parent_id')->first();
        if ($education) {
            foreach ($educationSubtypes as $index => $name) {
                ClientTypes::updateOrCreate(
                    ['name' => $name, 'parent_id' => $education->id],
                    ['status' => 'active', 'display_order' => $index]
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // seeder for create modules and permission
         $this->call([
            ModuleAndPermissionSeeder::class,
             SuperAdminSeeder::class,
             AdminUserSeeder::class,
             ClientTypeSeeder::class,
        ]);
        
        // seeder for create users status
        $this->call(UserStatusSeeder::class);

        // Seeders for notifications
        $this->call([
            NotificationEventSeeder::class,
            NotificationRuleSeeder::class,
        ]);

        $this->call(RestaurantAmenitiesSeeder::class);
        $this->call(RestaurantPriceRangeSeeder::class);
        $this->call(RestaurantMealSeeder::class);
        $this->call(LodgingAmenitiesSeeder::class);

        // Seeders for publication type and publications
        $this->call(PublicationTypeSeeder::class);
        $this->call(PublicationSeeder::class);

        $this->call(SeasonSeeder::class);
    }
}
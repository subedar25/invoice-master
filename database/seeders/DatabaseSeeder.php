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
             CountrySeeder::class,
             StateSeeder::class,
             SuperAdminSeeder::class,
             AdminUserSeeder::class,
        ]);


       
    }
}

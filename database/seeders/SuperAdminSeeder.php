<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash; 

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or find the "Super Admin" role.
      
        $role = Role::firstOrCreate(
            ['name' => 'Super Admin'], // Attributes to search by
            ['guard_name' => 'web']    // Additional attributes if it needs to be created
        );

        // 2. Get all permissions from the database.
        $permissions = Permission::pluck('id')->all();

        // 3. Assign all permissions to the "Super Admin" role.
        $role->syncPermissions($permissions);

        // 4. Create or find the Super Admin user.
        $user = User::firstOrCreate(
            ['email' => 'superadmin@pulsepublication.com'], // Search by email to prevent duplicates
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('password'), // Use Hash::make() for hashing
                'email_verified_at' => now(), // Assume the admin's email is verified
                'created_at' => now(),
                'updated_at' => now(),
                'is_wordpress_user' => 1,
                'registered' => 1,
                'driver' => 1,
                'active' => 1,
                // 'contributor_status' => 'no',
            ]
        );

        // 5. Assign the "Super Admin" role to the user.
        $user->assignRole($role);

        $this->command->info(' Super Admin user and role created/updated successfully.');
    }
}
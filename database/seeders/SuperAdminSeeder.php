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
            ['name' => 'System Admin'], // Attributes to search by
            ['guard_name' => 'web']    // Additional attributes if it needs to be created
        );

        // 2. Get all permissions from the database.
        $permissions = Permission::pluck('id')->all();

        // 3. Assign all permissions to the "System Admin" role.
        $role->syncPermissions($permissions);

        // 4. Create or find the Super Admin user.
        $user = User::firstOrCreate(
            ['email' => 'systemadmin@gmail.com'], // Search by email to prevent duplicates
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('Password@2507'), // Use Hash::make() for hashing
                'email_verified_at' => now(), // Assume the admin's email is verified
                'created_at' => now(),
                'updated_at' => now(),
                'active' => 1,
                'user_type' => 'systemuser',
                // 'contributor_status' => 'no',
            ]
        );

        // 5. Assign the "Super Admin" role to the user.
        $user->assignRole($role);

        $this->command->info(' System Admin user and role created/updated successfully.');
    }
}

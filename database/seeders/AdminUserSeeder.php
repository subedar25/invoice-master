<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Create or get the role
        $role = Role::firstOrCreate([
            'name' => 'Admin User',
            'guard_name' => 'web',
        ]);

        // Get all permissions
        $permissions = Permission::all();

        // Assign all permissions to the role
        $role->syncPermissions($permissions);

        // Create or get the user
        $user = User::firstOrCreate(
            ['email' => 'admin@pulsepublication.com'], // unique identifier
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => bcrypt('password'),
                'driver'     => 1,
                'active'     => 1,
            ]
        );

        // Assign the role to the user
        $user->assignRole($role);
    }
}

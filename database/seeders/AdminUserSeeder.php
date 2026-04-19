<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $organizationId = (int) DB::table('organizations')->orderBy('id')->value('id');
        if ($organizationId < 1) {
            return;
        }

        $role = Role::firstOrCreate([
            'name' => 'Admin User',
            'guard_name' => 'web',
            'organization_id' => $organizationId,
        ]);

        // Get all permissions
        $permissions = Permission::all();

        // Assign all permissions to the role
        $role->syncPermissions($permissions);

        // Create or get the user
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'], // unique identifier
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => bcrypt('password'),
               
                'active'     => 1,
            ]
        );

        // Assign the role to the user
        $user->assignRole($role);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class SystemAdminSeeder extends Seeder
{
    /**
     * Global system administrator: not tied to any organization; can create organizations
     * and manage the whole application (all permissions).
     */
    public function run(): void
    {
        $role = Role::query()
            ->where('name', 'System Admin')
            ->where('guard_name', 'web')
            ->whereNull('organization_id')
            ->first();

        if (! $role) {
            $role = Role::create([
                'name' => 'System Admin',
                'guard_name' => 'web',
                'organization_id' => null,
                'department_id' => null,
                'is_active' => true,
            ]);
        } else {
            $role->update([
                'is_active' => true,
            ]);
        }

        $permissions = Permission::query()->pluck('id')->all();
        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::firstOrCreate(
            ['email' => 'systemadmin@gmail.com'],
            [
                'first_name' => 'System',
                'last_name' => 'Admin',
                'password' => Hash::make('Password@2507'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'active' => true,
                'user_type' => 'systemuser',
            ]
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        $this->command->info('System Admin user and global role created or updated successfully.');
    }
}

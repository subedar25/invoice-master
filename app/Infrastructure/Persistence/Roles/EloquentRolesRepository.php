<?php

namespace App\Infrastructure\Persistence\Roles;

use App\Core\Roles\Contracts\RolesRepository;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB; // <-- 1. Import the DB facade
use Spatie\Permission\PermissionRegistrar;

class EloquentRolesRepository implements RolesRepository
{
    public function find(int $id): Role
    {
        return Role::findOrFail($id);
    }

    public function create(array $data): Role
    {
        // 2. Wrap the operation in a database transaction
        return DB::transaction(function () use ($data) {
           
           $role = Role::create([
            'name' => $data['name'],
            'department_id' => $data['department_id'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

         $permissions = Permission::where('is_active', true)
            ->whereIn('id', $data['permissions'])
            ->get();
            if (!empty($permissions)) {
                $role->givePermissionTo($permissions);
            }

            // Ensure permission cache reflects new role assignments
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return $role;
        });
    }

    public function update(int $id, array $data): Role
    {
       
        return DB::transaction(function () use ($id, $data) {
            $role = Role::findOrFail($id);

            if (array_key_exists('permissions', $data) && is_array($data['permissions'])) {
                $permissions = Permission::where('is_active', true)
                    ->whereIn('id', $data['permissions'])
                    ->get();
                $role->syncPermissions($permissions);
            }

            // Ensure permission cache reflects updated role assignments
            app(PermissionRegistrar::class)->forgetCachedPermissions();
          
            $role->update([
            'name' => $data['name'],
            'department_id' => $data['department_id'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
            
            return $role;
        });
    }

    public function delete(int $id): void
    {
        // No transaction needed here as it's a single delete operation.
        Role::findOrFail($id)->delete();
    }
}

<?php

namespace App\Infrastructure\Persistence\Roles;

use App\Core\Roles\Contracts\RolesRepository;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RoleInvoiceDepartmentScope;
use App\Models\User;
use App\Support\CurrentOrganization;
use Illuminate\Support\Facades\Auth;
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
            $organizationId = CurrentOrganization::idOrAbort();

            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web',
                'organization_id' => $organizationId,
                'department_id' => $data['department_id'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $viewer = Auth::user();
            $assignableIds = Permission::assignablePermissionIdsFor($viewer);
            $ids = array_values(array_intersect(
                array_map('intval', $data['permissions']),
                $assignableIds
            ));
            $permissions = Permission::where('is_active', true)
                ->whereIn('id', $ids)
                ->get();
            if ($permissions->isNotEmpty()) {
                $role->givePermissionTo($permissions);
            }

            $role->load('permissions');
            $permissionIdByName = Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('name', ['list-invoices', 'approve-invoice'])
                ->pluck('id', 'name')
                ->map(fn ($id) => (int) $id)
                ->all();
            RoleInvoiceDepartmentScope::syncForRole($role, $data['invoice_department_scopes'] ?? [], $permissionIdByName);

            // Ensure permission cache reflects new role assignments
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return $role;
        });
    }

    public function update(int $id, array $data): Role
    {
       
        return DB::transaction(function () use ($id, $data) {
            $role = Role::findOrFail($id);
            $orgId = CurrentOrganization::id();
            if ($orgId === null || (int) $role->organization_id !== $orgId) {
                abort(403);
            }

            if (array_key_exists('permissions', $data) && is_array($data['permissions'])) {
                $viewer = Auth::user();
                $assignableIds = Permission::assignablePermissionIdsFor($viewer);
                $submitted = array_map('intval', $data['permissions']);

                if ($viewer instanceof User && $viewer->isSystemUser()) {
                    $mergedIds = $submitted;
                } else {
                    $preservedIds = $role->permissions()
                        ->where('is_active', true)
                        ->whereNotIn('id', $assignableIds)
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->all();
                    $mergedIds = array_values(array_unique(array_merge($submitted, $preservedIds)));
                }

                $permissions = Permission::where('is_active', true)
                    ->whereIn('id', $mergedIds)
                    ->get();
                $role->syncPermissions($permissions);
            }

            $role->refresh();
            $role->load('permissions');
            $permissionIdByName = Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('name', ['list-invoices', 'approve-invoice'])
                ->pluck('id', 'name')
                ->map(fn ($id) => (int) $id)
                ->all();
            RoleInvoiceDepartmentScope::syncForRole($role, $data['invoice_department_scopes'] ?? [], $permissionIdByName);

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
        $role = Role::findOrFail($id);
        $orgId = CurrentOrganization::id();
        if ($orgId === null || (int) $role->organization_id !== $orgId) {
            abort(403);
        }
        $role->delete();
    }
}

<?php

namespace App\Infrastructure\Persistence\Roles;

use App\Core\Roles\Contracts\RolesRepository;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Department;
use App\Models\RoleInvoiceDepartmentScope;
use App\Models\User;
use App\Support\CurrentOrganization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- 1. Import the DB facade
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
                ->whereIn('name', ['list-users', 'list-invoices', 'approve-invoice'])
                ->pluck('id', 'name')
                ->map(fn ($id) => (int) $id)
                ->all();
            $scopePayload = array_merge(
                $data['invoice_department_scopes'] ?? [],
                $data['user_department_scopes'] ?? []
            );
            RoleInvoiceDepartmentScope::syncForRole($role, $scopePayload, $permissionIdByName);

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
                ->whereIn('name', ['list-users', 'list-invoices', 'approve-invoice'])
                ->pluck('id', 'name')
                ->map(fn ($id) => (int) $id)
                ->all();
            $scopePayload = array_merge(
                $data['invoice_department_scopes'] ?? [],
                $data['user_department_scopes'] ?? []
            );
            RoleInvoiceDepartmentScope::syncForRole($role, $scopePayload, $permissionIdByName);

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

    public function queryForDatatable(?int $organizationId, mixed $departmentId = null, ?string $search = null): Builder
    {
        $query = Role::with(['permissions.module', 'department']);

        if ($organizationId === null) {
            $query->whereRaw('1 = 0');
        } else {
            $query->where('organization_id', $organizationId);
        }

        if (!empty($departmentId)) {
            if (is_array($departmentId)) {
                $query->whereIn('department_id', $departmentId);
            } else {
                $query->where('department_id', $departmentId);
            }
        }

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query;
    }

    public function getDepartmentsForOrganization(?int $organizationId): Collection
    {
        if ($organizationId === null) {
            return collect();
        }

        return Department::where('organization_id', $organizationId)
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    public function getDepartmentRecordsForOrganization(?int $organizationId): Collection
    {
        if ($organizationId === null) {
            return collect();
        }

        return Department::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getActiveAssignablePermissionsGrouped(?User $viewer): Collection
    {
        return $this->getActiveAssignablePermissions($viewer)
            ->groupBy(fn ($permission) => optional($permission->module)->name ?? 'Uncategorized');
    }

    public function getActiveAssignablePermissions(?User $viewer): Collection
    {
        return Permission::with('module')
            ->where('is_active', true)
            ->assignableForViewer($viewer)
            ->orderBy('name')
            ->get();
    }

    public function toggleActive(int $id): Role
    {
        $role = Role::findOrFail($id);
        $role->is_active = ! (bool) $role->is_active;
        $role->save();

        return $role;
    }

    public function bulkDeleteForOrganization(int $organizationId, array $ids): int
    {
        return Role::where('organization_id', $organizationId)
            ->whereIn('id', array_map('intval', $ids))
            ->delete();
    }
}

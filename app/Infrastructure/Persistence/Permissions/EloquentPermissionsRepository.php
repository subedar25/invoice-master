<?php
namespace App\Infrastructure\Persistence\Permissions;

use App\Core\Permissions\Contracts\PermissionsRepository;
use App\Models\Module;
use App\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class EloquentPermissionsRepository implements PermissionsRepository
{
    public function find(int $id): Permission
    {
        return Permission::findOrFail($id);
    }

    public function create(array $data): Permission
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $guardName = $data['guard_name'] ?? 'web';

        $permission = Permission::create([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'module_id' => $data['module_id'],
            'slug' => $data['slug'],
            'guard_name' => $guardName,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return $permission;
    }

    public function update(int $id, array $data): Permission
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $permission = Permission::findOrFail($id);
        $permission->update([
            'name' => $data['name'],
            'display_name' => $data['display_name'] ?? null,
            'module_id' => $data['module_id'],
            'slug' => $data['slug'],
            'guard_name' => $data['guard_name'] ?? $permission->guard_name,
            'is_active' => array_key_exists('is_active', $data) && $data['is_active'] !== null
                ? (bool) $data['is_active']
                : (bool) $permission->is_active,
        ]);

        return $permission;
    }

    public function delete(int $id): void
    {
        Permission::findOrFail($id)->delete();
    }
}

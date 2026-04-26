<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleInvoiceDepartmentScope extends Model
{
    protected $fillable = [
        'role_id',
        'permission_id',
        'all_departments',
        'own_invoices',
        'reporting_only',
        'department_ids',
    ];

    protected $casts = [
        'all_departments' => 'boolean',
        'own_invoices' => 'boolean',
        'reporting_only' => 'boolean',
        'department_ids' => 'array',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * @param  array<string, array{all_departments?: bool, own_invoices?: bool, reporting_only?: bool, department_ids?: array<int>|null}>  $scopesByPermissionName
     */
    public static function syncForRole(Role $role, array $scopesByPermissionName, array $permissionIdByName): void
    {
        static::query()->where('role_id', $role->id)->delete();

        foreach (['list-invoices', 'approve-invoice'] as $permName) {
            $permissionId = $permissionIdByName[$permName] ?? null;
            if (! $permissionId || ! $role->hasPermissionTo($permName)) {
                continue;
            }

            $payload = $scopesByPermissionName[$permName] ?? [];
            $all = (bool) ($payload['all_departments'] ?? true);
            $ownOnly = $permName === 'list-invoices' && (bool) ($payload['own_invoices'] ?? false);
            $reportingOnly = in_array($permName, ['list-invoices', 'approve-invoice'], true)
                && (bool) ($payload['reporting_only'] ?? false);
            $ids = isset($payload['department_ids']) && is_array($payload['department_ids'])
                ? array_values(array_unique(array_filter(array_map('intval', $payload['department_ids']))))
                : [];

            static::query()->create([
                'role_id' => $role->id,
                'permission_id' => $permissionId,
                'all_departments' => ($ownOnly || $reportingOnly) ? false : $all,
                'own_invoices' => $ownOnly,
                'reporting_only' => $reportingOnly,
                'department_ids' => ($ownOnly || $reportingOnly || $all) ? null : $ids,
            ]);
        }
    }

    /**
     * @return array<string, array{all_departments: bool, own_invoices: bool, reporting_only: bool, department_ids: array<int>}>
     */
    public static function mapByPermissionNameForRole(int $roleId): array
    {
        $rows = static::query()
            ->where('role_id', $roleId)
            ->with('permission:id,name')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $name = $row->permission?->name;
            if (! $name) {
                continue;
            }
            $out[$name] = [
                'all_departments' => (bool) $row->all_departments,
                'own_invoices' => (bool) ($row->own_invoices ?? false),
                'reporting_only' => (bool) ($row->reporting_only ?? false),
                'department_ids' => array_values(array_map('intval', $row->department_ids ?? [])),
            ];
        }

        return $out;
    }
}

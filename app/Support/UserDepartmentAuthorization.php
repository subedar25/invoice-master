<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\RoleInvoiceDepartmentScope;
use App\Models\User;

class UserDepartmentAuthorization
{
    public const LIST_USERS = 'list-users';

    /**
     * @return array<string, int>
     */
    public static function userPermissionIdsByName(): array
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', [self::LIST_USERS])
            ->pluck('id', 'name')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function userHasListInOrganization(User $user, ?int $organizationId): bool
    {
        if ($organizationId === null) {
            return false;
        }

        if (self::systemUserMayListUsers($user)) {
            return true;
        }

        if ($user->hasDirectPermission(self::LIST_USERS)) {
            return true;
        }

        return $user->hasPermissionInOrganization(self::LIST_USERS, $organizationId);
    }

    /**
     * @return null|array<int> null = no extra department restriction; [] = none visible
     */
    public static function mergedListDepartmentRestriction(User $user, ?int $organizationId): ?array
    {
        if ($organizationId === null) {
            return [];
        }

        if (self::systemUserMayListUsers($user)) {
            return null;
        }

        if ($user->hasDirectPermission(self::LIST_USERS)) {
            return null;
        }

        $listId = Permission::query()
            ->where('name', self::LIST_USERS)
            ->where('guard_name', 'web')
            ->value('id');
        if (! $listId) {
            return [];
        }

        $orgRoles = $user->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        $hasListInOrg = $orgRoles->contains(fn ($role) => $role->hasPermissionTo(self::LIST_USERS));
        if (! $hasListInOrg) {
            return [];
        }

        $roleIds = $orgRoles->pluck('id');
        $scopes = RoleInvoiceDepartmentScope::query()
            ->whereIn('role_id', $roleIds)
            ->where('permission_id', $listId)
            ->get();

        if ($scopes->isEmpty()) {
            return null;
        }

        $merged = [];
        $hasNonOwnScope = false;
        $hasReportingOnlyScope = false;
        foreach ($scopes as $scope) {
            if ((bool) ($scope->own_invoices ?? false)) {
                continue;
            }
            if ((bool) ($scope->reporting_only ?? false)) {
                $hasReportingOnlyScope = true;
                continue;
            }
            if ($scope->all_departments) {
                return null;
            }
            $hasNonOwnScope = true;
            foreach ($scope->department_ids ?? [] as $id) {
                $merged[(int) $id] = true;
            }
        }

        if (! $hasNonOwnScope) {
            if ($hasReportingOnlyScope) {
                return null;
            }
            return [];
        }

        return array_map('intval', array_keys($merged));
    }

    public static function listOwnUsersOnly(User $user, ?int $organizationId): bool
    {
        if ($organizationId === null) {
            return false;
        }

        if (self::systemUserMayListUsers($user) || $user->hasDirectPermission(self::LIST_USERS)) {
            return false;
        }

        $listId = Permission::query()
            ->where('name', self::LIST_USERS)
            ->where('guard_name', 'web')
            ->value('id');
        if (! $listId) {
            return false;
        }

        $orgRoles = $user->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        $hasListInOrg = $orgRoles->contains(fn ($role) => $role->hasPermissionTo(self::LIST_USERS));
        if (! $hasListInOrg) {
            return false;
        }

        $scopes = RoleInvoiceDepartmentScope::query()
            ->whereIn('role_id', $orgRoles->pluck('id'))
            ->where('permission_id', $listId)
            ->get();

        if ($scopes->isEmpty()) {
            return false;
        }

        $hasOwnOnly = false;
        foreach ($scopes as $scope) {
            if ((bool) ($scope->own_invoices ?? false)) {
                $hasOwnOnly = true;
                continue;
            }
            if ((bool) ($scope->reporting_only ?? false)) {
                return false;
            }
            if ((bool) $scope->all_departments) {
                return false;
            }
            if (! empty($scope->department_ids ?? [])) {
                return false;
            }
        }

        return $hasOwnOnly;
    }

    public static function listReportingUsersOnly(User $user, ?int $organizationId): bool
    {
        if ($organizationId === null) {
            return false;
        }

        if (self::systemUserMayListUsers($user) || $user->hasDirectPermission(self::LIST_USERS)) {
            return false;
        }

        $listId = Permission::query()
            ->where('name', self::LIST_USERS)
            ->where('guard_name', 'web')
            ->value('id');
        if (! $listId) {
            return false;
        }

        $orgRoles = $user->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        $hasListInOrg = $orgRoles->contains(fn ($role) => $role->hasPermissionTo(self::LIST_USERS));
        if (! $hasListInOrg) {
            return false;
        }

        $scopes = RoleInvoiceDepartmentScope::query()
            ->whereIn('role_id', $orgRoles->pluck('id'))
            ->where('permission_id', $listId)
            ->get();

        if ($scopes->isEmpty()) {
            return false;
        }

        $hasReportingOnly = false;
        foreach ($scopes as $scope) {
            if ((bool) ($scope->reporting_only ?? false)) {
                $hasReportingOnly = true;
                continue;
            }
            if ((bool) ($scope->own_invoices ?? false)) {
                continue;
            }
            if ((bool) $scope->all_departments) {
                return false;
            }
            if (! empty($scope->department_ids ?? [])) {
                return false;
            }
        }

        return $hasReportingOnly;
    }

    /**
     * @return null|array<int> null means all roles allowed
     */
    public static function mergedListRoleRestriction(User $user, ?int $organizationId): ?array
    {
        if ($organizationId === null) {
            return [];
        }

        if (self::systemUserMayListUsers($user)) {
            return null;
        }

        if ($user->hasDirectPermission(self::LIST_USERS)) {
            return null;
        }

        $listId = Permission::query()
            ->where('name', self::LIST_USERS)
            ->where('guard_name', 'web')
            ->value('id');
        if (! $listId) {
            return [];
        }

        $orgRoles = $user->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        $hasListInOrg = $orgRoles->contains(fn ($role) => $role->hasPermissionTo(self::LIST_USERS));
        if (! $hasListInOrg) {
            return [];
        }

        $scopes = RoleInvoiceDepartmentScope::query()
            ->whereIn('role_id', $orgRoles->pluck('id'))
            ->where('permission_id', $listId)
            ->get();

        if ($scopes->isEmpty()) {
            return null;
        }

        $merged = [];
        foreach ($scopes as $scope) {
            $ids = array_values(array_unique(array_filter(array_map('intval', $scope->role_ids ?? []))));
            if ($ids === []) {
                return null;
            }
            foreach ($ids as $id) {
                $merged[$id] = true;
            }
        }

        return array_map('intval', array_keys($merged));
    }

    /**
     * Direct + subordinate reportees.
     *
     * @return array<int>
     */
    public static function reportingAndSubordinateUserIds(User $user): array
    {
        $directIds = User::query()
            ->where('reporting_manager_id', $user->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($directIds === []) {
            return [];
        }

        $subordinateIds = User::query()
            ->whereIn('reporting_manager_id', $directIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return array_values(array_unique(array_merge($directIds, $subordinateIds)));
    }

    private static function systemUserMayListUsers(User $user): bool
    {
        return $user->isSystemUser() && $user->can(self::LIST_USERS);
    }
}

<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\RoleInvoiceDepartmentScope;
use App\Models\User;

class InvoiceDepartmentAuthorization
{
    public const LIST_INVOICES = 'list-invoices';

    public const APPROVE_INVOICE = 'approve-invoice';

    /**
     * @return array<string, int>
     */
    public static function invoicePermissionIdsByName(): array
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', [self::LIST_INVOICES, self::APPROVE_INVOICE])
            ->pluck('id', 'name')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function userHasListInOrganization(User $user, ?int $organizationId): bool
    {
        if ($organizationId === null) {
            return false;
        }

        if (self::systemUserMayListInvoices($user)) {
            return true;
        }

        if ($user->hasDirectPermission(self::LIST_INVOICES)) {
            return true;
        }

        return $user->hasPermissionInOrganization(self::LIST_INVOICES, $organizationId);
    }

    /**
     * @return null|array<int> null = no extra department restriction; [] = none visible
     */
    public static function mergedListDepartmentRestriction(User $user, ?int $organizationId): ?array
    {
        if ($organizationId === null) {
            return [];
        }

        if (self::systemUserMayListInvoices($user)) {
            return null;
        }

        if ($user->hasDirectPermission(self::LIST_INVOICES)) {
            return null;
        }

        $listId = Permission::query()
            ->where('name', self::LIST_INVOICES)
            ->where('guard_name', 'web')
            ->value('id');
        if (! $listId) {
            return [];
        }

        $orgRoles = $user->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        $hasListInOrg = $orgRoles->contains(fn ($role) => $role->hasPermissionTo(self::LIST_INVOICES));
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
            if ((bool) ($scope->reporting_only ?? false)) {
                $hasReportingOnlyScope = true;
                continue;
            }
            if ((bool) ($scope->own_invoices ?? false)) {
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

    public static function listOwnInvoicesOnly(User $user, ?int $organizationId): bool
    {
        if ($organizationId === null) {
            return false;
        }

        if (self::systemUserMayListInvoices($user) || $user->hasDirectPermission(self::LIST_INVOICES)) {
            return false;
        }

        $listId = Permission::query()
            ->where('name', self::LIST_INVOICES)
            ->where('guard_name', 'web')
            ->value('id');
        if (! $listId) {
            return false;
        }

        $orgRoles = $user->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        $hasListInOrg = $orgRoles->contains(fn ($role) => $role->hasPermissionTo(self::LIST_INVOICES));
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
            if ((bool) ($scope->reporting_only ?? false)) {
                return false;
            }
            if ((bool) ($scope->own_invoices ?? false)) {
                $hasOwnOnly = true;
                continue;
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

    public static function listReportingInvoicesOnly(User $user, ?int $organizationId): bool
    {
        if ($organizationId === null) {
            return false;
        }

        if (self::systemUserMayListInvoices($user) || $user->hasDirectPermission(self::LIST_INVOICES)) {
            return false;
        }

        $listId = Permission::query()
            ->where('name', self::LIST_INVOICES)
            ->where('guard_name', 'web')
            ->value('id');
        if (! $listId) {
            return false;
        }

        $orgRoles = $user->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        $hasListInOrg = $orgRoles->contains(fn ($role) => $role->hasPermissionTo(self::LIST_INVOICES));
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
     * @return array<int>
     */
    public static function reportingUserIds(User $user, bool $includeSubordinates = false): array
    {
        $directIds = User::query()
            ->where('reporting_manager_id', $user->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (! $includeSubordinates || $directIds === []) {
            return $directIds;
        }

        $all = $directIds;
        $frontier = $directIds;
        while ($frontier !== []) {
            $next = User::query()
                ->whereIn('reporting_manager_id', $frontier)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $next = array_values(array_diff($next, $all));
            if ($next === []) {
                break;
            }

            $all = array_values(array_unique(array_merge($all, $next)));
            $frontier = $next;
        }

        return $all;
    }

    public static function listReportingInvoicesIncludeSubordinates(User $user, ?int $organizationId): bool
    {
        if (! self::listReportingInvoicesOnly($user, $organizationId)) {
            return false;
        }

        $listId = Permission::query()
            ->where('name', self::LIST_INVOICES)
            ->where('guard_name', 'web')
            ->value('id');
        if (! $listId || $organizationId === null) {
            return false;
        }

        $orgRoles = $user->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        $scopes = RoleInvoiceDepartmentScope::query()
            ->whereIn('role_id', $orgRoles->pluck('id'))
            ->where('permission_id', $listId)
            ->get();

        return $scopes->contains(fn ($scope) => (bool) ($scope->reporting_only ?? false) && (bool) ($scope->own_invoices ?? false));
    }

    /**
     * @return null|array<int, string> null means no status restriction
     */
    public static function mergedListStatusRestriction(User $user, ?int $organizationId): ?array
    {
        if ($organizationId === null) {
            return ['pending', 'in_process', 'approve', 'complete'];
        }

        if (self::systemUserMayListInvoices($user)) {
            return null;
        }

        if ($user->hasDirectPermission(self::LIST_INVOICES)) {
            return null;
        }

        $listId = Permission::query()
            ->where('name', self::LIST_INVOICES)
            ->where('guard_name', 'web')
            ->value('id');
        if (! $listId) {
            return ['pending', 'in_process', 'approve', 'complete'];
        }

        $orgRoles = $user->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        $hasListInOrg = $orgRoles->contains(fn ($role) => $role->hasPermissionTo(self::LIST_INVOICES));
        if (! $hasListInOrg) {
            return ['pending', 'in_process', 'approve', 'complete'];
        }

        $scopes = RoleInvoiceDepartmentScope::query()
            ->whereIn('role_id', $orgRoles->pluck('id'))
            ->where('permission_id', $listId)
            ->get();
        if ($scopes->isEmpty()) {
            return null;
        }

        $allowed = [];
        $allStatuses = ['pending', 'in_process', 'approve', 'complete'];
        foreach ($scopes as $scope) {
            $statuses = array_values(array_unique(array_filter(array_map(
                static fn ($s) => strtolower(trim((string) $s)),
                $scope->statuses ?? $allStatuses
            ))));
            if ($statuses === []) {
                $statuses = $allStatuses;
            }
            foreach ($statuses as $status) {
                $allowed[$status] = true;
            }
        }

        if ($allowed === []) {
            return $allStatuses;
        }

        return array_values(array_intersect($allStatuses, array_keys($allowed)));
    }

    public static function approveReportingInvoicesIncludeSubordinates(User $user, ?int $organizationId): bool
    {
        if ($organizationId === null) {
            return false;
        }

        if (self::systemUserMayApproveInvoice($user) || $user->hasDirectPermission(self::APPROVE_INVOICE)) {
            return false;
        }

        $approveId = Permission::query()
            ->where('name', self::APPROVE_INVOICE)
            ->where('guard_name', 'web')
            ->value('id');
        if (! $approveId) {
            return false;
        }

        $orgRoles = $user->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        $hasApproveInOrg = $orgRoles->contains(fn ($role) => $role->hasPermissionTo(self::APPROVE_INVOICE));
        if (! $hasApproveInOrg) {
            return false;
        }

        $scopes = RoleInvoiceDepartmentScope::query()
            ->whereIn('role_id', $orgRoles->pluck('id'))
            ->where('permission_id', $approveId)
            ->get();

        return $scopes->contains(fn ($scope) => (bool) ($scope->reporting_only ?? false) && (bool) ($scope->own_invoices ?? false));
    }

    public static function canApproveInvoice(
        User $user,
        ?int $organizationId,
        ?int $invoiceDepartmentId,
        ?int $invoiceCreatedByUserId = null
    ): bool
    {
        if ($organizationId === null) {
            return false;
        }

        if (self::systemUserMayApproveInvoice($user)) {
            return true;
        }

        if ($user->hasDirectPermission(self::APPROVE_INVOICE)) {
            return true;
        }

        $approveId = Permission::query()
            ->where('name', self::APPROVE_INVOICE)
            ->where('guard_name', 'web')
            ->value('id');
        if (! $approveId) {
            return false;
        }

        $roles = $user->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        $invoiceCreator = null;
        if ($invoiceCreatedByUserId !== null) {
            $invoiceCreator = User::query()
                ->select(['id', 'reporting_manager_id'])
                ->find((int) $invoiceCreatedByUserId);
        }

        foreach ($roles as $role) {
            if (! $role->hasPermissionTo(self::APPROVE_INVOICE)) {
                continue;
            }

            $scope = RoleInvoiceDepartmentScope::query()
                ->where('role_id', $role->id)
                ->where('permission_id', $approveId)
                ->first();

            if ($scope && (bool) ($scope->reporting_only ?? false)) {
                $canIncludeSubordinates = (bool) ($scope->own_invoices ?? false);
                if ($invoiceCreator) {
                    $allowedCreatorIds = self::reportingUserIds($user, $canIncludeSubordinates);
                    if (in_array((int) $invoiceCreator->id, $allowedCreatorIds, true)) {
                        return true;
                    }
                }

                continue;
            }

            if (! $scope || $scope->all_departments) {
                return true;
            }

            $ids = array_map('intval', $scope->department_ids ?? []);
            if ($invoiceDepartmentId !== null && in_array((int) $invoiceDepartmentId, $ids, true)) {
                return true;
            }
        }

        return false;
    }

    public static function canViewInvoice(
        User $user,
        ?int $organizationId,
        ?int $invoiceDepartmentId,
        ?int $invoiceCreatedByUserId = null
    ): bool
    {
        if (! self::userHasListInOrganization($user, $organizationId)) {
            return false;
        }

        if (
            self::listOwnInvoicesOnly($user, $organizationId)
            && $invoiceCreatedByUserId !== null
            && (int) $invoiceCreatedByUserId === (int) $user->id
        ) {
            return true;
        }

        if (
            self::listReportingInvoicesOnly($user, $organizationId)
            && $invoiceCreatedByUserId !== null
            && in_array(
                (int) $invoiceCreatedByUserId,
                self::reportingUserIds($user, self::listReportingInvoicesIncludeSubordinates($user, $organizationId)),
                true
            )
        ) {
            return true;
        }

        $restriction = self::mergedListDepartmentRestriction($user, $organizationId);
        if ($restriction === null) {
            return true;
        }
        if ($restriction === []) {
            return false;
        }
        if ($invoiceDepartmentId === null) {
            return false;
        }

        return in_array((int) $invoiceDepartmentId, $restriction, true);
    }

    private static function systemUserMayListInvoices(User $user): bool
    {
        return $user->isSystemUser() && $user->can(self::LIST_INVOICES);
    }

    private static function systemUserMayApproveInvoice(User $user): bool
    {
        return $user->isSystemUser() && $user->can(self::APPROVE_INVOICE);
    }
}

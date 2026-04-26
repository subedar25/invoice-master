<?php
namespace App\Core\Roles\Contracts;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface RolesRepository
{
    public function find(int $id): Role;

    public function create(array $data): Role;

    public function update(int $id, array $data): Role;

    public function delete(int $id): void;

    public function queryForDatatable(?int $organizationId, mixed $departmentId = null, ?string $search = null): Builder;

    public function getDepartmentsForOrganization(?int $organizationId): Collection;

    public function getDepartmentRecordsForOrganization(?int $organizationId): Collection;

    public function getRoleRecordsForOrganization(?int $organizationId): Collection;

    public function getActiveAssignablePermissionsGrouped(?User $viewer): Collection;

    public function getActiveAssignablePermissions(?User $viewer): Collection;

    public function toggleActive(int $id): Role;

    public function bulkDeleteForOrganization(int $organizationId, array $ids): int;
}

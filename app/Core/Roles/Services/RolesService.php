<?php

namespace App\Core\Roles\Services;

use App\Core\Roles\Contracts\RolesRepository;


class RolesService
{
   
    public function __construct(
        private RolesRepository $roles
    ) {}

    public function create(array $data)
    {
        return $this->roles->create($data);
    }

    public function get(int $id)
    {
        return $this->roles->find($id);
    }

    public function update(int $id, array $data)
    {
        return $this->roles->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->roles->delete($id);
    }

    public function queryForDatatable(?int $organizationId, mixed $departmentId = null, ?string $search = null)
    {
        return $this->roles->queryForDatatable($organizationId, $departmentId, $search);
    }

    public function getDepartmentsForOrganization(?int $organizationId)
    {
        return $this->roles->getDepartmentsForOrganization($organizationId);
    }

    public function getDepartmentRecordsForOrganization(?int $organizationId)
    {
        return $this->roles->getDepartmentRecordsForOrganization($organizationId);
    }

    public function getActiveAssignablePermissionsGrouped($viewer)
    {
        return $this->roles->getActiveAssignablePermissionsGrouped($viewer);
    }

    public function getActiveAssignablePermissions($viewer)
    {
        return $this->roles->getActiveAssignablePermissions($viewer);
    }

    public function toggleActive(int $id)
    {
        return $this->roles->toggleActive($id);
    }

    public function bulkDeleteForOrganization(int $organizationId, array $ids): int
    {
        return $this->roles->bulkDeleteForOrganization($organizationId, $ids);
    }

}

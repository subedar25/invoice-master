<?php
namespace App\Core\Permissions\Contracts;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PermissionsRepository
{
    public function find(int $id): Permission;

    public function create(array $data): Permission;

    public function update(int $id, array $data): Permission;

    public function delete(int $id): void;

    public function getAllModules(): Collection;

    public function getModuleNameOptions(): Collection;

    public function paginateWithModuleLatest(int $perPage = 200): LengthAwarePaginator;

    public function toggleActive(int $id): Permission;

    public function bulkDelete(array $ids): int;
}

<?php

namespace App\Core\OrganizationType\Contracts;

use App\Models\OrganizationType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OrganizationTypeRepository
{
    public function find(int $id): OrganizationType;

    public function findWithTrashed(int $id): OrganizationType;

    public function create(array $data): OrganizationType;

    public function update(int $id, array $data): OrganizationType;

    public function delete(int $id): void;

    /**
     * @return LengthAwarePaginator<OrganizationType>
     */
    public function list(string $search, string $statusFilter, string $sortField, string $sortDirection, int $perPage, int $page = 1): LengthAwarePaginator;

    /**
     * Active types for parent dropdown, optionally excluding one id.
     *
     * @return Collection<int, OrganizationType>
     */
    public function getParentOptions(?int $excludeId = null): Collection;

    /**
     * Single record for view modal with parent and children loaded.
     */
    public function getForView(int $id): ?OrganizationType;
}

<?php

namespace App\Core\OrganizationType\Services;

use App\Core\OrganizationType\Contracts\OrganizationTypeRepository;
use App\Models\OrganizationType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrganizationTypeService
{
    public function __construct(
        private OrganizationTypeRepository $organizationTypes
    ) {}

    public function find(int $id): OrganizationType
    {
        return $this->organizationTypes->find($id);
    }

    public function findWithTrashed(int $id): OrganizationType
    {
        return $this->organizationTypes->findWithTrashed($id);
    }

    public function create(array $data): OrganizationType
    {
        return $this->organizationTypes->create($data);
    }

    public function update(int $id, array $data): OrganizationType
    {
        return $this->organizationTypes->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->organizationTypes->delete($id);
    }

    public function list(string $search, string $statusFilter, string $sortField, string $sortDirection, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->organizationTypes->list($search, $statusFilter, $sortField, $sortDirection, $perPage, $page);
    }

    /** @return Collection<int, OrganizationType> */
    public function getParentOptions(?int $excludeId = null): Collection
    {
        return $this->organizationTypes->getParentOptions($excludeId);
    }

    public function getForView(int $id): ?OrganizationType
    {
        return $this->organizationTypes->getForView($id);
    }
}

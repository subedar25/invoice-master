<?php

namespace App\Core\Publication\Services;

use App\Core\Publication\Contracts\PublicationRepository;
use App\Models\Publication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PublicationService
{
    public function __construct(
        private PublicationRepository $publications
    ) {}

    public function find(int $id): Publication
    {
        return $this->publications->find($id);
    }

    public function findWithTrashed(int $id): Publication
    {
        return $this->publications->findWithTrashed($id);
    }

    public function create(array $data): Publication
    {
        return $this->publications->create($data);
    }

    public function update(int $id, array $data): Publication
    {
        return $this->publications->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->publications->delete($id);
    }

    public function list(string $search, string $sortField, string $sortDirection, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->publications->list($search, $sortField, $sortDirection, $perPage, $page);
    }

    /** @return Collection<int, \App\Models\PublicationType> */
    public function getPublicationTypeOptions(): Collection
    {
        return $this->publications->getPublicationTypeOptions();
    }

    public function getForView(int $id): ?Publication
    {
        return $this->publications->getForView($id);
    }
}

<?php

namespace App\Core\Publication\Contracts;

use App\Models\Publication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PublicationRepository
{
    public function find(int $id): Publication;

    public function findWithTrashed(int $id): Publication;

    public function create(array $data): Publication;

    public function update(int $id, array $data): Publication;

    public function delete(int $id): void;

    /** @return LengthAwarePaginator<Publication> */
    public function list(string $search, string $sortField, string $sortDirection, int $perPage, int $page = 1): LengthAwarePaginator;

    /** @return Collection<int, PublicationType> */
    public function getPublicationTypeOptions(): Collection;

    public function getForView(int $id): ?Publication;
}

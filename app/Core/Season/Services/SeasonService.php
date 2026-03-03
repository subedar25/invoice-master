<?php

namespace App\Core\Season\Services;

use App\Core\Season\Contracts\SeasonRepository;
use App\Models\Season;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SeasonService
{
    public function __construct(
        private SeasonRepository $seasons
    ) {}

    public function find(int $id): Season
    {
        return $this->seasons->find($id);
    }

    public function findWithTrashed(int $id): Season
    {
        return $this->seasons->findWithTrashed($id);
    }

    public function create(array $data): Season
    {
        return $this->seasons->create($data);
    }

    public function update(int $id, array $data): Season
    {
        return $this->seasons->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->seasons->delete($id);
    }

    public function list(string $search, string $statusFilter, string $sortField, string $sortDirection, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->seasons->list($search, $statusFilter, $sortField, $sortDirection, $perPage, $page);
    }

    public function getForView(int $id): ?Season
    {
        return $this->seasons->getForView($id);
    }
}

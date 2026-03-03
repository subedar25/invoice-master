<?php

namespace App\Core\Season\Contracts;

use App\Models\Season;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SeasonRepository
{
    public function find(int $id): Season;

    public function findWithTrashed(int $id): Season;

    public function create(array $data): Season;

    public function update(int $id, array $data): Season;

    public function delete(int $id): void;

    /**
     * @return LengthAwarePaginator<Season>
     */
    public function list(string $search, string $statusFilter, string $sortField, string $sortDirection, int $perPage, int $page = 1): LengthAwarePaginator;

    public function getForView(int $id): ?Season;
}

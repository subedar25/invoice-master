<?php

namespace App\Core\Advertiser\Contracts;

use App\Models\Advertiser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdvertiserRepository
{
    public function find(int $id): Advertiser;

    public function findWithTrashed(int $id): Advertiser;

    public function create(array $data): Advertiser;

    public function update(int $id, array $data): Advertiser;

    public function delete(int $id): void;

    /**
     * @return LengthAwarePaginator<Advertiser>
     */
    public function list(string $search, string $statusFilter, string $sortField, string $sortDirection, int $perPage, int $page = 1): LengthAwarePaginator;

    public function getForView(int $id): ?Advertiser;
}

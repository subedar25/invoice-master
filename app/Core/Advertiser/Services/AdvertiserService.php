<?php

namespace App\Core\Advertiser\Services;

use App\Core\Advertiser\Contracts\AdvertiserRepository;
use App\Models\Advertiser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdvertiserService
{
    public function __construct(
        private AdvertiserRepository $advertisers
    ) {}

    public function find(int $id): Advertiser
    {
        return $this->advertisers->find($id);
    }

    public function findWithTrashed(int $id): Advertiser
    {
        return $this->advertisers->findWithTrashed($id);
    }

    public function create(array $data): Advertiser
    {
        return $this->advertisers->create($data);
    }

    public function update(int $id, array $data): Advertiser
    {
        return $this->advertisers->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->advertisers->delete($id);
    }

    public function list(string $search, string $statusFilter, string $sortField, string $sortDirection, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->advertisers->list($search, $statusFilter, $sortField, $sortDirection, $perPage, $page);
    }

    public function getForView(int $id): ?Advertiser
    {
        return $this->advertisers->getForView($id);
    }
}

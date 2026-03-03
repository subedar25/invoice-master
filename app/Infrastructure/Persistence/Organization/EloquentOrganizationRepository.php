<?php

namespace App\Infrastructure\Persistence\Organization;

use App\Core\Organization\Contracts\OrganizationRepository;
use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentOrganizationRepository implements OrganizationRepository
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Client::query()
            ->orderByDesc('added_timestamp')
            ->paginate($perPage);
    }

    public function getAll(): Collection
    {
        return Client::query()->orderByDesc('added_timestamp')->get();
    }

    public function find(int $id): Client
    {
        return Client::findOrFail($id);
    }

    public function create(array $data): Client
    {
        return Client::create($data);
    }

    public function update(int $id, array $data): Client
    {
        $organization = $this->find($id);
        $organization->update($data);

        return $organization;
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }
}


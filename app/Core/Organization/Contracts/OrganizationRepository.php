<?php

namespace App\Core\Organization\Contracts;

use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrganizationRepository
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function getAll(): Collection;

    public function find(int $id): Client;

    public function create(array $data): Client;

    public function update(int $id, array $data): Client;

    public function delete(int $id): void;
}


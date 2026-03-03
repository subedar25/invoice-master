<?php

namespace App\Core\Organization\Services;

use App\Core\Organization\Contracts\OrganizationRepository;
use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class OrganizationService
{
    /** @var OrganizationRepository */
    private $organizations;

    public function __construct(OrganizationRepository $organizations)
    {
        $this->organizations = $organizations;
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->organizations->paginate($perPage);
    }

    public function get(int $id): Client
    {
        return $this->organizations->find($id);
    }

    public function create(array $data): Client
    {
        if (! array_key_exists('added_timestamp', $data) || empty($data['added_timestamp'])) {
            $data['added_timestamp'] = Carbon::now();
        }

        $data['open'] = (bool) ($data['open'] ?? true);
        $data['active'] = (bool) ($data['active'] ?? true);

        return $this->organizations->create($data);
    }

    public function update(int $id, array $data): Client
    {
        if (array_key_exists('open', $data)) {
            $data['open'] = (bool) $data['open'];
        }

        if (array_key_exists('active', $data)) {
            $data['active'] = (bool) $data['active'];
        }

        return $this->organizations->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->organizations->delete($id);
    }
}

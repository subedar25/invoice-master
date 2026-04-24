<?php

namespace App\Core\Location\Contracts;

use App\Models\Location;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface LocationRepository
{
    public function create(array $data): Location;

    public function update(Location $location, array $data): Location;

    public function updateLocation(int $id, array $data): Location;

    public function delete(int $id): void;

    public function find(int $id): Location;

    public function findWithTrashed(int $id): Location;

    public function paginateLatest(int $perPage = 20): LengthAwarePaginator;

    public function getActiveCountries(): Collection;

    /**
     * @return array<int, array<int, array{id:int, name:string}>>
     */
    public function getActiveStatesGroupedByCountry(): array;

    public function nameExists(string $name, ?int $excludeId = null): bool;

    public function adminUsersForLocationNotification(): Collection;

    public function getForDataTable(array $filters = [], ?string $search = null, int $start = 0, int $length = 10, string $sortColumn = 'id', string $sortDir = 'desc');

    public function countLocations(array $filters = [], ?string $search = null): int;

    public function updateByFilter(array $filters, array $data): int;
}

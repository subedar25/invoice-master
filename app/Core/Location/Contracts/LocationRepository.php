<?php

namespace App\Core\Location\Contracts;

use App\Models\Location;
use Illuminate\Support\Collection;

interface LocationRepository
{
    public function create(array $data): Location;

    public function update(Location $location, array $data): Location;

    public function delete(int $id): void;

    public function find(int $id): Location;

    public function getForDataTable(array $filters = [], ?string $search = null, int $start = 0, int $length = 10, string $sortColumn = 'id', string $sortDir = 'desc');

    public function countLocations(array $filters = [], ?string $search = null): int;

    public function updateByFilter(array $filters, array $data): int;
}

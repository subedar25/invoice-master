<?php

namespace App\Core\Location\Services;

use App\Core\Location\Contracts\LocationRepository;
use App\Http\Requests\MasterApp\Location\LocationStoreRequest;
use App\Http\Requests\MasterApp\Location\LocationUpdateRequest;
use App\Models\Location;

class LocationService
{
    public function __construct(
        private LocationRepository $locations
    ) {}

    public function createLocation(array $data): Location
    {
        return $this->locations->create($data);
    }
    public function deleteLocation(int $id): void
    {
        $this->locations->delete($id);
    }

    public function getLocation(int $id): Location
    {
        return $this->locations->find($id);
    }

    public function updateLocation(int $id, array $data): Location
    {
        return $this->locations->updateLocation($id, $data);
    }

    public function getDataTableData(array $filters, ?string $search, int $start, int $length, array $order)
    {
        $sortColumn = $order['column'] ?? 'id';
        $sortDir = $order['dir'] ?? 'desc';

        $data = $this->locations->getForDataTable($filters, $search, $start, $length, $sortColumn, $sortDir);
        $totalDisplay = $this->locations->countLocations($filters, $search);
        $totalAll = $this->locations->countLocations([], null);

        return [
            'data' => $data,
            'recordsFiltered' => $totalDisplay,
            'recordsTotal' => $totalAll,
        ];
    }
}

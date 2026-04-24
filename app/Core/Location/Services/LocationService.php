<?php

namespace App\Core\Location\Services;

use App\Core\Location\Contracts\LocationRepository;
use App\Models\Location;
use Illuminate\Support\Collection;

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

    public function getLocationWithTrashed(int $id): Location
    {
        return $this->locations->findWithTrashed($id);
    }

    /**
     * @return array{locations:\Illuminate\Contracts\Pagination\LengthAwarePaginator, countries:Collection, defaultCountryId:mixed, statesByCountry:array}
     */
    public function getIndexData(int $perPage = 20): array
    {
        $locations = $this->locations->paginateLatest($perPage);
        $countries = $this->locations->getActiveCountries();
        $defaultCountryId = optional($countries->firstWhere('name', 'India'))->id
            ?? optional($countries->firstWhere('id', 101))->id
            ?? optional($countries->first())->id;

        return [
            'locations' => $locations,
            'countries' => $countries,
            'defaultCountryId' => $defaultCountryId,
            'statesByCountry' => $this->locations->getActiveStatesGroupedByCountry(),
        ];
    }

    public function locationNameExists(string $name, ?int $excludeId = null): bool
    {
        return $this->locations->nameExists($name, $excludeId);
    }

    public function getAdminUsersForLocationNotification(): Collection
    {
        return $this->locations->adminUsersForLocationNotification();
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

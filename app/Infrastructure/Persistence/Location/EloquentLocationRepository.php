<?php

namespace App\Infrastructure\Persistence\Location;

use App\Core\Location\Contracts\LocationRepository;
use App\Models\Location;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentLocationRepository implements LocationRepository
{
    public function create(array $data): Location
    {
        return Location::create($data);
    }

    public function update(Location $location, array $data): Location
    {
        $location->update($data);
        return $location;
    }
    public function updateLocation(int $id, array $data): Location
    {
        $location = Location::findOrFail($id);
        return $this->update($location, $data);
    }
    public function delete(int $id): void
    {
        Location::whereKey($id)->delete(); // soft delete
    }

    public function find(int $id): Location
    {
        return Location::findOrFail($id);
    }

    public function findWithTrashed(int $id): Location
    {
        return Location::withTrashed()->findOrFail($id);
    }

    public function paginateLatest(int $perPage = 20): LengthAwarePaginator
    {
        return Location::latest('created_at')->paginate($perPage);
    }

    public function getActiveCountries(): Collection
    {
        return DB::table('countries')
            ->select('id', 'name')
            ->where('status', 1)
            ->orderBy('name')
            ->get();
    }

    public function getActiveStatesGroupedByCountry(): array
    {
        return DB::table('states')
            ->select('id', 'country_id', 'name')
            ->where('status', 1)
            ->orderBy('name')
            ->get()
            ->groupBy('country_id')
            ->map(fn ($items) => $items->map(fn ($item) => [
                'id' => (int) $item->id,
                'name' => (string) $item->name,
            ])->values())
            ->toArray();
    }

    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $query = Location::query()->where('name', $name);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function adminUsersForLocationNotification(): Collection
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Admin User', 'System Admin']);
        })->get();
    }

    public function getForDataTable(array $filters = [], ?string $search = null, int $start = 0, int $length = 10, string $sortColumn = 'id', string $sortDir = 'desc')
    {
        $query = $this->buildQuery($filters, $search);

        // Ensure valid columns for sorting
        $allowedSorts = ['id', 'name', 'address', 'city', 'state', 'country', 'postal_code', 'phone', 'show_map', 'show_map_link', 'latitude', 'longitude'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDir);
        } else {
            $query->orderBy('id', 'desc');
        }

        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        return $query->get();
    }

    public function countLocations(array $filters = [], ?string $search = null): int
    {
        return $this->buildQuery($filters, $search)->count();
    }

    private function buildQuery(array $filters = [], ?string $search = null)
    {
        $query = Location::query();

        // Apply filters if any
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        // Global Search
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('postal_code', 'like', "%{$search}%");
                //   ->orWhere('phone', 'like', "%{$search}%");
                //   ->orWhere('latitude', 'like', "%{$search}%");
                //   ->orWhere('longitude', 'like', "%{$search}%");
                //   ->orWhere('show_map', 'like', "%{$search}%");
                //   ->orWhere('show_map_link', 'like', "%{$search}%");

            });
        }

        return $query;
    }

    public function updateByFilter(array $filters, array $data): int
    {
        $query = Location::query();

        // Apply the same filters as buildQuery
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        return $query->update($data);
    }
}

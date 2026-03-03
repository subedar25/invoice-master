<?php

namespace App\Infrastructure\Persistence\OrganizationType;

use App\Core\OrganizationType\Contracts\OrganizationTypeRepository;
use App\Models\OrganizationType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentOrganizationTypeRepository implements OrganizationTypeRepository
{
    public function find(int $id): OrganizationType
    {
        return OrganizationType::findOrFail($id);
    }

    public function findWithTrashed(int $id): OrganizationType
    {
        return OrganizationType::withTrashed()->findOrFail($id);
    }

    public function create(array $data): OrganizationType
    {
        $code = $data['code'] ?? null;
        if ($code !== null && $code !== '') {
            $code = $this->ensureUniqueCode($code);
        }

        return OrganizationType::create([
            'name' => $data['name'],
            'code' => $code,
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'active' => $data['status'] ?? true,
        ]);
    }

    /**
     * If the given code already exists (including soft-deleted), returns a unique variant (e.g. code_2, code_3).
     */
    private function ensureUniqueCode(string $code): string
    {
        $exists = OrganizationType::withTrashed()->where('code', $code)->exists();
        if (! $exists) {
            return $code;
        }
        $suffix = 2;
        do {
            $candidate = $code . '_' . $suffix;
            $exists = OrganizationType::withTrashed()->where('code', $candidate)->exists();
            if (! $exists) {
                return $candidate;
            }
            $suffix++;
        } while (true);
    }

    public function update(int $id, array $data): OrganizationType
    {
        $record = OrganizationType::withTrashed()->findOrFail($id);
        $record->update([
            'name' => $data['name'] ?? $record->name,
            'description' => array_key_exists('description', $data) ? ($data['description'] ?: null) : $record->description,
            'parent_id' => array_key_exists('parent_id', $data) ? ($data['parent_id'] ?: null) : $record->parent_id,
            'active' => array_key_exists('status', $data) ? (bool) $data['status'] : $record->active,
        ]);

        return $record;
    }

    public function delete(int $id): void
    {
        OrganizationType::findOrFail($id)->delete();
    }

    public function list(string $search, string $statusFilter, string $sortField, string $sortDirection, int $perPage, int $page = 1): LengthAwarePaginator
    {
        $query = OrganizationType::query()
            ->with('parent')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->when($statusFilter !== '', function ($q) use ($statusFilter) {
                $q->where('active', (bool) $statusFilter);
            });

        $allowedSorts = ['name', 'active', 'created_at'];
        if (in_array($sortField, $allowedSorts, true)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getParentOptions(?int $excludeId = null): Collection
    {
        $query = OrganizationType::query()
            ->where('active', true)
            ->orderBy('name');

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }

    public function getForView(int $id): ?OrganizationType
    {
        return OrganizationType::withTrashed()
            ->with([
                'parent',
                'children' => fn ($q) => $q->with('parent')->orderBy('created_at', 'desc'),
            ])
            ->find($id);
    }
}

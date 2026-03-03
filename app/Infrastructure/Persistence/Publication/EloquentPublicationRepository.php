<?php

namespace App\Infrastructure\Persistence\Publication;

use App\Core\Publication\Contracts\PublicationRepository;
use App\Models\Publication;
use App\Models\PublicationType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentPublicationRepository implements PublicationRepository
{
    public function find(int $id): Publication
    {
        return Publication::findOrFail($id);
    }

    public function findWithTrashed(int $id): Publication
    {
        return Publication::withTrashed()->findOrFail($id);
    }

    public function create(array $data): Publication
    {
        $code = $data['code'] ?? null;
        if ($code !== null && $code !== '') {
            $code = $this->ensureUniqueCode($code);
        }

        return Publication::create([
            'publication_type_id' => $data['publication_type_id'] ?? null,
            'name' => $data['name'],
            'code' => $code,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? true,
        ]);
    }

    /**
     * If the given code already exists (including soft-deleted), returns a unique variant (e.g. code_2, code_3).
     */
    private function ensureUniqueCode(string $code): string
    {
        $exists = Publication::withTrashed()->where('code', $code)->exists();
        if (! $exists) {
            return $code;
        }
        $suffix = 2;
        do {
            $candidate = $code . '_' . $suffix;
            $exists = Publication::withTrashed()->where('code', $candidate)->exists();
            if (! $exists) {
                return $candidate;
            }
            $suffix++;
        } while (true);
    }

    public function update(int $id, array $data): Publication
    {
        $record = Publication::withTrashed()->findOrFail($id);
        $record->update([
            'publication_type_id' => array_key_exists('publication_type_id', $data) ? ($data['publication_type_id'] ?: null) : $record->publication_type_id,
            'name' => $data['name'] ?? $record->name,
            'description' => array_key_exists('description', $data) ? ($data['description'] ?: null) : $record->description,
            'status' => array_key_exists('status', $data) ? (bool) $data['status'] : $record->status,
        ]);
        // code is set on create only; not updated on edit (same as organization type)
        return $record;
    }

    public function delete(int $id): void
    {
        Publication::findOrFail($id)->delete();
    }

    public function list(string $search, string $sortField, string $sortDirection, int $perPage, int $page = 1): LengthAwarePaginator
    {
        $query = Publication::query()
            ->with('publicationType')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            });

        $allowedSorts = ['name', 'code', 'status', 'created_at'];
        if (in_array($sortField, $allowedSorts, true)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getPublicationTypeOptions(): Collection
    {
        return PublicationType::query()
            ->orderBy('publication_type')
            ->get();
    }

    public function getForView(int $id): ?Publication
    {
        return Publication::withTrashed()
            ->with('publicationType')
            ->find($id);
    }
}

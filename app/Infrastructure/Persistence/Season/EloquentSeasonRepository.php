<?php

namespace App\Infrastructure\Persistence\Season;

use App\Core\Season\Contracts\SeasonRepository;
use App\Models\Season;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class EloquentSeasonRepository implements SeasonRepository
{
    public function find(int $id): Season
    {
        return Season::findOrFail($id);
    }

    public function findWithTrashed(int $id): Season
    {
        return Season::withTrashed()->findOrFail($id);
    }

    public function create(array $data): Season
    {
        $code = $data['code'] ?? $this->generateCodeFromName($data['name'] ?? '');
        if ($code !== '') {
            $code = $this->ensureUniqueCode($code);
        }

        return Season::create([
            'name' => $data['name'],
            'code' => $code ?: null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? true,
        ]);
    }

    private function generateCodeFromName(string $name): string
    {
        return strtolower(Str::slug(trim($name), '_'));
    }

    private function ensureUniqueCode(string $code): string
    {
        $exists = Season::withTrashed()->where('code', $code)->exists();
        if (!$exists) {
            return $code;
        }
        $suffix = 2;
        do {
            $candidate = $code . '_' . $suffix;
            $exists = Season::withTrashed()->where('code', $candidate)->exists();
            if (!$exists) {
                return $candidate;
            }
            $suffix++;
        } while (true);
    }

    public function update(int $id, array $data): Season
    {
        $record = Season::withTrashed()->findOrFail($id);
        $record->update([
            'name' => $data['name'] ?? $record->name,
            'description' => array_key_exists('description', $data) ? ($data['description'] ?: null) : $record->description,
            'status' => array_key_exists('status', $data) ? (bool) $data['status'] : $record->status,
        ]);

        return $record;
    }

    public function delete(int $id): void
    {
        Season::findOrFail($id)->delete();
    }

    public function list(string $search, string $statusFilter, string $sortField, string $sortDirection, int $perPage, int $page = 1): LengthAwarePaginator
    {
        $query = Season::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->when($statusFilter !== '', function ($q) use ($statusFilter) {
                $q->where('status', (bool) $statusFilter);
            });

        $allowedSorts = ['name', 'status', 'created_at'];
        if (in_array($sortField, $allowedSorts, true)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getForView(int $id): ?Season
    {
        return Season::withTrashed()->find($id);
    }
}

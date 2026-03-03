<?php

namespace App\Infrastructure\Persistence\Advertiser;

use App\Core\Advertiser\Contracts\AdvertiserRepository;
use App\Models\Advertiser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class EloquentAdvertiserRepository implements AdvertiserRepository
{
    public function find(int $id): Advertiser
    {
        return Advertiser::findOrFail($id);
    }

    public function findWithTrashed(int $id): Advertiser
    {
        return Advertiser::withTrashed()->findOrFail($id);
    }

    public function create(array $data): Advertiser
    {
        $code = $data['code'] ?? $this->generateCodeFromName($data['name'] ?? '');
        if ($code !== '') {
            $code = $this->ensureUniqueCode($code);
        }

        return Advertiser::create([
            'name' => $data['name'],
            'code' => $code ?: null,
            'description' => $data['description'] ?? null,
            'active' => $data['status'] ?? true,
        ]);
    }

    private function generateCodeFromName(string $name): string
    {
        return strtolower(Str::slug(trim($name), '_'));
    }

    private function ensureUniqueCode(string $code): string
    {
        $exists = Advertiser::withTrashed()->where('code', $code)->exists();
        if (!$exists) {
            return $code;
        }
        $suffix = 2;
        do {
            $candidate = $code . '_' . $suffix;
            $exists = Advertiser::withTrashed()->where('code', $candidate)->exists();
            if (!$exists) {
                return $candidate;
            }
            $suffix++;
        } while (true);
    }

    public function update(int $id, array $data): Advertiser
    {
        $record = Advertiser::withTrashed()->findOrFail($id);
        $record->update([
            'name' => $data['name'] ?? $record->name,
            'description' => array_key_exists('description', $data) ? ($data['description'] ?: null) : $record->description,
            'active' => array_key_exists('status', $data) ? (bool) $data['status'] : $record->active,
        ]);

        return $record;
    }

    public function delete(int $id): void
    {
        Advertiser::findOrFail($id)->delete();
    }

    public function list(string $search, string $statusFilter, string $sortField, string $sortDirection, int $perPage, int $page = 1): LengthAwarePaginator
    {
        $query = Advertiser::query()
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

    public function getForView(int $id): ?Advertiser
    {
        return Advertiser::withTrashed()->find($id);
    }
}

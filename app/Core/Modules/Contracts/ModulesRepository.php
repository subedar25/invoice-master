<?php
namespace App\Core\Modules\Contracts;

use App\Models\Module;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ModulesRepository
{
    public function find(int $id): Module;

    public function create(array $data): Module;

    public function update(int $id, array $data): Module;

    public function delete(int $id): void;

    public function paginateByLatest(int $perPage = 200): LengthAwarePaginator;

    public function toggleActive(int $id): Module;

    public function bulkDelete(array $ids): int;
}

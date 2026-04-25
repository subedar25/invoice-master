<?php
namespace App\Infrastructure\Persistence\Modules;

use App\Core\Modules\Contracts\ModulesRepository;
use App\Models\Module;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentModulesRepository implements ModulesRepository
{
    public function find(int $id): Module
    {
        return Module::findOrFail($id);
    }

    public function create(array $data): Module
    {
        $modules = Module::create([
            'name'    => $data['name'],
            'slug'     => $data['slug'],
            'type'     => $data['type'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
      
        return $modules;
    }

    public function update(int $id, array $data): Module
    {
        $modules = Module::findOrFail($id);
        $modules->update($data);
        return $modules;
    }

    public function delete(int $id): void
    {
        Module::findOrFail($id)->delete();
    }

    public function paginateByLatest(int $perPage = 200): LengthAwarePaginator
    {
        return Module::orderBy('id', 'desc')->paginate($perPage);
    }

    public function toggleActive(int $id): Module
    {
        $module = Module::findOrFail($id);
        $module->is_active = ! (bool) $module->is_active;
        $module->save();

        return $module;
    }

    public function bulkDelete(array $ids): int
    {
        return Module::whereIn('id', $ids)->delete();
    }
}

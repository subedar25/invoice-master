<?php
namespace App\Infrastructure\Persistence\Modules;

use App\Core\Modules\Contracts\ModulesRepository;
use App\Models\Module;
use Spatie\Permission\Models\Role;

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
}

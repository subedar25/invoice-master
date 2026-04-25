<?php

namespace App\Core\Modules\Services;

use App\Core\Modules\Contracts\ModulesRepository;


class ModulesService
{
   
    public function __construct(
        private ModulesRepository $modules
    ) {}

    public function create(array $data)
    {
        return $this->modules->create($data);
    }

    public function get(int $id)
    {
        return $this->modules->find($id);
    }

    public function update(int $id, array $data)
    {
        return $this->modules->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->modules->delete($id);
    }

    public function paginateByLatest(int $perPage = 200)
    {
        return $this->modules->paginateByLatest($perPage);
    }

    public function toggleActive(int $id)
    {
        return $this->modules->toggleActive($id);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->modules->bulkDelete($ids);
    }
}

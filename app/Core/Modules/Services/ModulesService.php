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
}

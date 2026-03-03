<?php

namespace App\Core\Roles\Services;

use App\Core\Roles\Contracts\RolesRepository;


class RolesService
{
   
    public function __construct(
        private RolesRepository $roles
    ) {}

    public function create(array $data)
    {
        return $this->roles->create($data);
    }

    public function get(int $id)
    {
        return $this->roles->find($id);
    }

    public function update(int $id, array $data)
    {
        return $this->roles->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->roles->delete($id);
    }

}

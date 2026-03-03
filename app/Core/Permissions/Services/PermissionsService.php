<?php

namespace App\Core\Permissions\Services;

use App\Core\Permissions\Contracts\PermissionsRepository;


class PermissionsService
{
   
    public function __construct(
        private PermissionsRepository $Permission
    ) {}

    public function create(array $data)
    {
        return $this->Permission->create($data);
    }

    public function get(int $id)
    {
        return $this->Permission->find($id);
    }

    public function update(int $id, array $data)
    {
        return $this->Permission->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->Permission->delete($id);
    }
}

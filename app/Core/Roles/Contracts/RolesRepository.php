<?php
namespace App\Core\Roles\Contracts;

use App\Models\Role;

interface RolesRepository
{
    public function find(int $id): Role;

    public function create(array $data): Role;

    public function update(int $id, array $data): Role;

     public function delete(int $id): void;
}

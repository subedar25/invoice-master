<?php
namespace App\Core\Permissions\Contracts;

use App\Models\Permission;

interface PermissionsRepository
{
    public function find(int $id): Permission;

    public function create(array $data): Permission;

    public function update(int $id, array $data): Permission;

     public function delete(int $id): void;
}

<?php
namespace App\Core\Modules\Contracts;

use App\Models\Module;

interface ModulesRepository
{
    public function find(int $id): Module;

    public function create(array $data): Module;

    public function update(int $id, array $data): Module;

     public function delete(int $id): void;
}

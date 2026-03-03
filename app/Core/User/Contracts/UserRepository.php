<?php
namespace App\Core\User\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
interface UserRepository
{
    public function find(int $id): User;

    public function create(array $data): User;
    // public function edit(array $data): User;

    public function update(int $id, array $data): User;

    // public function delete(int $id): void;
     public function paginate(int $perPage = 10): LengthAwarePaginator;

    public function delete(int $id): void;

    public function getAll(): \Illuminate\Database\Eloquent\Collection;
// }
    // public function getAll(): Collection
    // {
    //     return User::with([
    //         'department',
    //         'publications',
    //         'status'
    //     ])->get();
    // }


}

<?php
namespace App\Infrastructure\Persistence\User;

use App\Core\User\Contracts\UserRepository;
use App\Models\User;
use App\Models\Publication;
use Spatie\Permission\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentUserRepository implements UserRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return User::latest()->paginate($perPage);
    }
    public function find(int $id): User
    {
        return User::with('roles')->findOrFail($id);
    }
    // public function find(int $id): User
    // {
    //     return user::with('publications')->findOrFail($id);
    // }
    public function create(array $data): User
    {
        $user = User::create([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'] ?? null,
            // 'password'      => Hash::make($data['password']),
            'password'     => $data['password'],
            'active'        => $data['active'],
            'driver'        => $data['driver'],
            'department_id' => $data['department_id'] ?? null,
            'is_wordpress_user' => $data['is_wordpress_user'],
            'publications' =>$data['publications'] ?? 0,
            'departments' =>$data['departments'] ?? 0,        
            'status_id'    => $data['status_id'] ?? null, 
            'department_id'=> $data['department_id'] ?? null,
           'contributor_status' => $data['contributor_status'] ?? null,
            'status_notes' => $data['status_notes'] ?? null,

            
        ]);
        // Sync roles
        if (!empty($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }

        // Sync publications
        if (!empty($data['publications'])) {
            $user->publications()->sync($data['publications']);
        }

        return $user;
    }

    public function update(int $id, array $data): User
    {
    $roles = $data['roles'] ?? [];
    unset($data['roles']);

    $publications = $data['publications'] ?? [];
    unset($data['publications']);

    $user = User::findOrFail($id);
    $user->update($data);

    // if (!empty($roles)) {
    //     $user->syncRoles(Role::find($roles));
    // }
    
    if ($roles) {
            $user->syncRoles(Role::find($roles));
        }

    //  FIX IS HERE
    if (!empty($publications)) {
        $user->publications()->sync($publications);
    }

    return $user;
    }

    public function delete(int $id): void
    {
        User::findOrFail($id)->delete();
    }

    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return User::all();
    }
}

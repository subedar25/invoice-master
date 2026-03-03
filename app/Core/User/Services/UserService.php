<?php

namespace App\Core\User\Services;

use App\Core\User\Contracts\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;
use App\Notifications\NewUserNotification;

class UserService
{
    public function __construct(
        private UserRepository $users
    ) {}
//    public function paginate(int $perPage = 10): LengthAwarePaginator
//     {
//         return $this->users->paginate($perPage);
//     }
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return User::orderBy('created_at', 'desc')->paginate($perPage);
        //  return $this->users->paginate($perPage);
    }

//     public function all(): \Illuminate\Database\Eloquent\Collection
//     {
//         return $this->users->all();
//     }

    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->users->getAll();
    }
    // Create user with notification to admins
    public function create(array $data)
    {
    if (!isset($data['password'])) {
        throw new \InvalidArgumentException('Password is required to create a user.');
    }

    $data['password'] = Hash::make($data['password']);
    $data['driver']   = (bool) ($data['driver'] ?? false);
    $data['active']   = (bool) ($data['active'] ?? false);
    $data['is_wordpress_user'] = (bool) ($data['is_wordpress_user'] ?? 0);
    $data['department_id'] = $data['department_id'] ?? null;
    $data['status_id']     = $data['status_id'] ?? null;
    $data['status_notes']  = $data['status_notes'] ?? '';

    //  CREATE USER ONCE
    $user = $this->users->create($data);

    //  NOTIFY ADMINS (excluding the user who created this user)
    User::where(function ($query) {
        $query->whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'superadmin']);
        });
    })->where('id', '!=', auth()->id())->each(function ($admin) use ($user) {
        $admin->notify(new NewUserNotification($user));
    });

    return $user;
    }


    public function get(int $id)
    {
        return $this->users->find($id);
    }

     public function edit(array $data)
    {
        $data['driver']   = (bool) ($data['driver'] ?? false);
        $data['active']   = (bool) ($data['active'] ?? false);
        $data['is_wordpress_user'] = (bool) ($data['is_wordpress_user'] ?? 0);
        $data['department_id'] = $data['department_id'] ?? null;
        $data['status_id']     = $data['status_id'] ?? null;
        $data['status_notes'] ??= '';
        // print_r($data);exit;

        return $this->users->edit($data);

    }
     public function update(int $id, array $data): User
    {
      
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['driver'] = (bool) ($data['driver'] ?? false);
        $data['active'] = (bool) ($data['active'] ?? false);
        $data['is_wordpress_user'] = (bool) ($data['is_wordpress_user'] ?? 0);
        $data['department_id'] = $data['department_id'] ?? null;
        $data['status_id']     = $data['status_id'] ?? null;
        $data['status_notes'] ??= '';
        // print_r($data);exit;
        return $this->users->update($id, $data);
    }
    public function delete(int $id): void
    {
        $this->users->delete($id);
    }
    //    ajax toggle without page reload 
    public function toggleActive(int $id): void
    {
        $user = $this->get($id);

        $this->update($id, [
            'active' => ! $user->active,
        ]);
    }
}

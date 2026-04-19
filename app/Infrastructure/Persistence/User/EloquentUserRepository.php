<?php
namespace App\Infrastructure\Persistence\User;

use App\Core\User\Contracts\UserRepository;
use App\Models\User;
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
        return User::with(['roles', 'organizations', 'reportingManager', 'userDocuments', 'department', 'designation'])->findOrFail($id);
    }
    public function create(array $data): User
    {
        $user = User::create([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'] ?? null,
            'password'     => $data['password'],
            'active'        => $data['active'],
            'user_type'     => $data['user_type'] ?? 'user',
            'department_id' => $data['department_id'] ?? null,
            'designation_id' => $data['designation_id'] ?? null,
            'reporting_manager_id' => $data['reporting_manager_id'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'pincode' => $data['pincode'] ?? null,
            'photo' => $data['photo'] ?? null,
            'is_wordpress_user' => $data['is_wordpress_user'],
        ]);

        if (!empty($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }

        if (!empty($data['organization_ids'])) {
            $user->organizations()->sync($data['organization_ids']);
        }

        if (!empty($data['other_documents_data'])) {
            $user->userDocuments()->createMany($data['other_documents_data']);
        }

        return $user;
    }

    public function update(int $id, array $data): User
    {
        $roles = $data['roles'] ?? [];
        $organizationIds = $data['organization_ids'] ?? [];
        $otherDocumentsData = $data['other_documents_data'] ?? [];

        unset($data['roles'], $data['organization_ids'], $data['other_documents_data'], $data['other_documents'], $data['remove_photo'], $data['remove_documents']);

        $user = User::findOrFail($id);
        $user->update($data);

        if ($roles) {
            $user->syncRoles(Role::find($roles));
        }

        $user->organizations()->sync($organizationIds);

        if (!empty($otherDocumentsData)) {
            $user->userDocuments()->createMany($otherDocumentsData);
        }

        return $user;
    }

    public function delete(int $id): void
    {
        User::findOrFail($id)->delete();
    }

    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return User::with(['roles', 'department', 'designation', 'organizations', 'reportingManager'])->get();
    }
}

<?php
namespace App\Infrastructure\Persistence\User;

use App\Core\User\Contracts\UserRepository;
use App\Models\Department;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserDesignation;
use Spatie\Permission\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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

    public function getUsersForIndex(User $authUser, int $currentOrganizationId): \Illuminate\Database\Eloquent\Collection
    {
        $isSystemUser = ($authUser->user_type ?? '') === 'systemuser';
        $query = User::query()
            ->with(['roles', 'department', 'designation', 'organizations', 'reportingManager'])
            ->where(function ($q) {
                $q->whereNull('user_type')
                    ->orWhere('user_type', '!=', 'systemuser');
            });

        if ($currentOrganizationId > 0) {
            $query->whereHas('organizations', function ($q) use ($currentOrganizationId) {
                $q->where('organizations.id', $currentOrganizationId);
            });
        } elseif (! $isSystemUser) {
            $allowedOrgIds = $authUser->organizations()->pluck('organizations.id')->all();
            if (empty($allowedOrgIds)) {
                return new \Illuminate\Database\Eloquent\Collection();
            }
            $query->whereHas('organizations', function ($q) use ($allowedOrgIds) {
                $q->whereIn('organizations.id', $allowedOrgIds);
            });
        }

        return $query->get();
    }

    public function getAccessibleOrganizations(User $authUser): Collection
    {
        $isSystemUser = ($authUser->user_type ?? '') === 'systemuser';

        return $isSystemUser
            ? Organization::orderBy('name')->get(['id', 'name'])
            : $authUser->organizations()->orderBy('name')->get(['organizations.id', 'organizations.name']);
    }

    public function getDepartmentsByOrganization(int $organizationId): \Illuminate\Database\Eloquent\Collection
    {
        return Department::where('organization_id', $organizationId)->orderBy('name')->get();
    }

    public function getDesignationsByOrganization(int $organizationId): \Illuminate\Database\Eloquent\Collection
    {
        return UserDesignation::query()
            ->where('status', true)
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getDepartmentsForUserContext(User $authUser, int $currentOrganizationId): \Illuminate\Database\Eloquent\Collection
    {
        if ($currentOrganizationId > 0) {
            return $this->getDepartmentsByOrganization($currentOrganizationId);
        }

        if (($authUser->user_type ?? '') === 'systemuser') {
            return Department::orderBy('name')->get();
        }

        $orgIds = $authUser->organizations()->pluck('organizations.id');
        if ($orgIds->isEmpty()) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return Department::whereIn('organization_id', $orgIds)->orderBy('name')->get();
    }

    public function getDesignationsForUserContext(User $authUser, int $currentOrganizationId): \Illuminate\Database\Eloquent\Collection
    {
        if ($currentOrganizationId > 0) {
            return $this->getDesignationsByOrganization($currentOrganizationId);
        }

        if (($authUser->user_type ?? '') === 'systemuser') {
            return UserDesignation::query()
                ->where('status', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $orgIds = $authUser->organizations()->pluck('organizations.id');
        if ($orgIds->isEmpty()) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return UserDesignation::query()
            ->where('status', true)
            ->whereIn('organization_id', $orgIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getReportingManagersForOrganizationIds(array $organizationIds, ?int $excludeUserId = null): Collection
    {
        $organizationIds = array_values(array_unique(array_filter(array_map('intval', $organizationIds))));
        if ($organizationIds === []) {
            return collect();
        }

        $query = User::query()
            ->where(function ($q) {
                $q->whereNull('user_type')
                    ->orWhere('user_type', '!=', 'systemuser');
            });

        if ($excludeUserId !== null) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query
            ->whereHas('organizations', function ($q) use ($organizationIds) {
                $q->whereIn('organizations.id', $organizationIds);
            })
            ->with(['designation:id,name'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'designation_id']);
    }

    public function getRolesForOrganizationIds(array $organizationIds): Collection
    {
        $organizationIds = array_values(array_unique(array_filter(array_map('intval', $organizationIds))));
        if ($organizationIds === []) {
            return collect();
        }

        return Role::query()
            ->where('is_active', true)
            ->whereIn('organization_id', $organizationIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function emailExistsWithTrashed(string $email, ?int $excludeUserId = null): bool
    {
        return User::withTrashed()
            ->where('email', $email)
            ->when($excludeUserId, function ($query) use ($excludeUserId) {
                return $query->where('id', '!=', $excludeUserId);
            })
            ->exists();
    }

    public function getAdminUsersExcluding(?int $excludeUserId = null): Collection
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Admin User', 'System Admin']);
        })->when($excludeUserId, function ($query) use ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        })->get();
    }
}

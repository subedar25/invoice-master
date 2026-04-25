<?php
namespace App\Core\User\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function getUsersForIndex(User $authUser, int $currentOrganizationId): \Illuminate\Database\Eloquent\Collection;

    public function getAccessibleOrganizations(User $authUser): Collection;

    public function getDepartmentsByOrganization(int $organizationId): \Illuminate\Database\Eloquent\Collection;

    public function getDesignationsByOrganization(int $organizationId): \Illuminate\Database\Eloquent\Collection;

    public function getDepartmentsForUserContext(User $authUser, int $currentOrganizationId): \Illuminate\Database\Eloquent\Collection;

    public function getDesignationsForUserContext(User $authUser, int $currentOrganizationId): \Illuminate\Database\Eloquent\Collection;

    public function getReportingManagersForOrganizationIds(array $organizationIds, ?int $excludeUserId = null): Collection;

    public function getRolesForOrganizationIds(array $organizationIds): Collection;

    public function emailExistsWithTrashed(string $email, ?int $excludeUserId = null): bool;

    public function getAdminUsersExcluding(?int $excludeUserId = null): Collection;
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

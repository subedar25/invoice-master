<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
   

    /**
     * View list (index)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('list-users');
    }

    /**
     * View a single user
     */
    public function view(User $user, User $model): bool
    {
        return $user->can('list-users');
    }

   

    /**
     * Create a user
     */
    public function create(User $user): bool
    {
        return $user->can('create-user');
    }

   

    /**
     * Update user
     */
    public function update(User $user, User $model): bool
    {
        return $user->can('edit-user');
    }

    /**
     * Update user status
     */
    public function updateStatus(User $user, User $model): bool
    {
        return $user->can('edit-user');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(User $user, User $model): bool
    {
        return $user->can('edit-user');
    }

    /**
     * Update password
     */
    public function updatePassword(User $user, User $model): bool
    {
        return $user->can('edit-user');
    }

   

    /**
     * Delete a user
     */
    public function delete(User $user, User $model): bool
    {
        return $user->can('delete-user');
    }

    /**
     * Bulk delete
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-user');
    }

    

    public function restore(User $user, User $model): bool
    {
        return $user->can('delete-user');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->can('delete-user');
    }
}

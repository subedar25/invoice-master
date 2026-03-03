<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Timesheet;

class TimesheetPolicy
{
   

    /**
     * View list (index / datatable / calendar)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('list-timesheets');
    }

    /**
     * View a single timesheet.
     * Admin/Super Admin: can view any (if they have list-timesheets).
     * Others: can view only their own timesheet.
     */
    public function view(User $user, Timesheet $timesheet): bool
    {
        if (!$user->can('list-timesheets')) {
            return false;
        }
        if ($user->hasAnyRole(['Admin User', 'Super Admin'])) {
            return true;
        }
        return $timesheet->user_id === $user->id;
    }

   

    /**
     * Create a timesheet
     * - Admin/Superadmin: can create for anyone
     * - User: only for themselves (clock-in)
     */
    public function create(User $user): bool
    {
        return $user->can('create-timesheet');
    }

   

    /**
     * Update timesheet (inline edit / edit modal)
     */
    public function update(User $user, Timesheet $timesheet): bool
    {
        // Admins can edit everything
        if ($user->hasAnyRole(['Admin User', 'Super Admin'])) {
            return true;
        }

        // User can edit ONLY their own timesheet
        return $timesheet->user_id === $user->id;
    }

    

    /**
     * Delete a single timesheet
     */
    public function delete(User $user, Timesheet $timesheet): bool
    {
        return $user->can('delete-timesheet');
    }

    /**
     * Bulk delete
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-timesheet');
    }

   

    /**
     * Duplicate timesheet
     */
    public function duplicate(User $user, Timesheet $timesheet): bool
    {
        return $user->hasAnyRole(['Admin User', 'Super Admin']);
    }

    

    public function restore(User $user, Timesheet $timesheet): bool
    {
        return $user->hasAnyRole(['Admin User', 'Super Admin']);
    }

    public function forceDelete(User $user, Timesheet $timesheet): bool
    {
        return $user->hasAnyRole(['Admin User', 'Super Admin']);
    }
}

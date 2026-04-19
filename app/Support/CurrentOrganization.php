<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\User;

final class CurrentOrganization
{
    public static function id(): ?int
    {
        $id = (int) session('current_organization_id', 0);
        if ($id > 0) {
            return $id;
        }

        $user = auth()->user();
        if ($user instanceof User && $user->last_selected_organization_id) {
            return (int) $user->last_selected_organization_id;
        }

        return null;
    }

    public static function idOrAbort(): int
    {
        $id = self::id();
        if ($id === null || $id < 1) {
            abort(403, 'Select an organization first.');
        }

        return $id;
    }

    /**
     * Organization from the header switcher (`current_organization_id`) for assigning users when the editor is not a system user.
     */
    public static function idForUserAssignment(): ?int
    {
        $id = (int) session('current_organization_id', 0);
        if ($id < 1) {
            return null;
        }

        return Organization::query()->whereKey($id)->exists() ? $id : null;
    }
}

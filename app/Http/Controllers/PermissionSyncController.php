<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;

class PermissionSyncController extends Controller
{
    public function getPermissions(User $user)
    {
        $permissions = $user->getDirectPermissions()
            ->pluck('name')
            ->merge($user->getPermissionsViaRoles()->pluck('name'))
            ->unique()
            ->values();

        return response()->json([
            'permissions' => $permissions
        ]);
    }
}

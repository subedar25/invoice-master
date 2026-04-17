<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationContextController extends Controller
{
    public function switch(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
        ]);

        $user = $request->user();
        $organizationId = (int) $validated['organization_id'];

        $isSystemUser = ($user->user_type ?? '') === 'systemuser';

        $allowed = $isSystemUser
            ? Organization::whereKey($organizationId)->exists()
            : $user->organizations()->whereKey($organizationId)->exists();

        if (! $allowed) {
            abort(403, 'You do not have access to this organization.');
        }

        session(['current_organization_id' => $organizationId]);
        session([
            'user_organization_ids' => $isSystemUser
                ? Organization::orderBy('name')->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
                : $user->organizations()->orderBy('name')->pluck('organizations.id')->map(fn ($id) => (int) $id)->values()->all(),
        ]);

        $user->forceFill([
            'last_selected_organization_id' => $organizationId,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Organization switched.',
                'organization_id' => $organizationId,
            ]);
        }

        return redirect()->back();
    }
}


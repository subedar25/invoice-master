<?php
namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Core\User\Services\UserService;
use App\Http\Requests\MasterApp\User\UserStoreRequest;
use App\Http\Requests\MasterApp\User\UserUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\AppNotification;
use App\Notifications\RoleUpdatedNotification;
use App\Core\Email\Services\EmailService;
use App\Core\File\Services\FileManagementService;
use App\Models\UserDocument;
use App\Support\CurrentOrganization;
use App\Support\UserDepartmentAuthorization;

class UserController extends Controller
{
    private EmailService $emailService;
    private FileManagementService $fileService;

    public function __construct(EmailService $emailService, FileManagementService $fileService)
    {
        $this->emailService = $emailService;
        $this->fileService = $fileService;
    }

    public function index(UserService $service): View
    {
        $authUser = auth()->user();
        $currentOrganizationId = (int) session('current_organization_id', 0);

        $users = $service->getUsersForIndex($authUser, $currentOrganizationId);
        $filterDepartments = $currentOrganizationId > 0
            ? $service->getDepartmentsByOrganization($currentOrganizationId)
            : collect();
        $filterDesignations = $currentOrganizationId > 0
            ? $service->getDesignationsByOrganization($currentOrganizationId)
            : collect();

        return view('masterapp.users.index', compact(
            'users',
            'currentOrganizationId',
            'filterDepartments',
            'filterDesignations'
        ));
    }

    public function create()
    {
        $service = app(UserService::class);
        $orgIds = [];
        $oid = CurrentOrganization::idForUserAssignment();
        if ($oid) {
            $orgIds = [$oid];
        }
        $reportingManagers = $this->reportingManagersForOrganizationIds($orgIds, null);

        return view('masterapp.users.create', [
            'roles' => $this->rolesForOrganizationIds($orgIds)->pluck('name', 'id'),
            'departments' => $this->departmentsForCurrentContext(),
            'designations' => $this->designationsForCurrentContext(),
            'organizations' => $service->getAccessibleOrganizations(auth()->user()),
            'reportingManagers' => $reportingManagers,
        ]);
    }


    public function store(UserStoreRequest $request, UserService $service): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        // Create the user first so we can store files under users/<id>/...
        $data['photo'] = null;
        $data['other_documents_data'] = [];
        $user = $service->create($data);

        if ($request->hasFile('photo')) {
            $user->photo = $this->fileService->upload($request->file('photo'), "users/{$user->id}/photo");
            $user->save();
        }

        $otherDocumentsData = $this->storeUserDocuments($request, $user);
        if (!empty($otherDocumentsData)) {
            $user->userDocuments()->createMany($otherDocumentsData);
        }

        // Send welcome email to the newly created user
        $this->sendWelcomeEmail($user);

        // Send universal notification for user creation
        // AppNotification::notify_event('user.created', $user, auth()->user() ?? $user);

        //  If request is AJAX to return JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'User created successfully'
            ], 201);
        }

        //  Normal form submit to redirect
        return redirect()
            ->route('masterapp.users.index')
            ->with('success', 'User created successfully');
    }


    public function edit(int $id, UserService $service):View
    {
        $user = $service->get($id);
        $this->ensureNotSystemUser($user);
        $orgIds = $user->organizations->pluck('id')->toArray();
        $reportingManagers = $this->reportingManagersForOrganizationIds($orgIds, $user->id);

        $organizations = $service->getAccessibleOrganizations(auth()->user());

        return view('masterapp.users.edit', [
            'user' => $user,
            'roles' => $this->rolesForOrganizationIds($orgIds)->pluck('name', 'id'),
            'userRoles' => $user->roles->pluck('name','id')->toArray(),
            'departments' => $this->departmentsForCurrentContext(),
            'designations' => $this->designationsForCurrentContext(),
            'organizations' => $organizations,
            'reportingManagers' => $reportingManagers,
        ]);
    }

    public function update(UserUpdateRequest $request, int $id, UserService $service): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $user = $service->get($id);
        $this->ensureNotSystemUser($user);
        $oldRoles = $user->roles->pluck('name')->toArray();

        if ($request->hasFile('photo')) {
            $this->fileService->delete($user->photo);
            $data['photo'] = $this->fileService->upload($request->file('photo'), "users/{$user->id}/photo");
        } elseif ($request->boolean('remove_photo')) {
            $this->fileService->delete($user->photo);
            $data['photo'] = null;
        }

        $data['other_documents_data'] = $this->storeUserDocuments($request, $user);

        if ($request->has('remove_documents')) {
            $docsToDelete = \App\Models\UserDocument::whereIn('id', $request->input('remove_documents'))
                ->where('user_id', $user->id)
                ->get();
            
            foreach ($docsToDelete as $doc) {
                $this->fileService->delete($doc->file_path);
                $doc->delete();
            }
        }

        $updatedUser = $service->update($id, $data);

        // Send universal notification for user update
        // AppNotification::notify_event('user.updated', $updatedUser, auth()->user() ?? $updatedUser);

        // Check if roles were updated and send notification
        $newRoles = $updatedUser->roles->pluck('name')->toArray();
        if ($oldRoles !== $newRoles) {
            // LEGACY NOTIFICATION CODE - COMMENTED OUT FOR REFERENCE
            // Notify the user about role changes
            // $updatedUser->sendRoleUpdatedNotification($oldRoles, $newRoles);

            // Notify admins about role changes (excluding current user if they're an admin)
            // $this->notifyAdminsAboutRoleUpdate($updatedUser, $oldRoles, $newRoles);

            // Send universal notification for role update
            // AppNotification::notify_event('role.updated', $updatedUser, auth()->user() ?? $updatedUser);
        }

        //  If request is AJAX → return JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'User updated successfully'
            ], 201);
        }

        //  Normal form submit → redirect
        return redirect()
            ->route('masterapp.users.index')
            ->with('success', 'User updated successfully');
    }

    public function destroy(int $id, UserService $service, User $user=null): JsonResponse {
        $user = $service->get($id);
        $this->ensureNotSystemUser($user);

        $this->fileService->delete($user->photo);
        foreach ($user->userDocuments as $document) {
            $this->fileService->delete($document->file_path);
        }

        // Send universal notification for user deletion
        // AppNotification::notify_event('user.deleted', $user, auth()->user() ?? $user);

        $service->delete($id);
        // $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    public function destroyPhoto(User $user): JsonResponse
    {
        $this->ensureNotSystemUser($user);
        $this->fileService->delete($user->photo);
        $user->forceFill(['photo' => null])->save();

        return response()->json([
            'message' => 'Photo deleted successfully',
        ]);
    }

    public function destroyDocument(User $user, UserDocument $document): JsonResponse
    {
        $this->ensureNotSystemUser($user);
        if ((int) $document->user_id !== (int) $user->id) {
            abort(404);
        }

        $this->fileService->delete($document->file_path);
        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully',
        ]);
    }

    // public function show(int $id, UserService $service): View
    // {
    //     $user = $service->get($id);
    //     return view('masterapp.users.show', compact('user'));
    // }

    public function apiIndex(UserService $service): JsonResponse {
        $users = $service->index();
        $users = is_iterable($users)
            ? collect($users)->reject(fn ($u) => ($u->user_type ?? '') === 'systemuser')->values()
            : $users;

        return response()->json([
            'users' => $users,
        ]);
    }

    public function toggleActive(Request $request,int $id, UserService $service) : JsonResponse
    {
    $user = $service->get($id);
    $this->ensureNotSystemUser($user);

    $service->update($id, [
        'active' => ! $user->active,

    ]);

    return response()->json([
        'message' => $user->active ? 'User Deactivated.' : 'User Activated.',
        // 'active'  => ! $user->active,
    ]);
    }

    private function ensureNotSystemUser(User $user): void
    {
        if (($user->user_type ?? '') === 'systemuser') {
            abort(403, 'System user cannot be modified.');
        }
    }

  // Show modal form (AJAX)
    public function changePasswordForm(User $user)
    {
        $user->load('roles');
        return view('users.partials.change-password-form', compact('user'));
    }


    public function updatePassword(Request $request, $id): JsonResponse
    {
    $service = app(UserService::class);
    $user = $service->get((int) $id);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

    return response()->json([
        'message' => 'Password changed successfully!'
    ]);
    }


    //  Notify admins about role updates

    public function checkEmail(Request $request): JsonResponse
    {
        $service = app(UserService::class);
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        $userId = $request->user_id ?? null;

        $exists = $service->emailExistsWithTrashed($email, $userId ? (int) $userId : null);

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Email already exists' : 'Email is available'
        ]);
    }

    private function notifyAdminsAboutRoleUpdate(User $user, array $oldRoles, array $newRoles): void
    {
        $service = app(UserService::class);
        $admins = $service->getAdminUsersExcluding(auth()->id());

        foreach ($admins as $admin) {
            $admin->notify(new RoleUpdatedNotification($user, $oldRoles, $newRoles, auth()->user()));
        }
    }

    /**
     * Send welcome email to the newly created user.
     *
     * @param User $user
     * @return void
     */
    private function sendWelcomeEmail(User $user): void
    {
        $subject = "Welcome to " . config('app.name') . "!";
        $view = 'masterapp.emails.welcome'; // Welcome email template

        $data = [
            'userName' => $user->first_name . ' ' . $user->last_name,
            'appName' => config('app.name'),
        ];

        $options = [];

        $this->emailService->send($user->email, $subject, $view, $data, $options);
    }

    private function storeUserDocuments(Request $request, User $user): array
    {
        $documents = [];

        foreach ((array) $request->file('other_documents', []) as $file) {
            if (! $file) {
                continue;
            }

            $documents[] = [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $this->fileService->upload($file, "users/{$user->id}/documents"),
            ];
        }

        return $documents;
    }

    private function departmentsForCurrentContext(): \Illuminate\Database\Eloquent\Collection
    {
        $service = app(UserService::class);
        $currentOrgId = (int) session('current_organization_id', 0);
        $authUser = auth()->user();
        return $service->getDepartmentsForUserContext($authUser, $currentOrgId);
    }

    private function designationsForCurrentContext(): \Illuminate\Database\Eloquent\Collection
    {
        $service = app(UserService::class);
        $currentOrgId = (int) session('current_organization_id', 0);
        $authUser = auth()->user();
        return $service->getDesignationsForUserContext($authUser, $currentOrgId);
    }

    /**
     * JSON list of users eligible as reporting managers for the given organization ids (must belong to at least one).
     */
    public function reportingManagersByOrganizations(Request $request): JsonResponse
    {
        $auth = auth()->user();
        if (! $auth || (! $auth->can('create-user') && ! $auth->can('edit-user'))) {
            abort(403);
        }

        $ids = $request->query('organization_ids', []);
        if (! is_array($ids)) {
            $ids = $ids !== null && $ids !== '' ? [$ids] : [];
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));

        $exclude = $request->query('exclude_user_id');
        $excludeId = $exclude !== null && $exclude !== '' ? (int) $exclude : null;

        $managers = $this->reportingManagersForOrganizationIds($ids, $excludeId);

        return response()->json([
            'managers' => $managers->map(fn (User $m) => [
                'id' => $m->id,
                'first_name' => $m->first_name,
                'last_name' => $m->last_name,
                'designation_name' => $m->designation?->name,
            ])->values(),
        ]);
    }

    /**
     * @param  array<int>  $organizationIds
     */
    protected function reportingManagersForOrganizationIds(array $organizationIds, ?int $excludeUserId = null)
    {
        $service = app(UserService::class);
        return $service->getReportingManagersForOrganizationIds($organizationIds, $excludeUserId);
    }

    /**
     * JSON list of active roles for the given organization ids (role.organization_id must match).
     */
    public function rolesByOrganizations(Request $request): JsonResponse
    {
        $auth = auth()->user();
        if (! $auth || (! $auth->can('create-user') && ! $auth->can('edit-user'))) {
            abort(403);
        }

        $ids = $request->query('organization_ids', []);
        if (! is_array($ids)) {
            $ids = $ids !== null && $ids !== '' ? [$ids] : [];
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));

        $roles = $this->rolesForOrganizationIds($ids);

        return response()->json([
            'roles' => $roles->map(fn (Role $r) => [
                'id' => $r->id,
                'name' => $r->name,
            ])->values(),
        ]);
    }

    /**
     * @param  array<int>  $organizationIds
     * @return \Illuminate\Support\Collection<int, Role>
     */
    protected function rolesForOrganizationIds(array $organizationIds)
    {
        $service = app(UserService::class);
        $roles = $service->getRolesForOrganizationIds($organizationIds);

        $authUser = auth()->user();
        $currentOrganizationId = (int) session('current_organization_id', 0);
        if (! $authUser || $currentOrganizationId <= 0) {
            return $roles;
        }

        $allowedRoleIds = UserDepartmentAuthorization::mergedListRoleRestriction($authUser, $currentOrganizationId);
        if ($allowedRoleIds === null) {
            return $roles;
        }
        if ($allowedRoleIds === []) {
            return collect();
        }

        $allowedMap = array_flip(array_map('intval', $allowedRoleIds));
        return $roles->filter(fn ($role) => isset($allowedMap[(int) $role->id]))->values();
    }
}

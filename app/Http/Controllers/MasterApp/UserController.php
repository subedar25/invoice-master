<?php
namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Core\User\Services\UserService;
use App\Http\Requests\MasterApp\User\UserStoreRequest;
use App\Http\Requests\MasterApp\User\UserUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;
use App\Models\Organization;
use App\Helpers\AppNotification;
use App\Notifications\RoleUpdatedNotification;
use App\Core\Email\Services\EmailService;
use App\Core\File\Services\FileManagementService;

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
        // $users = User::latest()->paginate(10);

        // return view('masterapp.users.index', compact('users'));
        $users = $service->getAll();


        return view('masterapp.users.index', compact('users', ));
    }

    public function create()
    {
        return view('masterapp.users.create', [
            'roles' => Role::where('is_active', true)->pluck('name', 'id'),
            'departments' => Department::all(),
            'organizations' => Organization::orderBy('name')->get(['id', 'name']),
            'reportingManagers' => User::orderBy('first_name')->get(['id', 'first_name', 'last_name']),
        ]);
    }


    public function store(UserStoreRequest $request, UserService $service): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $data['photo'] = $request->hasFile('photo')
            ? $this->fileService->upload($request->file('photo'), 'users/photos')
            : null;
        $data['other_documents_data'] = $this->storeUserDocuments($request);

        $user = $service->create($data);

        // Send welcome email to the newly created user
        $this->sendWelcomeEmail($user);

        // Send universal notification for user creation
        AppNotification::notify_event('user.created', $user, auth()->user() ?? $user);

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

        return view('masterapp.users.edit', [
            'user' => $user,
            'roles' => Role::where('is_active', true)->pluck('name','id'),
            'userRoles' => $user->roles->pluck('name','id')->toArray(),
            'departments' => Department::all(),
            'organizations' => Organization::orderBy('name')->get(['id', 'name']),
            'reportingManagers' => User::where('id', '!=', $user->id)->orderBy('first_name')->get(['id', 'first_name', 'last_name']),
        ]);
    }

    public function update(UserUpdateRequest $request, int $id, UserService $service): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $user = $service->get($id);
        $oldRoles = $user->roles->pluck('name')->toArray();

        if ($request->hasFile('photo')) {
            $this->fileService->delete($user->photo);
            $data['photo'] = $this->fileService->upload($request->file('photo'), 'users/photos');
        }

        $data['other_documents_data'] = $this->storeUserDocuments($request);

        $updatedUser = $service->update($id, $data);

        // Send universal notification for user update
        AppNotification::notify_event('user.updated', $updatedUser, auth()->user() ?? $updatedUser);

        // Check if roles were updated and send notification
        $newRoles = $updatedUser->roles->pluck('name')->toArray();
        if ($oldRoles !== $newRoles) {
            // LEGACY NOTIFICATION CODE - COMMENTED OUT FOR REFERENCE
            // Notify the user about role changes
            // $updatedUser->sendRoleUpdatedNotification($oldRoles, $newRoles);

            // Notify admins about role changes (excluding current user if they're an admin)
            // $this->notifyAdminsAboutRoleUpdate($updatedUser, $oldRoles, $newRoles);

            // Send universal notification for role update
            AppNotification::notify_event('role.updated', $updatedUser, auth()->user() ?? $updatedUser);
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

        $this->fileService->delete($user->photo);
        foreach ($user->userDocuments as $document) {
            $this->fileService->delete($document->file_path);
        }

        // Send universal notification for user deletion
        AppNotification::notify_event('user.deleted', $user, auth()->user() ?? $user);

        $service->delete($id);
        // $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    // public function show(int $id, UserService $service): View
    // {
    //     $user = $service->get($id);
    //     return view('masterapp.users.show', compact('user'));
    // }

    public function apiIndex(UserService $service): JsonResponse {
        $users = $service->index();

        return response()->json([
            'users' => $users,
        ]);
    }

    public function toggleActive(Request $request,int $id, UserService $service) : JsonResponse
    {
    $user = $service->get($id);

    $service->update($id, [
        'active' => ! $user->active,

    ]);

    return response()->json([
        'message' => $user->active ? 'User Deactivated.' : 'User Activated.',
        // 'active'  => ! $user->active,
    ]);
    }

  // Show modal form (AJAX)
    public function changePasswordForm(User $user)
    {
        $user->load('roles');
        return view('users.partials.change-password-form', compact('user'));
    }


    public function updatePassword(Request $request, $id): JsonResponse
    {
    $user = User::findOrFail($id);

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
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        $userId = $request->user_id ?? null;

        $exists = User::withTrashed()
            ->where('email', $email)
            ->when($userId, function ($query) use ($userId) {
                return $query->where('id', '!=', $userId);
            })
            ->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Email already exists' : 'Email is available'
        ]);
    }

    private function notifyAdminsAboutRoleUpdate(User $user, array $oldRoles, array $newRoles): void
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Admin User', 'Super Admin']);
        })->where('id', '!=', auth()->id())->get();

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

    private function storeUserDocuments(Request $request): array
    {
        $documents = [];

        foreach ((array) $request->file('other_documents', []) as $file) {
            if (! $file) {
                continue;
            }

            $documents[] = [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $this->fileService->upload($file, 'users/documents'),
            ];
        }

        return $documents;
    }
}

<?php
namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserStatus;
use Spatie\Permission\Models\Role;
use App\Core\User\Services\UserService;
use App\Http\Requests\MasterApp\User\UserStoreRequest;
use App\Http\Requests\MasterApp\User\UserUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Publication;
use App\Models\Department;
use App\Models\Timesheet;
use App\Helpers\NotificationHelper;
use App\Helpers\AppNotification;
use App\Notifications\RoleUpdatedNotification;
use App\Core\Email\Services\EmailService;


class UserController extends Controller
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function index(UserService $service): View
    {
        // $users = User::latest()->paginate(10);

        // return view('masterapp.users.index', compact('users'));
        $publications = Publication::select('id', 'name')->get();
        $departments = Department::select('id', 'name')->get();
        $statusesList = UserStatus::all();

        $users = $service->getAll();
        $users->load('status');

        // Current shift per user (clock_in_mode) → reflect in status badge (UserStatusSeeder labels)
        $userIds = $users->pluck('id')->toArray();
        $currentShifts = Timesheet::whereIn('user_id', $userIds)
            ->whereNull('end_time')
            ->orderByDesc('start_time')
            ->get()
            ->groupBy('user_id')
            ->map->first();

        $clockInModeToStatusLabel = [
            'office'         => 'Available',
            'remote'         => 'Available - Remote',
            'out_of_office'  => 'Available - Out of Office',
            'do_not_disturb' => 'Do Not Disturb',
            'lunch'          => 'Lunch',
        ];

        return view('masterapp.users.index', compact('users', 'publications', 'departments', 'statusesList', 'currentShifts', 'clockInModeToStatusLabel'));
    }

    public function create()
    {
        $publications = Publication::select('id', 'name')->get();
        $departments = Department::select('id', 'name')->get();
    //  $statusesList = UserStatus::all()->map(function ($s) {
    //     return [
    //         'id' => $s->id,
    //         'label' => $s->label
    //     ];
    // });
        $statusesList = UserStatus::select('id', 'label')->get();
        return view('masterapp.users.create', [
            'roles' => Role::where('is_active', true)->pluck('name', 'id'),
            'userStatuses' => UserStatus::all(),
            'publications' => Publication::all(),
            'departments' => Department::all(),
            'statusesList' => UserStatus::all(),
            // compact('publications')
        ]);
    }


    public function store(UserStoreRequest $request, UserService $service): JsonResponse|RedirectResponse
    {

        $user = $service->create($request->validated());

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
        $publications = Publication::select('id', 'name')->get();
        $departments = Department::select('id', 'name')->get();
        $statusesList = UserStatus::select('id', 'label')->get();


        // print_r($user->roles->pluck('name')->toArray());exit;

        return view('masterapp.users.edit', [
            'user' => $user,
            'roles' => Role::where('is_active', true)->pluck('name','id'),
            'userRoles' => $user->roles->pluck('name','id')->toArray(),
            'publications' => Publication::pluck('name', 'id'),
            'userPublications' => $user->publications->pluck('name', 'id')->toArray(),
            'departments' => Department::all(),
            'statusesList' => UserStatus::all(),
        ]);
    }

     public function update(UserUpdateRequest $request, int $id, UserService $service): JsonResponse|RedirectResponse
    {
        // $service->update($id, $request->validated());
    // Get user before update to compare roles
        $user = $service->get($id);
        $oldRoles = $user->roles->pluck('name')->toArray();

        $updatedUser = $service->update($id, $request->validated());

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

    // Update user status via AJAX (two-way: also syncs dashboard/current timesheet)
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status_id' => ['required', 'exists:user_statuses,id'],
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'status_id' => $request->status_id,
        ]);

        $status = UserStatus::find($request->status_id);
        $label = $status ? $status->label : null;

        // Sync to current timesheet so dashboard reflects this status (two-way)
        $currentShift = Timesheet::currentShiftForUser($user->id);
        $statusLabelToClockInMode = [
            'Available'               => 'office',
            'Available - Remote'      => 'remote',
            'Available - Out of Office' => 'out_of_office',
            'Available - Lunch'       => 'lunch',
            'Lunch'                  => 'lunch',
            'Do Not Disturb'         => 'do_not_disturb',
        ];

        if ($currentShift) {
            if ($label === 'Not Available') {
                $currentShift->update(['end_time' => now()]);
            } elseif ($label && isset($statusLabelToClockInMode[$label])) {
                $currentShift->update(['clock_in_mode' => $statusLabelToClockInMode[$label]]);
            }
        } else {
            // No active shift: setting to Available (or other clock-in status) = clock them in so dashboard shows it
            if ($label && isset($statusLabelToClockInMode[$label])) {
                Timesheet::create([
                    'user_id'       => $user->id,
                    'start_time'    => now(),
                    'clock_in_mode' => $statusLabelToClockInMode[$label],
                    'type'          => 'normal_paid',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'label' => $status ? $status->label : 'N/A',
            'badge_class' => $status ? $status->badge_class : 'badge-secondary',
        ]);
    }

  // Show modal form (AJAX)
    public function changePasswordForm(User $user)
    {
        $user->load('roles', 'status');
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
}

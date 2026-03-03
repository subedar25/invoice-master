<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Core\Timesheet\Services\TimesheetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\MasterApp\Timesheet\TimesheetStoreRequest;
use App\Http\Requests\MasterApp\Timesheet\TimesheetUpdateRequest;
use App\Helpers\NotificationHelper;
use App\Helpers\AppNotification;
use Spatie\Permission\Models\Role;

class TimesheetController extends Controller
{
    protected TimesheetService $service;

    public function __construct(TimesheetService $service)
    {
        $this->service = $service;
    }
    public function index(): View
    {
        $users = User::all();
        $timesheets = Timesheet::with('user')
            ->visibleTo(auth()->user())
            ->latest('start_time')
            ->paginate(20);

        $canManageTimesheets = auth()->user()->hasAnyRole(['Admin User', 'Super Admin']);

        return view('masterapp.timesheets.index', compact('timesheets', 'users', 'canManageTimesheets'));
    }

    public function getTimesheets(Request $request)
    {
        $filters = $request->only(['user_id', 'date_from', 'date_to', 'type']);

        // Non-admin / non-super-admin: only their own timesheets
        if (!auth()->user()->hasAnyRole(['Admin User', 'Super Admin'])) {
            $filters['user_id'] = auth()->id();
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        // Handle sorting mapping
        $columns = ['user', 'start_time', 'end_time', 'hours', 'clock_in_mode', 'type', 'notes', 'actions'];
        $orderInput = $request->input('order.0');
        $orderColumn = $columns[$orderInput['column'] ?? 1] ?? 'start_time'; // Default to start_time
        $orderDir = $orderInput['dir'] ?? 'desc';

        $result = $this->service->getDataTableData($filters, $search, $start, $length, ['column' => $orderColumn, 'dir' => $orderDir]);

        $canManageTimesheets = auth()->user()->hasAnyRole(['Admin User', 'Super Admin']);

        // Transform data for DataTables
        $transformedData = $result['data']->map(function($timesheet) use ($canManageTimesheets) {
            $actions = 'N/A';
            if ($timesheet->user) {
                $actions = '<div class="action-div d-flex gap-2 no-export">
                    <a href="' . route('masterapp.entity.info', ['type' => 'users', 'id' => $timesheet->user->id]) . '" title="View user" class="action-icon entity-link">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                    </a>';
                if ($canManageTimesheets && auth()->user()->can('edit-timesheet')) {
                    $actions .= '
                    <button class="btn btn-link p-0 action-icon js-edit-timesheet" data-id="' . $timesheet->id . '" data-url="' . route('masterapp.timesheets.json', $timesheet->id) . '">
                        <i class="fa fa-edit"></i>
                    </button>';
                }
                if (auth()->user()->can('delete-timesheet')) {
                    $actions .= '
                    <button type="button" class="btn btn-link p-0 action-icon text-danger delete-item" data-url="' . route('masterapp.timesheets.destroy', $timesheet->id) . '" data-name="Timesheet" title="Delete timesheet">
                        <i class="fa fa-trash"></i>
                    </button>';
                }
                $actions .= '</div>';
            }

            return [
                'user' => $timesheet->user ? '<a href="' . route('masterapp.entity.info', ['type' => 'users', 'id' => $timesheet->user->id]) . '" class="entity-link">' . e($timesheet->user->first_name . ' ' . $timesheet->user->last_name) . '</a>' : 'N/A',
                'start_time' => $timesheet->start_time ? $timesheet->start_time->format('Y-m-d H:i:s') : '',
                'end_time' => $timesheet->end_time ? $timesheet->end_time->format('Y-m-d H:i:s') : '',
                'hours' => $timesheet->duration_hours,
                'clock_in_mode' => $timesheet->clock_in_mode_label,
                'type' => $timesheet->type_label,
                'notes' => $timesheet->notes,
                'actions' => $actions,
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $transformedData,
        ]);
    }

    public function show(Timesheet $timesheet): View
    {
        $this->authorize('view', $timesheet);

        return view('masterapp.timesheets.show', compact('timesheet'));
    }
    // public function destroy(int $id, TimesheetService $service, Timesheet $timesheet=null): JsonResponse {
    //     $service->delete($id);
    //     // $user->delete();

    //     return response()->json([
    //         'message' => 'Timesheet deleted successfully',
    //     ]);
    // }
    public function destroy(int $id, TimesheetService $service): JsonResponse
    {
        $timesheet = Timesheet::findOrFail($id);
        $userName = $timesheet->user->name;
        $date = $timesheet->start_time->format('M d, Y');

        $service->delete($id);

        // Send notification to admins about timesheet deletion
        $this->notifyAdminsAboutTimesheet(
            'Timesheet Entry Deleted',
            "A timesheet entry for {$userName} on {$date} has been deleted.",
            route('masterapp.timesheets.index')
        );

        return response()->json([
            'message' => 'Timesheet deleted successfully',
        ]);
    }

    // public function create(): View
    // {
    // $users = User::orderBy('first_name')->get();

    // return view('masterapp.timesheets.create', compact('users'));
    // }
    public function store(TimesheetStoreRequest $request): JsonResponse
    {
        $timesheet = $this->service->createTimesheet($request->validated());

        // Send universal notification for timesheet creation
        AppNotification::notify_event('timesheet.created', $timesheet, auth()->user());

        // Send notification to admins about new timesheet entry
        $this->notifyAdminsAboutTimesheet(
            'New Timesheet Entry Created',
            "A new timesheet entry has been created for {$timesheet->user->name} on {$timesheet->start_time->format('M d, Y')}.",
            route('masterapp.timesheets.show', $timesheet->id)
        );

        return response()->json([
            'success' => true,
            'message' => 'Timesheet entry created successfully.'
        ]);
    }
    public function edit(Timesheet $timesheet)
    {
    return view('masterapp.timesheets.edit', [
        'timesheet' => $timesheet,
        'users'     => User::orderBy('first_name')->get(),
    ]);
    }

    public function update(TimesheetUpdateRequest $request,Timesheet $timesheet): JsonResponse
    {
        $this->service->updateTimesheet(
            $timesheet->id,
            $request->validated()
        );

        // Send universal notification for timesheet update
        AppNotification::notify_event('timesheet.updated', $timesheet, auth()->user() ?? $timesheet->user);

        // Send notification to admins about timesheet update
        $this->notifyAdminsAboutTimesheet(
            'Timesheet Entry Updated',
            "A timesheet entry for {$timesheet->user->name} on {$timesheet->start_time->format('M d, Y')} has been updated.",
            route('masterapp.timesheets.show', $timesheet->id)
        );

        return response()->json([
            'success' => true,
            'message' => 'Timesheet updated successfully.'
        ]);
    }
    public function json(Timesheet $timesheet)
{
    return response()->json([
        'id'            => $timesheet->id,
        'user_id'       => $timesheet->user_id,
        'start_time'    => optional($timesheet->start_time)->format('Y-m-d\TH:i'),
        'end_time'      => optional($timesheet->end_time)->format('Y-m-d\TH:i'),
        'clock_in_mode' => $timesheet->clock_in_mode,
        'type'          => $timesheet->type,
        'notes'         => $timesheet->notes,
    ]);
}

    /**
     * Send notification to all admin users about timesheet changes
     */
    private function notifyAdminsAboutTimesheet(string $title, string $message, string $url): void
    {
        // Get all admin and superadmin users
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Admin User', 'Super Admin']);
        })->get();

        // Send notification to each admin (excluding the current user if they're an admin)
        foreach ($adminUsers as $admin) {
            if ($admin->id !== auth()->id()) {
                NotificationHelper::create($admin, $title, $message, $url);
            }
        }
    }

}

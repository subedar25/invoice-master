<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Core\Location\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\MasterApp\Location\LocationStoreRequest;
use App\Http\Requests\MasterApp\Location\LocationUpdateRequest;
use App\Helpers\NotificationHelper;
use App\Helpers\AppNotification;
use Spatie\Permission\Models\Role;
use App\Models\User;

class LocationController extends Controller
{
    protected LocationService $service;

    public function __construct(LocationService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $locations = Location::latest('created_at')
            ->paginate(20);

        $countries = DB::table('countries')
            ->select('id', 'name')
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $defaultCountryId = optional($countries->firstWhere('name', 'India'))->id
            ?? optional($countries->firstWhere('id', 101))->id
            ?? optional($countries->first())->id;

        $statesByCountry = DB::table('states')
            ->select('id', 'country_id', 'name')
            ->where('status', 1)
            ->orderBy('name')
            ->get()
            ->groupBy('country_id')
            ->map(fn ($items) => $items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])->values())
            ->toArray();

        return view('masterapp.locations.index', compact('locations', 'countries', 'defaultCountryId', 'statesByCountry'));
    }

    public function getLocations(Request $request)
    {
        $filters = $request->only(['city', 'state', 'country']);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        // Handle sorting mapping
        $columns = ['name', 'address', 'country', 'state', 'city', 'postal_code', 'actions'];
        $orderInput = $request->input('order.0');
        $orderColumn = $columns[$orderInput['column'] ?? 0] ?? 'created_at';
        $orderDir = $orderInput['dir'] ?? 'desc';

        $result = $this->service->getDataTableData($filters, $search, $start, $length, ['column' => $orderColumn, 'dir' => $orderDir]);

        // Transform data for DataTables
        $transformedData = $result['data']->map(function($location) {
            return [
                'created_at' => $location->created_at?->toIso8601String() ?? '',
                'name' => $location->name ? '<a href="' . (route('masterapp.entity.info', ['type' => 'locations', 'id' => $location->id, 'tab' => 'Info'])) . '" class="entity-link">' . e($location->name) . '</a>' : 'N/A',
                'address' => e($location->address),
                'country' => e($location->country),
                'state' => e($location->state),
                'city' => e($location->city),
                'postal_code' => e($location->postal_code),
                'actions' => auth()->user()->can('locations')
                    ? '<div class="action-div d-flex gap-2">
                    <button class="btn btn-link p-0 action-icon js-edit-location" data-id="' . $location->id . '" data-url="' . route('masterapp.locations.json', $location->id) . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-link p-0 action-icon text-danger delete-item" data-url="' . route('masterapp.locations.destroy', $location->id) . '" data-name="Location" title="Delete location">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>'
                    : '<div class="action-div d-flex gap-2"></div>',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $transformedData,
        ]);
    }

    public function show(Location $location): View
    {
        return view('masterapp.locations.show', compact('location'));
    }

    public function destroy(int $id, LocationService $service): JsonResponse
    {
        $location = Location::findOrFail($id);
        $locationName = $location->name;

        $service->deleteLocation($id);

        // Send notification to admins about location deletion
        $this->notifyAdminsAboutLocation(
            'Location Entry Deleted',
            "A location entry for {$locationName} has been deleted.",
            route('masterapp.locations.index')
        );

        return response()->json([
            'message' => 'Location deleted successfully',
        ]);
    }

    public function store(LocationStoreRequest $request): JsonResponse
    {
        $location = $this->service->createLocation($request->validated());

        // Send universal notification for location creation
        AppNotification::notify_event('location.created', $location, auth()->user());

        // Send notification to admins about new location entry
        $this->notifyAdminsAboutLocation(
            'New Location Entry Created',
            "A new location entry has been created for {$location->name}.",
            route('masterapp.locations.show', $location->id)
        );

        return response()->json([
            'success' => true,
            'message' => 'Location entry created successfully.'
        ]);
    }

    public function edit(Location $location)
    {
        return view('masterapp.locations.edit', [
            'location' => $location,
        ]);
    }

    public function update(LocationUpdateRequest $request, Location $location): JsonResponse
    {
        $this->service->updateLocation(
            $location->id,
            $request->validated()
        );

        // Send universal notification for location update
        AppNotification::notify_event('location.updated', $location, auth()->user() ?? $location);

        // Send notification to admins about location update
        $this->notifyAdminsAboutLocation(
            'Location Entry Updated',
            "A location entry for {$location->name} has been updated.",
            route('masterapp.locations.show', $location->id)
        );

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully.'
        ]);
    }

    public function json($id)
    {
        $location = Location::withTrashed()->findOrFail($id);

        return response()->json([
            'id' => $location->id,
            'name' => $location->name,
            'address' => $location->address,
            'country' => $location->country,
            'state' => $location->state,
            'city' => $location->city,
            'postal_code' => $location->postal_code,
            // 'phone' => $location->phone,
            // 'show_map' => $location->show_map,
            // 'show_map_link' => $location->show_map_link,
        ]);
    }

    /**
     * Check if location name is unique among non-deleted locations.
     * Allows reusing a name when the existing record is soft deleted.
     */
    public function checkUnique(Request $request): JsonResponse
    {
        $name = $request->input('name');
        $excludeId = $request->input('exclude_id');

        $query = Location::query()
            ->where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json(!$exists);
    }

    /**
     * Send notification to all admin users about location changes
     */
    private function notifyAdminsAboutLocation(string $title, string $message, string $url): void
    {
        // Get all admin and superadmin users
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Admin User', 'System Admin']);
        })->get();

        // Send notification to each admin (excluding the current user if they're an admin)
        foreach ($adminUsers as $admin) {
            if ($admin->id !== auth()->id()) {
                NotificationHelper::create($admin, $title, $message, $url);
            }
        }
    }
}

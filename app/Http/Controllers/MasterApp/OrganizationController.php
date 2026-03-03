<?php

namespace App\Http\Controllers\MasterApp;

use App\Core\Organization\Services\OrganizationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\MasterApp\Organization\OrganizationStoreRequest;
use App\Http\Requests\MasterApp\Organization\OrganizationUpdateRequest;
use App\Models\Client;
use App\Models\ClientAmenity;
use App\Models\ClientTypes;
use App\Models\ClientContact;
use App\Models\ClientLocationLink;
use App\Models\Location;
use App\Models\Publication;
use App\Models\RestaurantMeal;
use App\Models\RestaurantPriceRange;
use App\Models\Season;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class OrganizationController extends Controller
{
    public function index(OrganizationService $service): View
    {
        return view('masterapp.organizations.index');
    }

    public function data(Request $request)
    {
        $query = Client::query()->select([
            'id',
            'name',
            'open',
            'active',
            'added_timestamp',
            'seasons_open',
        ])->with('clientTypes');

        $search = trim((string) $request->get('organization_search', ''));
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $active = $request->get('active');
        if ($active !== null && $active !== '') {
            $query->where('active', (int) $active);
        }

        return DataTables::of($query)
            ->addColumn('organization_types', function ($organization) {
                $parentTypes = $organization->clientTypes->where('parent_id', null);
                return $parentTypes->pluck('name')->implode(', ') ?: '—';
            })
            ->addColumn('seasons', function ($organization) {
                $ids = $organization->seasons_open ?? [];
                if (empty($ids)) {
                    return '—';
                }
                $names = Season::whereIn('id', $ids)->orderBy('name')->pluck('name');
                return $names->implode(', ');
            })
            ->editColumn('open', function ($organization) {
                return (int) $organization->open === 1 ? 'Yes' : 'No';
            })
            ->editColumn('active', function ($organization) {
                $isActive = (int) $organization->active === 1;
                if (auth()->user()->can('activate-deactivate-organization')) {
                    $toggleUrl = route('masterapp.organizations.toggle-active', $organization->id);
                    $checked = $isActive ? ' checked' : '';
                    return '<div class="custom-control custom-switch d-inline-block">
                        <input type="checkbox" class="custom-control-input org-active-toggle" id="active-' . (int) $organization->id . '" data-id="' . (int) $organization->id . '" data-url="' . e($toggleUrl) . '"' . $checked . '>
                        <label class="custom-control-label" for="active-' . (int) $organization->id . '"></label>
                    </div>';
                }
                return $isActive
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-secondary">Inactive</span>';
            })
            ->editColumn('added_timestamp', function ($organization) {
                return $organization->added_timestamp ?: 'N/A';
            })
            ->editColumn('name', function ($organization) {
                $showUrl = route('masterapp.organizations.show', $organization->id);
                return '<a href="' . e($showUrl) . '" class="text-primary">' . e($organization->name) . '</a>';
            })
            ->addColumn('actions', function ($organization) {
                $canEdit = auth()->user()->can('edit-organization');
                $canDelete = auth()->user()->can('delete-organization');

                if (! $canEdit && ! $canDelete) {
                    return '<span class="text-muted">N/A</span>';
                }

                $editUrl = route('masterapp.organizations.edit', $organization->id);
                $deleteUrl = route('masterapp.organizations.destroy', $organization->id);
                return '<div class="action-div">
                            ' . ($canEdit
                                ? '<a href="' . $editUrl . '" class="btn btn-link p-0 action-icon"
                                    title="Edit ' . e($organization->name) . '">
                                    <i class="fa fa-edit"></i>
                                   </a>'
                                : '') . '
                            ' . ($canDelete
                                ? '<button type="button"
                                    class="btn btn-link p-0 action-icon text-danger delete-item"
                                    data-url="' . $deleteUrl . '"
                                    data-name="' . e($organization->name) . '"
                                    title="Delete ' . e($organization->name) . '">
                                    <i class="fa fa-trash"></i>
                                   </button>'
                                : '') . '
                        </div>';
            })
            ->rawColumns(['actions', 'active', 'name'])
            ->make(true);
    }

    public function create(): View
    {
        $users = User::select('id', 'first_name', 'last_name')->orderBy('first_name')->get();
        $seasons = Season::orderBy('name')->get();
        $publications = Publication::orderBy('name')->get();
        $parentClientTypes = ClientTypes::whereNull('parent_id')->where('status', 'active')->with(['children', 'amenities'])->orderBy('display_order')->orderBy('name')->get();
        $allClientTypes = ClientTypes::where('status', 'active')->orderBy('display_order')->orderBy('name')->get(['id', 'name', 'parent_id']);
        $selectedClientTypeIds = old('client_type_ids') !== null
            ? array_map('intval', (array) old('client_type_ids'))
            : [];
        $restaurantPriceRanges = RestaurantPriceRange::orderBy('name')->get();
        $restaurantMeals = RestaurantMeal::orderBy('name')->get();

        return view('masterapp.organizations.create', compact('users', 'seasons', 'publications', 'parentClientTypes', 'allClientTypes', 'selectedClientTypeIds', 'restaurantPriceRanges', 'restaurantMeals'));
    }

    public function show(int $id, OrganizationService $service): View
    {
        $organization = $service->get($id);
        $linkedPhysicalLocation = ClientLocationLink::query()
            ->with('location')
            ->where('client_id', $id)
            ->where('location_type', 'physical')
            ->first();
        $linkedPhysicalLocation = $linkedPhysicalLocation ? $linkedPhysicalLocation->location : null;

        $linkedMailingLocation = ClientLocationLink::query()
            ->with('location')
            ->where('client_id', $id)
            ->where('location_type', 'mailing')
            ->first();
        $linkedMailingLocation = $linkedMailingLocation ? $linkedMailingLocation->location : null;

        $seasons = Season::orderBy('name')->get();
        $seasonsOpen = $organization->seasons_open ?? [];
        $selectedSeasonIds = $this->normalizeSeasonsOpenToIds($seasonsOpen);
        $selectedAmenityIds = $organization->amenities->pluck('id')->toArray();
        $publications = Publication::orderBy('name')->get();
        $parentClientTypes = ClientTypes::whereNull('parent_id')->where('status', 'active')->with(['children', 'amenities'])->orderBy('display_order')->orderBy('name')->get();
        $allClientTypes = ClientTypes::where('status', 'active')->orderBy('display_order')->orderBy('name')->get(['id', 'name', 'parent_id']);
        $selectedClientTypeIds = $organization->clientTypes->pluck('id')->toArray();
        $restaurantPriceRanges = RestaurantPriceRange::orderBy('name')->get();
        $restaurantMeals = RestaurantMeal::orderBy('name')->get();
        $selectedMealIds = $organization->restaurantMeals->pluck('id')->toArray();

        return view('masterapp.organizations.show', compact('organization', 'linkedPhysicalLocation', 'linkedMailingLocation', 'seasons', 'selectedSeasonIds', 'selectedAmenityIds', 'publications', 'parentClientTypes', 'allClientTypes', 'selectedClientTypeIds', 'restaurantPriceRanges', 'restaurantMeals', 'selectedMealIds'));
    }

    public function store(
        OrganizationStoreRequest $request,
        OrganizationService $service
    ) {
        $data = $request->validated();
        $physicalLocationId = $request->input('physical_location_id') ?: $request->input('location_id');
        $mailingLocationId = $request->input('mailing_location_id');
        $amenityIds = array_values(array_map('intval', (array) $request->input('amenity_ids', [])));
        $clientTypeIds = array_values(array_map('intval', (array) $request->input('client_type_ids', [])));
        $restaurantMealIds = array_values(array_map('intval', (array) $request->input('restaurant_meal_ids', [])));
        unset(
            $data['physical_location_id'], $data['mailing_location_id'], $data['location_id'],
            $data['amenity_ids'],
            $data['client_type_ids'],
            $data['restaurant_meal_ids']
        );

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->storeLogoFile($request->file('logo'));
        } else {
            $data['logo'] = null;
        }

        $data['seasons_open'] = array_values(array_map('intval', (array) ($data['seasons_open'] ?? [])));
        $data['advertiser'] = array_values(array_map('intval', (array) ($data['advertiser'] ?? [])));

        $organization = $service->create($data);
        $this->syncLocationLinks($organization->id, $physicalLocationId, $mailingLocationId);
        $this->syncClientAmenities($organization->id, $amenityIds);
        $this->syncClientTypes($organization->id, $clientTypeIds);
        $this->syncRestaurantMeals($organization->id, $restaurantMealIds);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Organization created successfully.'], 201);
        }

        return redirect()
            ->route('masterapp.organizations.index')
            ->with('success', 'Organization created successfully.');
    }

    public function edit(int $id, OrganizationService $service): View
    {
        $organization = $service->get($id);
        $users = User::select('id', 'first_name', 'last_name')->orderBy('first_name')->get();
        $linkedPhysicalLocation = ClientLocationLink::query()
            ->with('location')
            ->where('client_id', $id)
            ->where('location_type', 'physical')
            ->first();

        $linkedPhysicalLocation = $linkedPhysicalLocation ? $linkedPhysicalLocation->location : null;

        $linkedMailingLocation = ClientLocationLink::query()
            ->with('location')
            ->where('client_id', $id)
            ->where('location_type', 'mailing')
            ->first();

        $linkedMailingLocation = $linkedMailingLocation ? $linkedMailingLocation->location : null;

        $seasons = Season::orderBy('name')->get();
        $seasonsOpen = $organization->seasons_open ?? [];
        $selectedSeasonIds = $this->normalizeSeasonsOpenToIds($seasonsOpen);
        $selectedAmenityIds = old('amenity_ids') !== null
            ? array_map('intval', (array) old('amenity_ids'))
            : $organization->amenities->pluck('id')->toArray();
        $publications = Publication::orderBy('name')->get();
        $parentClientTypes = ClientTypes::whereNull('parent_id')->where('status', 'active')->with(['children', 'amenities'])->orderBy('display_order')->orderBy('name')->get();
        $allClientTypes = ClientTypes::where('status', 'active')->orderBy('display_order')->orderBy('name')->get(['id', 'name', 'parent_id']);
        $selectedClientTypeIds = old('client_type_ids') !== null
            ? array_map('intval', (array) old('client_type_ids'))
            : $organization->clientTypes->pluck('id')->toArray();
        $restaurantPriceRanges = RestaurantPriceRange::orderBy('name')->get();
        $restaurantMeals = RestaurantMeal::orderBy('name')->get();
        $selectedMealIds = old('restaurant_meal_ids') !== null
            ? array_map('intval', (array) old('restaurant_meal_ids'))
            : $organization->restaurantMeals->pluck('id')->toArray();

        return view('masterapp.organizations.edit', compact('organization', 'users', 'linkedPhysicalLocation', 'linkedMailingLocation', 'seasons', 'selectedSeasonIds', 'selectedAmenityIds', 'publications', 'parentClientTypes', 'allClientTypes', 'selectedClientTypeIds', 'restaurantPriceRanges', 'restaurantMeals', 'selectedMealIds'));
    }

    public function update(
        OrganizationUpdateRequest $request,
        int $id,
        OrganizationService $service
    ) {
        $data = $request->validated();
        $physicalLocationId = $request->input('physical_location_id') ?: $request->input('location_id');
        $mailingLocationId = $request->input('mailing_location_id');
        $amenityIds = array_values(array_map('intval', (array) $request->input('amenity_ids', [])));
        $clientTypeIds = array_values(array_map('intval', (array) $request->input('client_type_ids', [])));
        $restaurantMealIds = array_values(array_map('intval', (array) $request->input('restaurant_meal_ids', [])));
        unset(
            $data['physical_location_id'], $data['mailing_location_id'], $data['location_id'],
            $data['amenity_ids'],
            $data['client_type_ids'],
            $data['restaurant_meal_ids'],
            $data['logo_remove']
        );

        if ($request->boolean('logo_remove')) {
            $data['logo'] = null;
            $this->deleteLogoFileIfExists(Client::find($id)?->logo);
        } elseif ($request->hasFile('logo')) {
            $client = Client::find($id);
            $this->deleteLogoFileIfExists($client?->logo);
            $data['logo'] = $this->storeLogoFile($request->file('logo'));
        } else {
            unset($data['logo']);
        }

        if (array_key_exists('seasons_open', $data)) {
            $data['seasons_open'] = array_values(array_map('intval', (array) $data['seasons_open']));
        }
        if (array_key_exists('advertiser', $data)) {
            $data['advertiser'] = array_values(array_map('intval', (array) $data['advertiser']));
        }

        $service->update($id, $data);
        $this->syncLocationLinks($id, $physicalLocationId, $mailingLocationId);
        $this->syncClientAmenities($id, $amenityIds);
        $this->syncClientTypes($id, $clientTypeIds);
        $this->syncRestaurantMeals($id, $restaurantMealIds);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Organization updated successfully.'], 200);
        }

        return redirect()
            ->route('masterapp.organizations.index')
            ->with('success', 'Organization updated successfully.');
    }

    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $request->validate(['active' => 'required|boolean']);
        $client = Client::findOrFail($id);
        $client->update(['active' => (bool) $request->input('active')]);
        return response()->json([
            'message' => 'Organization active status updated.',
            'active' => (int) $client->active,
        ]);
    }

    public function destroy(int $id, OrganizationService $service)
    {
        $service->delete($id);

        return response()->json([
            'message' => 'Organization deleted successfully.',
        ]);
    }

    public function suggestLocations(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        if (Str::length($search) < 2) {
            return response()->json(['data' => []]);
        }

        $locations = Location::query()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('state', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%')
                    ->orWhere('postal_code', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'address', 'city', 'state', 'country', 'postal_code']);

        $data = $locations->map(function (Location $location) {
            $parts = array_filter([
                $location->address,
                $location->city,
                $location->state,
                $location->postal_code,
                $location->country,
            ]);

            return [
                'id' => $location->id,
                'name' => $location->name,
                'address' => $location->address,
                'city' => $location->city,
                'state' => $location->state,
                'country' => $location->country,
                'postal_code' => $location->postal_code,
                'display' => trim($location->name . ' - ' . implode(', ', $parts), ' -'),
            ];
        });

        return response()->json(['data' => $data]);
    }

    private function syncLocationLinks(int $clientId, ?string $physicalLocationId, ?string $mailingLocationId): void
    {
        ClientLocationLink::query()
            ->where('client_id', $clientId)
            ->whereIn('location_type', ['physical', 'mailing'])
            ->delete();

        if (! empty($physicalLocationId)) {
            ClientLocationLink::create([
                'client_id' => $clientId,
                'location_id' => (int) $physicalLocationId,
                'location_type' => 'physical',
                'added_by' => auth()->id(),
                'created_date' => now(),
            ]);
        }

        if (! empty($mailingLocationId)) {
            ClientLocationLink::create([
                'client_id' => $clientId,
                'location_id' => (int) $mailingLocationId,
                'location_type' => 'mailing',
                'added_by' => auth()->id(),
                'created_date' => now(),
            ]);
        }
    }

    private function syncClientAmenities(int $clientId, array $amenityIds): void
    {
        $client = Client::find($clientId);
        if ($client) {
            $client->amenities()->sync(array_filter($amenityIds, fn ($id) => (int) $id > 0));
        }
    }

    private function syncClientTypes(int $clientId, array $clientTypeIds): void
    {
        $client = Client::find($clientId);
        if ($client) {
            $client->clientTypes()->sync(array_filter($clientTypeIds, fn ($id) => (int) $id > 0));
        }
    }

    private function syncRestaurantMeals(int $clientId, array $mealIds): void
    {
        $client = Client::find($clientId);
        if ($client) {
            $client->restaurantMeals()->sync(array_filter($mealIds, fn ($id) => (int) $id > 0));
        }
    }

    private function storeLogoFile(UploadedFile $file): string
    {
        $dir = public_path('clients');
        File::ensureDirectoryExists($dir);

        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'png';
        $name = Str::random(40) . '.' . $ext;
        $file->move($dir, $name);

        return $name;
    }

    private function deleteLogoFileIfExists(?string $logo): void
    {
        if (! $logo) {
            return;
        }
        $path = public_path('clients/' . $logo);
        if (File::isFile($path)) {
            File::delete($path);
        }
    }

    /**
     * Normalize seasons_open (may be legacy names or ids) to array of season ids.
     */
    private function normalizeSeasonsOpenToIds(array $seasonsOpen): array
    {
        if (empty($seasonsOpen)) {
            return [];
        }
        $first = reset($seasonsOpen);
        if (is_numeric($first)) {
            return array_map('intval', array_values($seasonsOpen));
        }

        return Season::whereIn('name', $seasonsOpen)->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
    }
}

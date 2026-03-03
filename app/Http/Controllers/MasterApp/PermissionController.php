<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Module;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use App\Core\Permissions\Services\PermissionsService;
use App\Http\Requests\MasterApp\Permissions\PermissionsStoreRequest;
use App\Http\Requests\MasterApp\Permissions\PermissionsUpdateRequest;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    //


    public function index()
    {
        $modules = Module::all();

        $permissions = Permission::with('module')->latest()->paginate(200);
        // No longer fetching modules here. The view will be empty initially.
        return view('masterapp.permissions.index', compact('modules', 'permissions'));

        // Pass the $modules variable to the view
        // return view('masterapp.permissions.index', compact('modules'));
    }

    //  public function getPermissions(Request $request)
    // {

    //     if ($request->ajax()) {
    //         $query = Permission::with('module');

    //         // Apply the module filter if it's set
    //         if ($request->has('module_filter') && !empty($request->get('module_filter'))) {
    //             $query->where('module_id', $request->get('module_filter'));
    //         }

    //         return datatables()->of($query)
    //             ->addColumn('checkbox', function ($permission) {
    //                 return '<input type="checkbox" class="select-row" value="' . $permission->id . '">';
    //             })
    //             // Add this new column for the module name
    //             ->addColumn('module_name', function ($permission) {
    //                 // Use a default value like 'N/A' if a permission has no module
    //                 return $permission->module ? $permission->module->name : 'N/A';
    //             })

    //             ->rawColumns(['checkbox'])
    //             ->make(true);
    //     }

    //     return response()->json(['error' => 'Not an AJAX request'], 400);
    // }

    public function create()
    {
        $modules = Module::orderBy('name')->pluck('name', 'id');

        // Pass the modules to the view
        return view('masterapp.permissions.create', compact('modules'));
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255|unique:permissions,name',
    //         'display_name' => 'nullable|string|max:255',
    //     ]);
    //     $slug = Str::slug($request->name);
    //     Permission::create([
    //         'name' => $request->name,
    //         'display_name' => $request->display_name,
    //         'module_id'     => $request->module_id,
    //         'slug'          => $slug,
    //         'guard_name'    => 'web',
    //     ]);


    //     return response()->json([
    //         'success' => 'Permission created successfully!',
    //         'redirect' => route('masterapp.permissions.index')
    //     ], 200);
    // }


    public function store(
        PermissionsStoreRequest $request,
        PermissionsService $service
    ) {
        $permission = $service->create($request->validated());
        $permission->load('module');

        return response()->json([
            'success' => 'Permission created successfully!',
            'redirect' => route('masterapp.permissions.index'),
            'permission' => $this->formatPermissionPayload($permission),
        ], 200);
    }


    // public function edit(Permission $permission)
    // {
    //     $modules = Module::orderBy('name')->pluck('name', 'id');

    //     // Pass both the permission and the modules to the view
    //     return view('masterapp.permissions.edit', compact('permission', 'modules'));
    // }


    public function edit(int $id, PermissionsService $service)
    {
        $modules = Module::orderBy('name')->pluck('name', 'id');
        $permission = $service->get($id);

        return view('masterapp.permissions.edit',  compact('permission', 'modules'));
    }

    public function update(PermissionsUpdateRequest $request, int $id, PermissionsService $service)
    {

        $service->update($id, $request->validated());
        $permission = $service->get($id);
        $permission->load('module');

        return response()->json([
            'message' => 'Permission updated successfully!',
            'redirect' => route('masterapp.permissions.index'),
            'permission' => $this->formatPermissionPayload($permission),
        ], 200);
    }

    public function destroy(int $id, PermissionsService $service)
    {
        $service->delete($id);
        return response()->json(['message' => 'Permission deleted successfully!'], 200);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        $permission->is_active = ! (bool) $permission->is_active;
        $permission->save();

        // Clear permission cache so list/delete (and all) checks use fresh is_active
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'message' => $permission->is_active
                ? 'Permission activated successfully.'
                : 'Permission deactivated successfully.',
            'is_active' => (bool) $permission->is_active,
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        try {

            $request->validate([
                'ids' => 'required|array',
                // Use the explicit table name 'permissions' for the 'exists' rule
                'ids.*' => 'required|integer|exists:permissions,id'
            ]);

            $ids = $request->input('ids');

            // Use the Permission model to delete the selected permissions
            // The 'delete()' method returns the number of deleted rows
            $deletedCount = Permission::whereIn('id', $ids)->delete();

            return response()->json([
                'message' => "{$deletedCount} permission(s) deleted successfully!"
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Permissions Bulk Deletion Error: ' . $e->getMessage());

            // Return a generic error message to the user
            return response()->json(['message' => 'An error occurred while trying to delete the permission(s).'], 500);
        }
    }

    private function formatPermissionPayload(Permission $permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'display_name' => $permission->display_name,
            'slug' => $permission->slug,
            'guard_name' => $permission->guard_name,
            'module_name' => optional($permission->module)->name ?? '',
            'status_html' => $this->buildStatusToggleHtml($permission),
            'actions_html' => $this->buildActionsHtml($permission),
        ];
    }

    private function buildStatusToggleHtml(Permission $permission): string
    {
        if (! Gate::allows('toggle-permission-active')) {
            return $permission->is_active
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-secondary">Inactive</span>';
        }

        $checked = $permission->is_active ? 'checked' : '';

        return '<div class="text-center">
                    <div class="custom-control custom-switch d-inline-block">
                        <input type="checkbox"
                               class="custom-control-input js-toggle-permission-active"
                               id="permissionActiveSwitch' . $permission->id . '"
                               data-id="' . $permission->id . '"
                               ' . $checked . '>
                        <label class="custom-control-label" for="permissionActiveSwitch' . $permission->id . '"></label>
                    </div>
                </div>';
    }

    private function buildActionsHtml(Permission $permission): string
    {
        $html = '<div class="action-div">';

        if (Gate::allows('edit-permission')) {
            $html .= '<button type="button" class="btn btn-link p-0 action-icon edit-item"
                        data-url="' . route('masterapp.permissions.edit', ['permission' => $permission->id]) . '"
                        data-title="Edit permission"
                        title="Edit permission">
                        <i class="fa fa-edit"></i>
                    </button>';
        }

        if (Gate::allows('delete-permission')) {
            $html .= '<button type="button"
                        class="btn btn-link p-0 action-icon text-danger delete-item"
                        data-url="' . route('masterapp.permissions.destroy', ['permission' => $permission->id]) . '"
                        data-name="' . e($permission->name) . '"
                        title="Delete permission">
                        <i class="fa fa-trash"></i>
                    </button>';
        }

        $html .= '</div>';

        return $html;
    }
}

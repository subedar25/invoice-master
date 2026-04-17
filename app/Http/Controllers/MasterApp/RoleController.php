<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Department;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Core\Roles\Services\RolesService;
use App\Http\Requests\MasterApp\Roles\RolesStoreRequest;
use App\Http\Requests\MasterApp\Roles\RolesUpdateRequest;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')->pluck('name', 'id');
        return view('masterapp.roles.index', compact('departments'));
    }

    /**
     * Return data for the Roles DataTable.
     * This method handles the AJAX requests from the DataTable.
     */
    public function getRoles(Request $request)
    {
        if ($request->ajax()) {

            // $data = Role::with('permissions')->latest()->get(); 


            $query = Role::with(['permissions.module', 'department']);

            // --- DEPARTMENT FILTER ---
            if ($request->filled('department_id')) {
                $departmentId = $request->input('department_id');

                if (is_array($departmentId)) {
                    $query->whereIn('department_id', $departmentId);
                } else {
                    $query->where('department_id', $departmentId);
                }
            }

            // --- SEARCH FILTER ---
            if ($request->filled('search')) {
                $searchTerm = $request->get('search');

                $query->where('name', 'like', '%' . $searchTerm . '%');
            }


            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="row-check" value="' . $row->id . '">';
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->is_active ? 'checked' : '';

                    return '<div class="text-center">
                                <div class="custom-control custom-switch d-inline-block">
                                    <input type="checkbox"
                                           class="custom-control-input js-toggle-role-active"
                                           id="roleActiveSwitch' . $row->id . '"
                                           data-id="' . $row->id . '"
                                           ' . $checked . '>
                                    <label class="custom-control-label" for="roleActiveSwitch' . $row->id . '"></label>
                                </div>
                            </div>';
                })
                ->addColumn('permissions', function ($row) {
                    $activePermissions = $row->permissions->where('is_active', true);

                    if ($activePermissions->isEmpty()) {
                        return '<span class="text-muted font-italic">No Permissions</span>';
                    }

                    $grouped = $activePermissions->groupBy(function ($p) {
                        return optional($p->module)->name ?? 'Uncategorized';
                    });

                    $html = '<div class="role-perms-grouped">';
                    foreach ($grouped as $moduleName => $perms) {
                        $permChips = $perms->map(function ($p) {
                            $label = e($p->display_name ?? $p->name);
                            return '<span class="role-perm-chip" title="' . $label . '">' . $label . '</span>';
                        })->implode('');
                        $html .= '<div class="role-perms-module">';
                        $html .= '<button type="button" class="role-module-toggle d-flex align-items-center py-1 text-dark w-100" title="Click to show permissions" aria-expanded="false">';
                        $html .= '<i class="fa fa-chevron-right role-module-icon mr-1" aria-hidden="true"></i>';
                        $html .= '<span class="small font-weight-bold">' . e($moduleName) . '</span>';
                        $html .= '<span class="badge badge-pill badge-light border ml-1 role-perm-count">' . $perms->count() . '</span>';
                        $html .= '</button>';
                        $html .= '<div class="role-perms-list" style="display:none;"><div class="role-perms-chips">' . $permChips . '</div></div>';
                        $html .= '</div>';
                    }
                    $html .= '</div>';

                    return $html;
                })
               ->addColumn('department', function ($row) {
                return $row->department ? $row->department->name : '';
            })
                ->addColumn('actions', function ($row) {
                    $btn = '<div class="action-div">';

                  
                    if (Gate::allows('edit-role')) {
                        $btn .= '<button type="button" class="btn btn-link p-0 action-icon edit-item"
                                            data-url="' . route('masterapp.roles.edit', ['role' => $row->id]) . '"
                                            data-title="Edit ' . e($row->name) . '"
                                            title="Edit ' . e($row->name) . '">
                                            <i class="fa fa-edit"></i>
                                        </button>';
                    }

                  
                    // Delete button disabled on roles listing page

                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['checkbox', 'status', 'permissions', 'actions'])
                ->setRowAttr([
                    'data-id' => 'id'
                ])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    public function create()
    {
        $departments = Department::orderBy('name')->pluck('name', 'id');
        $groupedPermissions = Permission::with('module')
            ->where('is_active', true)
            ->get()
            ->groupBy(function ($permission) {
                return optional($permission->module)->name ?? 'Uncategorized';
            });

        return view('masterapp.roles.create', compact('groupedPermissions', 'departments'));
    }


    public function store(
        RolesStoreRequest $request,
        RolesService $service
    ) {
        $service->create($request->validated());

        return response()->json([
            'success' => 'Role created successfully!',
            'redirect' => route('masterapp.roles.index')
        ], 200);
    }


    public function edit(Role $role)
    {
        $departments = Department::orderBy('name')->pluck('name', 'id');

        // Fetch only active permissions for the edit form
        $activePermissions = Permission::with('module')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $groupedPermissions = $activePermissions->groupBy(function ($permission) {
            return optional($permission->module)->name ?? 'Uncategorized';
        });

        // Role's assigned permission IDs that are still active (only these are pre-checked in the form)
        $activePermissionIds = $activePermissions->pluck('id')->toArray();
        $rolePermissions = $role->permissions
            ->whereIn('id', $activePermissionIds)
            ->pluck('id')
            ->toArray();

        return view('masterapp.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions', 'departments'));
    }


    public function update(RolesUpdateRequest $request, int $id, RolesService $service)
    {

        $service->update($id, $request->validated());

        return response()->json([
            'message' => 'Roles updated successfully!',
            'redirect' => route('masterapp.roles.index')
        ], 200);
    }

    public function destroy(int $id, RolesService $service)
    {
        $service->delete($id);
        return response()->json(['message' => 'Role deleted successfully!'], 200);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->is_active = ! (bool) $role->is_active;
        $role->save();

        return response()->json([
            'message' => $role->is_active ? 'Role activated successfully.' : 'Role deactivated successfully.',
            'is_active' => (bool) $role->is_active,
        ]);
    }



    public function bulkDestroy(Request $request)
    {
        try {
            // Validate the incoming request data
            $request->validate([
                'ids' => 'required|array',
                // IMPORTANT: Change 'modules' to 'roles' to check against the correct table
                'ids.*' => 'integer|exists:roles,id'
            ]);

            $ids = $request->input('ids');

            // Delete each role via the model so audit log records each deletion
            $deletedCount = 0;
            foreach ($ids as $id) {
                Role::findOrFail((int) $id)->delete();
                $deletedCount++;
            }

            return response()->json([
                'message' => "{$deletedCount} role(s) deleted successfully!"
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Role Bulk Deletion Error: ' . $e->getMessage());

            // Return a generic error message to the user
            return response()->json(['message' => 'An error occurred while trying to delete the role(s).'], 500);
        }
    }
}

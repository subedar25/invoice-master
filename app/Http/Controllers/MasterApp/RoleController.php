<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Department;
use App\Models\RoleInvoiceDepartmentScope;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Core\Roles\Services\RolesService;
use App\Http\Requests\MasterApp\Roles\RolesStoreRequest;
use App\Http\Requests\MasterApp\Roles\RolesUpdateRequest;
use App\Support\CurrentOrganization;
use App\Support\InvoiceDepartmentAuthorization;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class RoleController extends Controller
{
    public function index()
    {
        $departments = $this->departmentsForCurrentOrganization();
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

            $orgId = CurrentOrganization::id();
            if ($orgId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('organization_id', $orgId);
            }

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
                    $viewer = auth()->user();
                    $activePermissions = $row->permissions->where('is_active', true);
                    if ($viewer instanceof User && ! $viewer->isSystemUser()) {
                        $activePermissions = $activePermissions->filter(
                            fn (Permission $p) => $p->isAssignableForViewer($viewer)
                        );
                    }

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
        $departments = $this->departmentsForCurrentOrganization();
        $groupedPermissions = Permission::with('module')
            ->where('is_active', true)
            ->assignableForViewer(auth()->user())
            ->orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                return optional($permission->module)->name ?? 'Uncategorized';
            });

        $allDepartmentsForInvoiceScope = $this->departmentsCollectionForCurrentOrganization();
        $invoiceDepartmentScopes = $this->defaultInvoiceDepartmentScopes();
        $invoicePermissionIds = InvoiceDepartmentAuthorization::invoicePermissionIdsByName();
        $listInvoicesPermissionId = $invoicePermissionIds['list-invoices'] ?? null;
        $approveInvoicePermissionId = $invoicePermissionIds['approve-invoice'] ?? null;

        return view('masterapp.roles.create', compact(
            'groupedPermissions',
            'departments',
            'allDepartmentsForInvoiceScope',
            'invoiceDepartmentScopes',
            'listInvoicesPermissionId',
            'approveInvoicePermissionId',
        ));
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
        $this->assertRoleInCurrentOrganization($role);

        $departments = $this->departmentsForCurrentOrganization();

        // Active permissions this viewer may assign (public/public for normal users; all for system users)
        $activePermissions = Permission::with('module')
            ->where('is_active', true)
            ->assignableForViewer(auth()->user())
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

        $allDepartmentsForInvoiceScope = $this->departmentsCollectionForCurrentOrganization();
        $defaults = $this->defaultInvoiceDepartmentScopes();
        $loaded = RoleInvoiceDepartmentScope::mapByPermissionNameForRole($role->id);
        $invoiceDepartmentScopes = array_replace_recursive($defaults, $loaded);
        $invoicePermissionIds = InvoiceDepartmentAuthorization::invoicePermissionIdsByName();
        $listInvoicesPermissionId = $invoicePermissionIds['list-invoices'] ?? null;
        $approveInvoicePermissionId = $invoicePermissionIds['approve-invoice'] ?? null;

        return view('masterapp.roles.edit', compact(
            'role',
            'groupedPermissions',
            'rolePermissions',
            'departments',
            'allDepartmentsForInvoiceScope',
            'invoiceDepartmentScopes',
            'listInvoicesPermissionId',
            'approveInvoicePermissionId',
        ));
    }


    public function update(RolesUpdateRequest $request, Role $role, RolesService $service)
    {
        $this->assertRoleInCurrentOrganization($role);

        $service->update($role->id, $request->validated());

        return response()->json([
            'message' => 'Roles updated successfully!',
            'redirect' => route('masterapp.roles.index')
        ], 200);
    }

    public function show(Role $role)
    {
        $this->assertRoleInCurrentOrganization($role);

        return redirect()->route('masterapp.roles.edit', $role);
    }

    public function destroy(Role $role, RolesService $service)
    {
        $this->assertRoleInCurrentOrganization($role);

        $service->delete($role->id);
        return response()->json(['message' => 'Role deleted successfully!'], 200);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $this->assertRoleInCurrentOrganization($role);
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
            $orgId = CurrentOrganization::id();
            if ($orgId === null) {
                return response()->json(['message' => 'Select an organization first.'], 403);
            }

            $deletedCount = Role::where('organization_id', $orgId)
                ->whereIn('id', array_map('intval', $ids))
                ->delete();

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

    protected function departmentsForCurrentOrganization(): Collection
    {
        $orgId = CurrentOrganization::id();
        if ($orgId === null) {
            return collect();
        }

        return Department::where('organization_id', $orgId)->orderBy('name')->pluck('name', 'id');
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Department>
     */
    protected function departmentsCollectionForCurrentOrganization(): Collection
    {
        $orgId = CurrentOrganization::id();
        if ($orgId === null) {
            return collect();
        }

        return Department::query()
            ->where('organization_id', $orgId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array<string, array{all_departments: bool, own_invoices: bool, reporting_only: bool, department_ids: array<int>}>
     */
    protected function defaultInvoiceDepartmentScopes(): array
    {
        return [
            'list-invoices' => [
                'all_departments' => true,
                'own_invoices' => false,
                'reporting_only' => false,
                'department_ids' => [],
            ],
            'approve-invoice' => [
                'all_departments' => true,
                'own_invoices' => false,
                'reporting_only' => false,
                'department_ids' => [],
            ],
        ];
    }

    protected function assertRoleInCurrentOrganization(Role $role): void
    {
        $orgId = CurrentOrganization::id();
        if ($orgId === null || (int) $role->organization_id !== $orgId) {
            abort(403);
        }
    }
}

<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
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
    public function index(RolesService $service)
    {
        $departments = $service->getDepartmentsForOrganization(CurrentOrganization::id());
        return view('masterapp.roles.index', compact('departments'));
    }

    /**
     * Return data for the Roles DataTable.
     * This method handles the AJAX requests from the DataTable.
     */
    public function getRoles(Request $request, RolesService $service)
    {
        if ($request->ajax()) {
            $orgId = CurrentOrganization::id();
            $query = $service->queryForDatatable(
                $orgId,
                $request->input('department_id'),
                $request->filled('search') ? (string) $request->get('search') : null
            );


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

    public function create(RolesService $service)
    {
        $departments = $service->getDepartmentsForOrganization(CurrentOrganization::id());
        $groupedPermissions = $service->getActiveAssignablePermissionsGrouped(auth()->user());
        $allDepartmentsForInvoiceScope = $service->getDepartmentRecordsForOrganization(CurrentOrganization::id());
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


    public function edit(Role $role, RolesService $service)
    {
        $this->assertRoleInCurrentOrganization($role);

        $departments = $service->getDepartmentsForOrganization(CurrentOrganization::id());

        // Active permissions this viewer may assign (public/public for normal users; all for system users)
        $activePermissions = $service->getActiveAssignablePermissions(auth()->user());
        $groupedPermissions = $activePermissions->groupBy(function ($permission) {
            return optional($permission->module)->name ?? 'Uncategorized';
        });

        // Role's assigned permission IDs that are still active (only these are pre-checked in the form)
        $activePermissionIds = $activePermissions->pluck('id')->toArray();
        $rolePermissions = $role->permissions
            ->whereIn('id', $activePermissionIds)
            ->pluck('id')
            ->toArray();

        $allDepartmentsForInvoiceScope = $service->getDepartmentRecordsForOrganization(CurrentOrganization::id());
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

    public function toggleActive(int $id, RolesService $service): JsonResponse
    {
        $role = $service->get($id);
        $this->assertRoleInCurrentOrganization($role);
        $role = $service->toggleActive($id);

        return response()->json([
            'message' => $role->is_active ? 'Role activated successfully.' : 'Role deactivated successfully.',
            'is_active' => (bool) $role->is_active,
        ]);
    }



    public function bulkDestroy(Request $request, RolesService $service)
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

            $deletedCount = $service->bulkDeleteForOrganization($orgId, $ids);

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
        return app(RolesService::class)->getDepartmentsForOrganization(CurrentOrganization::id());
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Department>
     */
    protected function departmentsCollectionForCurrentOrganization(): Collection
    {
        return app(RolesService::class)->getDepartmentRecordsForOrganization(CurrentOrganization::id());
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

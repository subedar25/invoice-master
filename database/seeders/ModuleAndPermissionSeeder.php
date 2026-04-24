<?php

// database/seeders/ModuleAndPermissionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Permission;
use Illuminate\Support\Facades\DB; // Optional, for a clean reset

class ModuleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Define all your modules and permissions in one place
        $modulesAndPermissions = [
            'User Management' => [
                'permissions' => [
                    ['name' => 'create-user', 'display_name' => 'Create User','slug' => 'create-user'],
                    ['name' => 'edit-user', 'display_name' => 'Edit User','slug' => 'edit-user'],
                    // as discuss with client, we don't need to delete user
                    // ['name' => 'delete-user', 'display_name' => 'Delete User','slug' => 'delete-user'],
                    ['name' => 'edit-email', 'display_name' => 'Edit Email','slug' => 'edit-email'],
                    ['name' => 'list-users', 'display_name' => 'View Users','slug' => 'list-users'],
                    ['name' => 'list-driver', 'display_name' => 'View Driver','slug' => 'list-driver'],
                   // ['name' => 'list-wordpress-user', 'display_name' => 'View WordPress User', 'slug' => 'list-wordpress-user'],
                    //create active user,drivers, wordpress permissions
                    ['name' => 'active-deactive', 'display_name' => 'Active-Deactive User','slug' => 'active-deactive'],
                ],
                'slug' => 'user-management'
            ],
             'Roles Management' => [
                'permissions' => [
                    ['name' => 'create-role', 'display_name' => 'Create Role','slug' => 'create-role'],
                    ['name' => 'edit-role', 'display_name' => 'Edit Role','slug' => 'edit-role'],
                    ['name' => 'delete-role', 'display_name' => 'Delete Role','slug' => 'delete-role'],
                    ['name' => 'list-role', 'display_name' => 'View Roles','slug' => 'list-role'],
                ],
                'slug' => 'roles-management'
            ],

            'Permission Management' => [
                'type' => 'system',
                'permissions' => [
                    ['name' => 'create-permission', 'display_name' => 'Create Permission','slug' => 'create-permission'],
                    ['name' => 'edit-permission', 'display_name' => 'Edit Permission','slug' => 'edit-permission'],
                    ['name' => 'delete-permission', 'display_name' => 'Delete Permission','slug' => 'delete-permission'],
                    ['name' => 'list-permission', 'display_name' => 'View Permission','slug' => 'list-permission'],
                    ['name' => 'activate-deactivate-permission', 'display_name' => 'Activate/Deactivate Permission','slug' => 'activate-deactivate-permission'],
                ],
                'slug' => 'permission-management'
            ],

            'Modules Management' => [
                'type' => 'system',
                'permissions' => [
                    ['name' => 'create-modules', 'display_name' => 'Create Modules','slug' => 'create-modules'],
                    ['name' => 'edit-modules', 'display_name' => 'Edit Modules','slug' => 'edit-modules'],
                    ['name' => 'delete-modules', 'display_name' => 'Delete Modules','slug' => 'delete-modules'],
                    ['name' => 'list-modules', 'display_name' => 'View Modules','slug' => 'list-modules'],
                ],
                'slug' => 'modules-management'
            ],
            // 'Client Management' => [
            //     'permissions' => [
            //         ['name' => 'create-client', 'display_name' => 'Create Client','slug' => 'create-client'],
            //         ['name' => 'edit-client', 'display_name' => 'Edit Client','slug' => 'edit-client'],
            //         ['name' => 'delete-client', 'display_name' => 'Delete Client','slug' => 'delete-client'],
            //         ['name' => 'list-client', 'display_name' => 'View Client','slug' => 'list-client'],
            //     ],
            //     'slug' => 'client-management'
            // ],

            // Hide driver and wordpress user management after discussing with client
            // 'Driver Management' => [
            //     'permissions' => [
            //         ['name' => 'create-driver', 'display_name' => 'Create driver','slug' => 'create-driver'],
            //         ['name' => 'edit-driver', 'display_name' => 'Edit driver','slug' => 'edit-driver'],
            //         ['name' => 'delete-driver', 'display_name' => 'Delete driver','slug' => 'delete-driver'],
            //         ['name' => 'list-driver', 'display_name' => 'View driver','slug' => 'list-driver'],
            //     ],
            //     'slug' => 'driver-management'
            // ],
            //  'Wordpress User Management' => [
            //     'permissions' => [
            //         ['name' => 'create-wordpress-user', 'display_name' => 'Create Wordpress User','slug' => 'create-wordpress-user'],
            //         ['name' => 'edit-wordpress-user', 'display_name' => 'Edit Wordpress User','slug' => 'edit-wordpress-user'],
            //         ['name' => 'delete-wordpress-user', 'display_name' => 'Delete Wordpress User','slug' => 'delete-wordpress-user'],
            //         ['name' => 'list-wordpress-user', 'display_name' => 'View Wordpress User','slug' => 'list-wordpress-user'],
            //     ],
            //     'slug' => 'wordpress-user--management'
            // ],

            // 'Vehicle Management' => [
            //     'permissions' => [
            //         ['name' => 'create-vehicle', 'display_name' => 'Create vehicle','slug' => 'create-vehicle'],
            //         ['name' => 'edit-vehicle', 'display_name' => 'Edit vehicle','slug' => 'edit-vehicle'],
            //         ['name' => 'delete-vehicle', 'display_name' => 'Delete vehicle','slug' => 'delete-vehicle'],
            //         ['name' => 'list-vehicle', 'display_name' => 'View vehicle','slug' => 'list-vehicle'],
            //     ],
            //     'slug' => 'vehicle-management'
            // ],

            //  'Timesheet Management' => [
            //     'permissions' => [
            //         ['name' => 'create-timesheet', 'display_name' => 'Create Timesheet','slug' => 'create-timesheet'],
            //         ['name' => 'edit-timesheet', 'display_name' => 'Edit Timesheet','slug' => 'edit-timesheet'],
            //         ['name' => 'delete-timesheet', 'display_name' => 'Delete Timesheet','slug' => 'delete-timesheet'],
            //         ['name' => 'list-timesheets', 'display_name' => 'View Timesheet','slug' => 'list-timesheets'],
            //     ],
            //     'slug' => 'timesheet-management'

            // ],
            // 'Time Off Request Management' => [
            //     'permissions' => [
            //         ['name' => 'create-time-off-request', 'display_name' => 'Create Time Off Request', 'slug' => 'create-time-off-request'],
            //         ['name' => 'edit-time-off-request', 'display_name' => 'Edit Time Off Request', 'slug' => 'edit-time-off-request'],
            //         ['name' => 'delete-time-off-request', 'display_name' => 'Delete Time Off Request', 'slug' => 'delete-time-off-request'],
            //         ['name' => 'list-time-off-requests', 'display_name' => 'List Time Off Requests', 'slug' => 'list-time-off-requests'],
            //         ['name' => 'status-time-off-request', 'display_name' => 'Change Status Time Off Request', 'slug' => 'status-time-off-request'],
            //         ['name' => 'admin-time-off-requests', 'display_name' => 'Admin Time Off Request', 'slug' => 'admin-time-off-requests'],
            //     ],
            //     'slug' => 'time-off-request-management'
            // ],
            // 'Droppoint Management' => [
            //     'permissions' => [
            //         ['name' => 'create-dropoint', 'display_name' => 'Create dropoint','slug' => 'create-dropoint'],
            //         ['name' => 'edit-dropoint', 'display_name' => 'Edit dropoint','slug' => 'edit-dropoint'],
            //         ['name' => 'delete-dropoint', 'display_name' => 'Delete dropoint','slug' => 'delete-dropoint'],
            //         ['name' => 'list-dropoint', 'display_name' => 'View dropoint','slug' => 'list-dropoint'],
            //     ],
            //     'slug' => 'dropoint-management'
            // ],

            // Location Management Module and Permissions
            // 'Location Management' => [
            //     'permissions' => [
            //         ['name' => 'create-location', 'display_name' => 'Create Location', 'slug' => 'create-location'],
            //         ['name' => 'edit-location', 'display_name' => 'Edit Location', 'slug' => 'edit-location'],
            //         ['name' => 'delete-location', 'display_name' => 'Delete Location', 'slug' => 'delete-location'],
            //         ['name' => 'list-locations', 'display_name' => 'View Locations', 'slug' => 'list-locations'],
            //     ],
            //     'slug' => 'location-management'
            // ],

            'Invoice Management' => [
                'permissions' => [
                    ['name' => 'create-invoice', 'display_name' => 'Create Invoice', 'slug' => 'create-invoice'],
                    ['name' => 'edit-invoice', 'display_name' => 'Edit Invoice', 'slug' => 'edit-invoice'],
                    ['name' => 'after-approval-change-edit-invoice', 'display_name' => 'After Approval Change Edit Invoice', 'slug' => 'after-approval-change-edit-invoice'],
                    ['name' => 'delete-invoice', 'display_name' => 'Delete Invoice', 'slug' => 'delete-invoice'],
                    ['name' => 'list-invoices', 'display_name' => 'View Invoices', 'slug' => 'list-invoices'],
                    ['name' => 'approve-invoice', 'display_name' => 'Approve Invoice', 'slug' => 'approve-invoice'],
                    ['name' => 'make-payment', 'display_name' => 'Make Payment', 'slug' => 'make-payment'],
                    ['name' => 'view-payment-history', 'display_name' => 'View Payment History', 'slug' => 'view-payment-history'],
                    ['name' => 'change-payment-status', 'display_name' => 'Change Payment Status', 'slug' => 'change-payment-status'],
                ],
                'slug' => 'invoice-management'
            ],
            'Dashboard' => [
                'permissions' => [
                    ['name' => 'dashboard-operational-manager', 'display_name' => 'Dashboard Operational Manager', 'slug' => 'dashboard-operational-manager'],
                    ['name' => 'dashboard-general-manager', 'display_name' => 'Dashboard General Manager', 'slug' => 'dashboard-general-manager'],
                    ['name' => 'dashboard-area-manager', 'display_name' => 'Dashboard Area Manager', 'slug' => 'dashboard-area-manager'],
                    ['name' => 'dashboard-accountant', 'display_name' => 'Dashboard Accountant', 'slug' => 'dashboard-accountant'],
                    ['name' => 'dashboard-outlet', 'display_name' => 'Dashboard Outlet', 'slug' => 'dashboard-outlet'],
                    ['name' => 'dashboard-vendors', 'display_name' => 'Dashboard Vendors', 'slug' => 'dashboard-vendors'],
                    ['name' => 'dashboard-total-invoice', 'display_name' => 'Dashboard Total Invoice', 'slug' => 'dashboard-total-invoice'],
                    ['name' => 'dashboard-completed-invoice', 'display_name' => 'Dashboard Completed Invoice', 'slug' => 'dashboard-completed-invoice'],
                    ['name' => 'dashboard-pending-invoice', 'display_name' => 'Dashboard Pending Invoice', 'slug' => 'dashboard-pending-invoice'],
                    ['name' => 'dashboard-in-process-invoice', 'display_name' => 'Dashboard In Process Invoice', 'slug' => 'dashboard-in-process-invoice'],
                    ['name' => 'dashboard-approved-invoice', 'display_name' => 'Dashboard Approved Invoice', 'slug' => 'dashboard-approved-invoice'],
                ],
                'slug' => 'dashboard'
            ],

            // 'Contact Management' => [
            //     'permissions' => [
            //         ['name' => 'create-contact', 'display_name' => 'Create contact','slug' => 'create-contact'],
            //         ['name' => 'edit-contact', 'display_name' => 'Edit contact','slug' => 'edit-contact'],
            //         ['name' => 'delete-contact', 'display_name' => 'Delete contact','slug' => 'delete-contact'],
            //         ['name' => 'list-contact', 'display_name' => 'View contact','slug' => 'list-contact'],
            //         ['name' => 'list-contact-item', 'display_name' => 'View contact Items','slug' => 'list-contact-item'],
            //         ['name' => 'create-contact-item', 'display_name' => 'Create contact item','slug' => 'create-contact-item'],
            //         ['name' => 'edit-contact-item', 'display_name' => 'Edit contact item','slug' => 'edit-contact-item'],
            //         ['name' => 'delete-contact-item', 'display_name' => 'Delete contact item','slug' => 'delete-contact-item'],
            //     ],
            //     'slug' => 'contact-management'
            // ],
            'Organization Management' => [
                'type' => 'system',
                'permissions' => [
                    ['name' => 'create-organization', 'display_name' => 'Create organization','slug' => 'create-organization'],
                    ['name' => 'edit-organization', 'display_name' => 'Edit organization','slug' => 'edit-organization'],
                    ['name' => 'delete-organization', 'display_name' => 'Delete organization','slug' => 'delete-organization'],
                    ['name' => 'list-organization', 'display_name' => 'View organization','slug' => 'list-organization'],
                    ['name' => 'activate-deactivate-organization', 'display_name' => 'Activate/Deactivate organization','slug' => 'activate-deactivate-organization'],
                ],
                'slug' => 'organization-management'
            ],
            // 'Price Structure Type Management' => [
            //     'permissions' => [
            //         ['name' => 'create-price-structure-type', 'display_name' => 'Create Price Structure Type', 'slug' => 'create-price-structure-type'],
            //         ['name' => 'edit-price-structure-type', 'display_name' => 'Edit Price Structure Type', 'slug' => 'edit-price-structure-type'],
            //         ['name' => 'delete-price-structure-type', 'display_name' => 'Delete Price Structure Type', 'slug' => 'delete-price-structure-type'],
            //         ['name' => 'list-price-structure-type', 'display_name' => 'View Price Structure Type', 'slug' => 'list-price-structure-type'],
            //     ],
            //     'slug' => 'price-structure-type-management'
            // ],
            // 'Issue Orientations' => [
            //     'permissions' => [
            //         ['name' => 'add-issue-orientation', 'display_name' => 'Add Orientation', 'slug' => 'add-issue-orientation'],
            //         ['name' => 'toggle-issue-orientation', 'display_name' => 'Active/Inactive Toggle (Orientations)', 'slug' => 'toggle-issue-orientation'],
            //         ['name' => 'edit-issue-orientation', 'display_name' => 'Edit Orientation', 'slug' => 'edit-issue-orientation'],
            //         ['name' => 'delete-issue-orientation', 'display_name' => 'Delete Orientation', 'slug' => 'delete-issue-orientation'],
            //     ],
            //     'slug' => 'issue-orientations'
            // ],
            // 'Issue Sections' => [
            //     'permissions' => [
            //         ['name' => 'add-issue-section', 'display_name' => 'Add Section', 'slug' => 'add-issue-section'],
            //         ['name' => 'toggle-issue-section', 'display_name' => 'Active/Inactive Toggle (Sections)', 'slug' => 'toggle-issue-section'],
            //         ['name' => 'edit-issue-section', 'display_name' => 'Edit Section', 'slug' => 'edit-issue-section'],
            //         ['name' => 'delete-issue-section', 'display_name' => 'Delete Section', 'slug' => 'delete-issue-section'],
            //     ],
            //     'slug' => 'issue-sections'
            // ],
            'Audit Log' => [
                'permissions' => [
                    ['name' => 'list-auditlog', 'display_name' => 'View Audit Log','slug' => 'list-auditlog'],
                ],
                'slug' => 'auditlog-management'
            ],
            // 'Two-Factor Authentication' => [
            //     'permissions' => [
            //         ['name' => 'enable-two-factor', 'display_name' => 'Enable Two-Factor Authentication','slug' => 'enable-two-factor']
            //     ],
            //     'slug' => 'two-factor-authentication'
            // ],
            //Master permissions
            'Master Management' => [
                'permissions' => [
                    ['name' => 'list-master', 'display_name' => 'View Masters','slug' => 'list-master'],
                    ['name' => 'department', 'display_name' => 'Department', 'slug' => 'department'],
                    ['name' => 'locations', 'display_name' => 'Locations', 'slug' => 'locations'],
                    ['name' => 'vendors', 'display_name' => 'Vendors', 'slug' => 'vendors'],
                    ['name' => 'outlets', 'display_name' => 'Outlets', 'slug' => 'outlets'],
                    ['name' => 'country', 'display_name' => 'Country','slug' => 'country'],
                    ['name' => 'state', 'display_name' => 'State','slug' => 'state'],
                    ['name' => 'products', 'display_name' => 'Products','slug' => 'products'],
                    ['name' => 'taxes', 'display_name' => 'Taxes','slug' => 'taxes'],
                    ['name' => 'designation', 'display_name' => 'Designation', 'slug' => 'designation'],
                    
                ],
                'slug' => 'master-management'
            ]
            ];
          


        $guardName = 'web';

      

        foreach ($modulesAndPermissions as $moduleName => $data) {
            $moduleType = $data['type'] ?? 'public';

            // Use updateOrCreate to avoid creating duplicates and keep names in sync.
            $module = Module::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $moduleName,
                    'type' => $moduleType,
                ]
            );

            foreach ($data['permissions'] as $permissionData) {
                $permissionType = $permissionData['type'] ?? $moduleType;

                // Use updateOrCreate for permissions as well to prevent duplicates.
                Permission::updateOrCreate(
                    // Find by the unique name
                    ['name' => $permissionData['name'], 'guard_name' => $guardName],
                    // Create with these details if not found
                    [
                        'display_name' => $permissionData['display_name'],
                        'module_id' => $module->id,
                        'guard_name' => $guardName,
                        'slug' => $permissionData['slug'],
                        'is_active' => true,
                        'type' => $permissionType,
                    ]
                );
            }
        }

    }
}

<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use OwenIt\Auditing\Models\Audit;
use OwenIt\Auditing\Policies\AuditPolicy;
use App\Models\User;
use App\Models\Module;
use App\Policies\ModulePolicy;
use App\Models\Timesheet;
use App\Policies\TimesheetPolicy;
use App\Policies\NotificationPolicy;
use App\Models\Notification;
use Spatie\Permission\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Audit::class => AuditPolicy::class,
         Module::class => ModulePolicy::class,
         Timesheet::class => TimesheetPolicy::class,
         Notification::class => NotificationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('view-audits', function (User $user) {
            if (($user->is_admin ?? false) == 1) {
                return true;
            }

            if ($user->hasAnyRole(['Admin', 'System Admin'])) {
                return true;
            }

            if (Permission::where('name', 'view audits')->exists()) {
                return $user->hasPermissionTo('view audits');
            }

            return false;
        });

        // Allow driver/wordpress permissions to access user create/edit screens
        Gate::define('create-user', function (User $user) {
            return $user->hasAnyPermission([
                'create-user',
                'create-driver',
                'create-wordpress-user',
            ]);
        });

        Gate::define('edit-user', function (User $user) {
            return $user->hasAnyPermission([
                'edit-user',
                'edit-driver',
                'edit-wordpress-user',
            ]);
        });

        // Permission active toggle: allow if user has dedicated permission or edit (backward compat)
        Gate::define('toggle-permission-active', function (User $user) {
            return $user->can('activate-deactivate-permission') || $user->can('edit-permission');
        });

        // Master Management: show sidebar when user can view Masters; show sub-items by permission
        Gate::define('list-master', function (User $user) {
            return $user->hasPermissionTo('list-master');
        });
        Gate::define('organization_type', function (User $user) {
            return $user->hasPermissionTo('organization_type');
        });
        Gate::define('seasons', function (User $user) {
            return $user->hasPermissionTo('seasons');
        });
        // Notification Gates
        Gate::define('view-notifications', [NotificationPolicy::class, 'view']);
        Gate::define('mark-notification-read', [NotificationPolicy::class, 'markAsRead']);
        Gate::define('mark-all-notifications-read', [NotificationPolicy::class, 'markAllAsRead']);

    }
}

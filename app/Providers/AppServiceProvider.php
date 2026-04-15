<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Permission;
use App\Core\User\Contracts\UserRepository;
use App\Infrastructure\Persistence\User\EloquentUserRepository;
use App\Core\Modules\Contracts\ModulesRepository;
use App\Infrastructure\Persistence\Modules\EloquentModulesRepository;
use App\Core\Permissions\Contracts\PermissionsRepository;
use App\Infrastructure\Persistence\Permissions\EloquentPermissionsRepository;
use App\Core\Roles\Contracts\RolesRepository;
use App\Infrastructure\Persistence\Roles\EloquentRolesRepository;
use Illuminate\Support\Facades\View;
use Laravel\Telescope\TelescopeServiceProvider;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\PermissionRegistrar;
use App\Services\GoogleDriveService;
use App\Core\Notification\Contracts\NotificationRepository;
use App\Infrastructure\Persistence\Notification\EloquentNotificationRepository;
use App\Core\Timesheet\Contracts\TimesheetRepository;
use App\Infrastructure\Persistence\Timesheet\EloquentTimesheetRepository;
use App\Core\Location\Contracts\LocationRepository;
use App\Infrastructure\Persistence\Location\EloquentLocationRepository;
use App\Helpers\AppNotification;
use Illuminate\Pagination\Paginator;
use App\Helpers\UniversalNotification;
use App\Core\TimeOff\Contracts\TimeOffRequestRepository;
use App\Infrastructure\Persistence\User\EloquentTimeOffRequestRepository;
use App\Core\User\Contracts\SettingRepository;
use App\Infrastructure\Persistence\User\EloquentSettingRepository;
use App\Core\TwoFactor\Contracts\TwoFactorRepository;
use App\Infrastructure\Persistence\TwoFactor\EloquentTwoFactorRepository;
use App\Core\Organization\Contracts\OrganizationRepository;
use App\Infrastructure\Persistence\Organization\EloquentOrganizationRepository;
use App\Core\OrganizationType\Contracts\OrganizationTypeRepository;
use App\Infrastructure\Persistence\OrganizationType\EloquentOrganizationTypeRepository;
use App\Core\Season\Contracts\SeasonRepository;
use App\Infrastructure\Persistence\Season\EloquentSeasonRepository;
use App\Http\Livewire\MasterApp\Masters\OrganizationType as OrganizationTypeComponent;
use App\Http\Livewire\MasterApp\Masters\Seasons as SeasonsComponent;
use App\Http\Livewire\MasterApp\Masters\Department as DepartmentComponent;
use App\Http\Livewire\MasterApp\Masters\Country as CountryComponent;
use App\Http\Livewire\MasterApp\Masters\Organization as OrganizationComponent;
use App\Http\Livewire\MasterApp\Masters\State as StateComponent;
use App\Http\Livewire\MasterApp\Masters\Location as LocationComponent;
use App\Http\Livewire\MasterApp\Masters\Outlet as OutletComponent;
use App\Http\Livewire\MasterApp\Masters\Product as ProductComponent;
use App\Http\Livewire\MasterApp\Masters\Tax as TaxComponent;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        if ($this->app->environment('local')) {
            $this->app->register(TelescopeServiceProvider::class);
        }

        $bindings = [
            \Spatie\Permission\Contracts\Permission::class => \Spatie\Permission\Models\Permission::class,
            UserRepository::class => EloquentUserRepository::class,
            TimeOffRequestRepository::class => EloquentTimeOffRequestRepository::class,
            ModulesRepository::class => EloquentModulesRepository::class,
            PermissionsRepository::class => EloquentPermissionsRepository::class,
            RolesRepository::class => EloquentRolesRepository::class,
            NotificationRepository::class => EloquentNotificationRepository::class,
            TimesheetRepository::class => EloquentTimesheetRepository::class,
            SettingRepository::class => EloquentSettingRepository::class,
            TimeOffRequestRepository::class => EloquentTimeOffRequestRepository::class,
            TwoFactorRepository::class => EloquentTwoFactorRepository::class,
            OrganizationTypeRepository::class => EloquentOrganizationTypeRepository::class,
            SeasonRepository::class => EloquentSeasonRepository::class,
            OrganizationRepository::class => EloquentOrganizationRepository::class,
            LocationRepository::class => EloquentLocationRepository::class,
        ];

        foreach ($bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }


        //universal notification binding
        $this->app->bind( UniversalNotification::class);

    //universal notification binding
    $this->app->bind(  AppNotification::class  );

        $this->app->singleton(GoogleDriveService::class, function () {
            return new GoogleDriveService();
        });
    }


    public function boot(): void
    {
        Livewire::component('master-app.masters.organization-type', OrganizationTypeComponent::class);
        Livewire::component('master-app.masters.seasons', SeasonsComponent::class);
        Livewire::component('master-app.masters.department', DepartmentComponent::class);
        Livewire::component('master-app.masters.organization', OrganizationComponent::class);
        Livewire::component('master-app.masters.country', CountryComponent::class);
        Livewire::component('master-app.masters.state', StateComponent::class);
        Livewire::component('master-app.masters.location', LocationComponent::class);
        Livewire::component('master-app.masters.outlet', OutletComponent::class);
        Livewire::component('master-app.masters.product', ProductComponent::class);
        Livewire::component('master-app.masters.tax', TaxComponent::class);

        Paginator::useBootstrap();
        View::composer('partials.notification', function ($view) {
            if (!auth()->check()) {
                return;
            }

            try {
                $user = auth()->user();

                $view->with([
                    'unreadCount' => $user->unreadNotifications->count(),
                    'recentNotifications' => $user->notifications()
                        ->latest()
                        ->take(10)
                        ->get(),
                ]);
            } catch (\Exception $e) {
                // If database is not available, don't load notifications
                $view->with([
                    'unreadCount' => 0,
                    'recentNotifications' => collect(),
                ]);
            }
        });
    }
}

<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterApp\RoleController;
use App\Http\Controllers\MasterApp\UserController;
use App\Http\Controllers\MasterApp\EntityInfoController;
use App\Http\Controllers\MasterApp\ModuleController;
use App\Http\Controllers\MasterApp\PermissionController;
use App\Http\Controllers\MasterApp\TimeOffRequestController;
use App\Http\Controllers\MasterApp\DashboardController;
use App\Http\Controllers\MasterApp\TimesheetController;
use App\Http\Controllers\MasterApp\TimesheetClockController;
use App\Http\Controllers\MasterApp\UserTimesheetController;
use App\Http\Controllers\MasterApp\NotificationController;
use App\Http\Controllers\MasterApp\SettingsController;
use App\Http\Controllers\MasterApp\LocationController;
use App\Http\Controllers\MasterApp\OrganizationController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login.view');
});
use App\Http\Controllers\AuditController;
use App\Http\Middleware\EnsureCanViewAudits;

// Protected Routes
Route::middleware('auth')->group(function () {

Route::prefix('master-app')
    // ->middleware(['auth'])
    ->name('masterapp.')
    ->group(function ()
    {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::get('/changepassword', [ProfileController::class, 'changepassword'])->name('profile.changepassword');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/changepassword', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index')
            ->middleware('can:list-users');
        Route::get('/users/create', [UserController::class, 'create'])
            ->name('users.create')
            ->middleware('can:create-user');

        Route::post('/users/store', [UserController::class, 'store'])
            ->name('users.store')
            ->middleware('can:create-user');

        Route::get('/users/{id}/edit', [UserController::class, 'edit'])
            ->name('users.edit')
            ->middleware('can:edit-user');

        Route::put('/users/{id}', [UserController::class, 'update'])
            ->name('users.update')
            ->middleware('can:edit-user');

        Route::delete('/users/{id}', [UserController::class, 'destroy'])
            ->name('users.destroy')
            ->middleware('can:delete-user');

         //  ajax toggle without page reload
        Route::patch('/users/{id}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active')
            ->middleware('can:active-deactive');
        Route::patch('/users/{id}/status', [UserController::class, 'updateStatus'])
            ->name('users.updateStatus')
            ->middleware('can:active-deactive');
        Route::post('/users/check-email', [UserController::class, 'checkEmail'])->name('users.check-email');
        Route::get('/entity/{type}/{id}', [EntityInfoController::class, 'show'])->name('entity.info');
        Route::get('/entity/{type}/{id}/tab/{tab}', [EntityInfoController::class, 'showTab'])->name('entity.info.tab');
        Route::get('/entity/{type}/{id}/modal/{modal}', [EntityInfoController::class, 'showModal'])->name('entity.info.modal');
        // Route::resource('users', UserController::class);

        Route::patch('users/{id}/password', [UserController::class, 'updatePassword'])->name('users.password.update');

           Route::resource('users', UserController::class)->only(['show'])
           ->names([
               'show' => 'users.show',
           ])
           ->middleware('can:list-users');

       // Additional custom routes for users

        Route::prefix('users')->name('users.')->group(function () {
           Route::get('/create', [UserController::class, 'create'])->name('create')->middleware('can:create-user');
           Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit')->middleware('can:edit-user');
           Route::patch('/{id}/toggle-active', [UserController::class, 'toggleActive'])
               ->name('toggle-active')
               ->middleware('can:active-deactive');
           Route::patch('/{id}/status', [UserController::class, 'updateStatus'])
               ->name('updateStatus')
               ->middleware('can:active-deactive');
           Route::patch('/{id}/password', [UserController::class, 'updatePassword'])->name('password.update')->middleware('can:edit-user');
        });

        // TIME OFF REQUESTS

        Route::prefix('time-off-requests')->name('time-off-requests.')->group(function () {
            Route::get('/data', [TimeOffRequestController::class, 'data'])->name('data');
            Route::get('/', [TimeOffRequestController::class, 'index'])->name('index');
            Route::post('/store', [TimeOffRequestController::class, 'store'])->name('store');
            Route::put('/{id}', [TimeOffRequestController::class, 'update'])->name('update');
            Route::patch('/{id}/status', [TimeOffRequestController::class, 'updateStatus'])->name('updateStatus');
            Route::post('/bulk-status', [TimeOffRequestController::class, 'bulkUpdateStatus'])->name('bulkUpdateStatus');
            Route::delete('/{id}', [TimeOffRequestController::class, 'destroy'])->name('destroy');
            Route::get('/export', [TimeOffRequestController::class, 'export'])->name('export');

        });

        /* AUDIT LOGS */
        Route::middleware([EnsureCanViewAudits::class])->group(function () {
            Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
            Route::get('/audit/export', [AuditController::class, 'export'])->name('audit.export');
            Route::get('/audit/history', [AuditController::class, 'modelHistory'])->name('audit.history');
        });

        /* ORGANIZATIONS (Client model-backed) */
        Route::prefix('organizations')->name('organizations.')->group(function () {
            Route::get('/', [OrganizationController::class, 'index'])->name('index')->middleware('can:list-organization');
            Route::get('/data', [OrganizationController::class, 'data'])->name('data')->middleware('can:list-organization');
            Route::get('/locations/suggest', [OrganizationController::class, 'suggestLocations'])->name('locations.suggest')->middleware('can:list-organization');
            Route::get('/contacts/phones/suggest', [OrganizationController::class, 'suggestContactPhones'])->name('contacts.phones.suggest')->middleware('can:list-organization');
            Route::get('/contacts/suggest', [OrganizationController::class, 'suggestContacts'])->name('contacts.suggest')->middleware('can:list-organization');
            Route::get('/create', [OrganizationController::class, 'create'])->name('create')->middleware('can:create-organization');
            Route::post('/', [OrganizationController::class, 'store'])->name('store')->middleware('can:create-organization');
            Route::get('/{id}', [OrganizationController::class, 'show'])->name('show')->middleware('can:list-organization');
            Route::get('/{id}/edit', [OrganizationController::class, 'edit'])->name('edit')->middleware('can:edit-organization');
            Route::put('/{id}', [OrganizationController::class, 'update'])->name('update')->middleware('can:edit-organization');
            Route::patch('/{id}/active', [OrganizationController::class, 'toggleActive'])->name('toggle-active')->middleware('can:toggle-organization-active');
            Route::delete('/{id}', [OrganizationController::class, 'destroy'])->name('destroy')->middleware('can:delete-organization');
        });



         /*  NOTIFICATIONS  */
        Route::get('notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::match(['get', 'patch'], 'notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');

        Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])
            ->name('notifications.read-all');

        /*  MODULES   */
        Route::get('modules', [ModuleController::class, 'index'])
            ->name('modules.index')
            ->middleware('can:list-modules');
        Route::get('modules/create', [ModuleController::class, 'create'])
            ->name('modules.create')
            ->middleware('can:create-modules');
        Route::post('modules', [ModuleController::class, 'store'])
            ->name('modules.store')
            ->middleware('can:create-modules');
        Route::get('modules/{module}/edit', [ModuleController::class, 'edit'])
            ->name('modules.edit')
            ->middleware('can:edit-modules');
        Route::put('modules/{module}', [ModuleController::class, 'update'])
            ->name('modules.update')
            ->middleware('can:edit-modules');
        Route::delete('modules/{module}', [ModuleController::class, 'destroy'])
            ->name('modules.destroy')
            ->middleware('can:delete-modules');
        Route::get('modules/{module}', [ModuleController::class, 'show'])
            ->name('modules.show')
            ->middleware('can:list-modules');



         /*  PERMISSIONS  */
        Route::get('/permissions', [PermissionController::class, 'index'])
            ->name('permissions.index')
            ->middleware('can:list-permission');
        Route::get('/permissions/create', [PermissionController::class, 'create'])
            ->name('permissions.create')
            ->middleware('can:create-permission');
        Route::post('/permissions', [PermissionController::class, 'store'])
            ->name('permissions.store')
            ->middleware('can:create-permission');
        Route::get('/permissions/{permission}', [PermissionController::class, 'show'])
            ->name('permissions.show')
            ->middleware('can:list-permission');
        Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])
            ->name('permissions.edit')
            ->middleware('can:edit-permission');
        Route::put('/permissions/{permission}', [PermissionController::class, 'update'])
            ->name('permissions.update')
            ->middleware('can:edit-permission');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
            ->name('permissions.destroy')
            ->middleware('can:delete-permission');
        Route::patch('/permissions/{id}/toggle-active', [PermissionController::class, 'toggleActive'])
            ->name('permissions.toggle-active')
            ->middleware('can:toggle-permission-active');

         /*   ROLES   */
        Route::get('/roles/data', [RoleController::class, 'getRoles'])
            ->name('roles.data')
            ->middleware('can:list-role');
        Route::patch('/roles/{id}/toggle-active', [RoleController::class, 'toggleActive'])
            ->name('roles.toggle-active')
            ->middleware('can:edit-role');
        Route::get('/roles', [RoleController::class, 'index'])
            ->name('roles.index')
            ->middleware('can:list-role');
        Route::get('/roles/create', [RoleController::class, 'create'])
            ->name('roles.create')
            ->middleware('can:create-role');
        Route::post('/roles', [RoleController::class, 'store'])
            ->name('roles.store')
            ->middleware('can:create-role');
        Route::get('/roles/{role}', [RoleController::class, 'show'])
            ->name('roles.show')
            ->middleware('can:list-role');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
            ->name('roles.edit')
            ->middleware('can:edit-role');
        Route::put('/roles/{role}', [RoleController::class, 'update'])
            ->name('roles.update')
            ->middleware('can:edit-role');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->name('roles.destroy')
            ->middleware('can:delete-role');
        // Route::resource('modules', ModuleController::class);



    Route::get('test-email', [App\Http\Controllers\MasterApp\TestemailController::class, 'index'])->name('testemail.index');


        // SETTINGS PAGE
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

        // MASTERS (Master data: Organization Type, Seasons, Advertisers)
        Route::get('/masters', function () {
            return view('masterapp.masters');
        })->name('masters')->middleware('can:list-master');


        // TIMESHEETS
        Route::prefix('timesheets')->name('timesheets.')->group(function () {

            // DataTables AJAX
            Route::get('/data', [TimesheetController::class, 'getTimesheets'])
                ->name('data')->middleware('can:list-timesheets');

            // Timesheet Standard resource routes
            Route::get('/', [TimesheetController::class,'index'])->name('index')->middleware('can:list-timesheets');
            Route::post('/', [TimesheetController::class,'store'])->name('store')->middleware('can:create-timesheet');
            Route::get('/{timesheet}', [TimesheetController::class,'show'])->name('show')->middleware('can:list-timesheets');
            Route::get('/{timesheet}/edit', [TimesheetController::class,'edit'])->name('edit')->middleware('can:edit-timesheet');
            Route::get('/{timesheet}/json', [TimesheetController::class,'json'])->name('json')->middleware('can:edit-timesheet');
            Route::put('/{timesheet}', [TimesheetController::class,'update'])->name('update')->middleware('can:edit-timesheet');
            Route::delete('/{timesheet}', [TimesheetController::class,'destroy'])->name('destroy')->middleware('can:delete-timesheet');
        });


    // CLOCK IN / OUT

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::post('/clock-in', [TimesheetClockController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out', [TimesheetClockController::class, 'clockOut'])->name('clock-out');
        Route::post('/resume-from-lunch', [TimesheetClockController::class, 'resumeFromLunch'])->name('resume-from-lunch');
    });

        // USER TIMESHEET (CALENDAR)
        Route::prefix('users/{user}/timesheets')->name('users.timesheets.')->group(function () {

            Route::get('/', [UserTimesheetController::class, 'index'])->name('index');
            Route::get('/calendar', [UserTimesheetController::class, 'calendarEvents'])->name('calendar');
        });

    // LOCATIONS
    //location routes - custom routes first to avoid conflict with resource routes
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::post('check-unique', [LocationController::class, 'checkUnique'])->name('check-unique');
        Route::get('data', [LocationController::class, 'getLocations'])->name('data');
        Route::post('bulk-delete', [LocationController::class, 'bulkDestroy'])->name('bulk-delete');
        Route::patch('{id}/toggle-active', [LocationController::class, 'toggleActive'])->name('toggle-active');
        Route::patch('{id}/status', [LocationController::class, 'updateStatus'])->name('updateStatus');
        Route::get('{id}/map', [LocationController::class, 'map'])->name('map');
        Route::get('{id}/json', [LocationController::class, 'json'])->name('json');
        Route::get('{id}/modal', [LocationController::class, 'modal'])->name('modal');
        //Entity Information route
        Route::get('/entity/{id}', [LocationController::class, 'entityInfo'])->name('entity.info');

    });

    // Route::resource('locations', LocationController::class)->middleware([
    //     'index'   => 'can:list-locations',
    //     'create'  => 'can:create-location',
    //     'store'   => 'can:create-location',
    //     'show'    => 'can:list-locations',
    //     'edit'    => 'can:edit-location',
    //     'update'  => 'can:edit-location',
    //     'destroy' => 'can:delete-location',
    // ]);
    Route::resource('locations', LocationController::class)->middleware([
        'index' => 'can:list-locations',
         'create' => 'can:create-location',
         'edit'   => 'can:edit-location',
        'destroy'=> 'can:delete-location',
    ]);


    });

  });

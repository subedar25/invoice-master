<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ClientCrudController;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\EntityInfoController;
use App\Http\Controllers\DropPointController;
use App\Http\Controllers\VehicleExpenseController;
use App\Http\Controllers\EntityInlineProxyController;
use App\Http\Controllers\FileController;
use App\Models\File;
use App\Http\Controllers\MasterApp\TimesheetController;
use App\Http\Controllers\MasterApp\TimesheetClockController;
use App\Http\Controllers\MasterApp\DashboardController;
use App\Http\Controllers\MasterApp\UserTimesheetController;
use App\Http\Controllers\UserController1;
use App\Http\Controllers\MasterApp\NotificationController as MasterNotificationController;
use App\Http\Controllers\InvoicePdfController;


Route::get('/', function () {
    return redirect()->route('login.view');
});

Route::get('/dashboard', function () {
    return redirect()->route('masterapp.dashboard');
})->middleware('auth')->name('dashboard');

// Protected
Route::middleware('auth')->group(function () {

    /*  PROFILE  */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/changepassword', [ProfileController::class, 'changepassword'])->name('profile.changepassword');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/changepassword', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifications', function () {
        return redirect()->route('masterapp.notifications.index');
    })->name('notifications.index');

    Route::prefix('invoice')->name('invoice.')->group(function () {
        Route::get('/', function () {
            return view('invoice.index');
        })->name('index')->middleware('can:list-invoices');
        Route::get('/{invoice}/pdf', InvoicePdfController::class)
            ->name('pdf')
            ->middleware('can:list-invoices');
    });

});



// });
require __DIR__ . '/auth.php';
require __DIR__ . '/master-app.php';

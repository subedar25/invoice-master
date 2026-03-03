<?php

namespace App\Http\Controllers\MasterApp;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Models\Client;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleExpense;
use App\Models\File;
use App\Models\Timesheet;
use App\Models\UserStatus;
use Illuminate\Support\Collection;
class EntityInfoController extends Controller
{
   public function show(Request $request, string $type, int $id)
    {
        abort_if(!ctype_digit((string) $id), 404);

        $config = config("entities.$type");
        abort_if(!$config, 404);

        $modelClass = $config['model'];
        $entity = $modelClass::findOrFail($id);

        $displayStatus = null;
        if ($type === 'users') {
            $currentShift = Timesheet::currentShiftForUser($entity->id);
            $clockInModeToStatusLabel = [
                'office'         => 'Available',
                'remote'         => 'Available - Remote',
                'out_of_office'  => 'Available - Out of Office',
                'do_not_disturb' => 'Do Not Disturb',
                'lunch'          => 'Lunch',
            ];
            $statusesList = UserStatus::all();
            if ($currentShift && isset($clockInModeToStatusLabel[$currentShift->clock_in_mode ?? ''])) {
                $displayStatus = $statusesList->firstWhere('label', $clockInModeToStatusLabel[$currentShift->clock_in_mode]);
            } else {
                $displayStatus = $statusesList->firstWhere('label', 'Not Available');
            }
            $displayStatus = $displayStatus ?? $statusesList->firstWhere('label', 'Not Available');
        }

        $currentTab = strtolower($request->query('tab', 'info'));

        // Resolve tab view so it works on both case-sensitive (Linux) and case-insensitive (Windows) filesystems
        $tabViewName = "masterapp.entity.tabs.{$type}.{$currentTab}";
        if (!View::exists($tabViewName)) {
            $configTabLabel = collect($config['tabs'] ?? [])->first(fn ($t) => strtolower($t) === $currentTab);
            if ($configTabLabel !== null) {
                $tabViewName = "masterapp.entity.tabs.{$type}.{$configTabLabel}";
            }
        }

        // Always define (VERY IMPORTANT)
        $expenses = collect();

        // Only load when needed
         if ($type === 'vehicles' && $currentTab === 'expenses') {

        $expenses = VehicleExpense::query()
            ->with([
                'file',   // receipt
                'user',   // creator (IMPORTANT for Blade)
            ])
            ->where('vehicle_id', $entity->id)
            ->orderByDesc('date')
            ->get();
    }
    // Permissions (NULL SAFE)
    $user = auth()->user();

    $permissions = $config['permissions'] ?? [];

    return view('masterapp.entity.info', [
        'type'         => $type,
        'entity'       => $entity,
        'vehicle'      => $entity,
        'displayStatus' => $displayStatus, 
        // Tabs / Data
        'currentTab'   => $currentTab,
        'tabViewName'  => $tabViewName,
        'tabs'         => $config['tabs'] ?? [],
        'expenses'  => $expenses,

        // Permissions
        'canEdit'   => $user?->can($permissions['edit']   ?? '') ?? false,
        'canDelete' => $user?->can($permissions['delete'] ?? '') ?? false,
        'canCreate' => $user?->can($permissions['create'] ?? '') ?? false,
        'canView'   => $user?->can($permissions['view']   ?? '') ?? false,
        // Supporting Data
        'users'     => User::orderBy('first_name')->get(),
        // 'clients'   => Client::orderBy('name')->get(),
        // 'files'     => File::orderBy('name')->get(),

        // Config
        'config'    => $config,
    ]);
    }
    

}

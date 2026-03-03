<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleExpense;
use App\Models\File;
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

        $tab = strtolower($request->query('tab', 'info'));

        // Always define (VERY IMPORTANT)
        $expenses = collect();

        // Only load when needed
         if ($type === 'vehicles' && $tab === 'expenses') {

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

    return view('entity.info', [
        'type'      => $type,

        // Entity
        'entity'    => $entity,
        'vehicle'   => $entity, 
        // Tabs / Data
        'tab'       => $tab,
        'tabs'      => $config['tabs'] ?? [],
        'expenses'  => $expenses,

        // Permissions
        'canEdit'   => $user?->can($permissions['edit']   ?? '') ?? false,
        'canDelete' => $user?->can($permissions['delete'] ?? '') ?? false,
        'canCreate' => $user?->can($permissions['create'] ?? '') ?? false,
        'canView'   => $user?->can($permissions['view']   ?? '') ?? false,
        // Supporting Data
        'users'     => User::orderBy('first_name')->get(),
        'clients'   => Client::orderBy('name')->get(),
        'files'     => File::orderBy('name')->get(),

        // Config
        'config'    => $config,
    ]);
        // return view('entity.info', [
        //     'type'     => $type,
        //     'entity'   => $entity,   // vehicle object
        //     'vehicle'  => $entity,   // alias for convenience
        //     'expenses' => $expenses,
        //     'config'   => $config,
        //     'tab'      => $tab,
        //     'tabs'     => $config['tabs'] ?? [],
        //     'canEdit'  => auth()->user()->can($config['permissions']['edit'] ?? ''),
        //     'canDelete'=> auth()->user()->can($config['permissions']['delete'] ?? ''),
        //     'canCreate'=> auth()->user()->can($config['permissions']['create'] ?? ''),
        //     'canView'  => auth()->user()->can($config['permissions']['view'] ?? ''),
        //     'users'    => User::orderBy('name')->get(),
        //     'clients'  => Client::orderBy('name')->get(),
        //     'files'    => File::orderBy('name')->get(),
            
        // ]);
    }
    

}

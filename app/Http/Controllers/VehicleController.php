<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    //  * INDEX
    public function index()
    {
        $vehicles = Vehicle::with('driver')->get();

        $drivers = User::where('driver', 1)
            ->orWhereHas('roles', fn ($q) => $q->where('name', 'Driver'))
            ->get(['id', 'first_name']);

        return view('vehicles.index', compact('vehicles', 'drivers'));
    }

    //  * CREATE FORM
    public function create()
    {
        $drivers = $this->drivers();

        return view('vehicles.create', compact('drivers'));
    }

    //  * STORE (CREATE)
    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        $vehicle = Vehicle::create($validated);

        // AJAX (modal)
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle created successfully!',
            ]);
        }

        // Normal submit
        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Vehicle created successfully!');
    }

    //  * EDIT FORM
    public function edit(Vehicle $vehicle)
    {
        $drivers = $this->drivers();

        return view('vehicles.edit', compact('vehicle', 'drivers'));
    }

    //  * UPDATE (INLINE + FULL FORM)
    public function update(Request $request, Vehicle $vehicle)
    {
        /* ---------- INLINE EDIT ---------- */
        $inlineFields = [
            'driver_id',
            'vin',
            'description',
            'active',
            'hitch',
            'driver_side_sponsor',
            'passenger_side_sponsor',
        ];

        $isInline =
            $request->ajax()
            && count($request->except(['_token', '_method'])) === 1
            && $request->hasAny($inlineFields);

        if ($isInline) {
            $field = array_key_first($request->except(['_token', '_method']));
            $value = $request->input($field);

            $rules = match ($field) {
                'driver_id' => ['nullable', 'exists:users,id'],
                'vin' => ['required', 'string', 'max:17'],
                'description',
                'driver_side_sponsor',
                'passenger_side_sponsor' => ['required', 'string'],
                'active',
                'hitch' => ['required', 'in:0,1'],
                default => null,
            };

            if (!$rules) {
                return response()->json(['message' => 'Invalid field'], 422);
            }

            $request->validate([$field => $rules]);

            if (in_array($field, ['active', 'hitch'])) {
                $value = (int) $value;
            }

            $vehicle->update([$field => $value]);

            return response()->json([
                'success' => true,
                'field'   => $field,
                'value'   => $value,
            ]);
        }

        /* ---------- FULL FORM UPDATE ---------- */
        $validated = $this->validatedData($request);

        $vehicle->update($validated);

        // AJAX (modal edit)
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle updated successfully!',
            ]);
        }

        // Normal submit
        return redirect()
            ->route('entity.info', [
                'type' => 'vehicles',
                'id'   => $vehicle->id,
            ])
            ->with('success', 'Vehicle updated successfully!');
    }

    //  * DUPLICATE FORM
    public function duplicate($id)
    {
        $original = Vehicle::findOrFail($id);

        $vehicle = $original->replicate();
        $drivers = $this->drivers();

        return view('vehicles.duplicate', [
            'vehicle'     => $vehicle,
            'original_id' => $id,
            'drivers'     => $drivers,
        ]);
    }

    //  * DUPLICATE STORE
    public function storeDuplicate(Request $request, $id)
    {
        $validated = $this->validatedData($request);

        Vehicle::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle duplicated successfully!',
        ]);
    }

    //  * DELETE
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully!',
        ]);
    }

    //  * BULK DELETE
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (!is_array($ids) || empty($ids)) {
                return response()->json(['message' => 'No vehicles selected'], 422);
            }

            $count = Vehicle::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} vehicles deleted successfully.",
            ]);
        } catch (\Throwable $e) {
            Log::error('Vehicle bulk delete failed', [
                'error' => $e->getMessage(),
                'ids'   => $request->input('ids'),
            ]);

            return response()->json([
                'message' => 'Server error while deleting vehicles',
            ], 500);
        }
    }

    //  * SHOW (ENTITY PAGE)
    public function show(Request $request, Vehicle $vehicle)
    {
        $tab = strtolower($request->get('tab', 'info'));
        $expenses = collect();

        if ($tab === 'expenses') {
            $expenses = $vehicle->expenses()->latest('date')->get();
        }

        return view('entity.index', [
            'entity'   => $vehicle,
            'expenses' => $expenses,
        ]);
    }

    //  * HELPERS
    private function drivers()
    {
        return User::where('driver', 1)
            ->orWhereHas('roles', fn ($q) => $q->where('name', 'Driver'))
            ->get(['id', 'name']);
    }

    private function validatedData(Request $request)
    {
        return $request->validate([
            'driver_id' => 'nullable|exists:users,id',
            'vin' => 'required|string|max:17',
            'description' => 'required|string',
            'active' => 'boolean',
            'hitch' => 'boolean',
            'driver_side_sponsor' => 'required|string',
            'passenger_side_sponsor' => 'required|string',
        ]);
    }
}

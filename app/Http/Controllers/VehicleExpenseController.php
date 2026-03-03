<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleExpense;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class VehicleExpenseController extends Controller
{
    //  | CREATE
  public function create(Vehicle $vehicle)
    {
        // Authorize creation of a vehicle expense
        // $this->authorize('create', VehicleExpense::class);

        return view(
            'entity.tabs.vehicles.expenses.create',
            [
                'vehicle' => $vehicle,
            ]
        );
    }

    //  | STORE

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'total'      => ['required', 'numeric', 'min:0'],
            'mileage'    => ['required', 'integer', 'min:0'],
            'type'       => ['required', 'in:fuel,maintenance,other'],
            'date'       => ['required', 'date'],
            'notes'      => ['nullable', 'string'],
            'file'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
        ]);

        DB::beginTransaction();

        try {
            //  Create expense
            $expense = VehicleExpense::create([
                'vehicle_id' => $data['vehicle_id'],
                'total'      => $data['total'],
                'mileage'    => $data['mileage'],
                'type'       => $data['type'],
                'date'       => $data['date'],
                'notes'      => $data['notes'] ?? null,
                'user_id'   => auth()->id(),
            ]);

            //  Optional file upload
            if ($request->hasFile('file')) {
                $this->storeExpenseFile(
                    $request->file('file'),
                    $expense
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle expense created successfully',
                'id'      => $expense->id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create expense',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    //  | EDIT
    public function edit(Vehicle $vehicle, VehicleExpense $expense)
    {
        abort_if($expense->vehicle_id !== $vehicle->id, 404);
        // $this->authorize('update', $expense);

        return view(
            'entity.tabs.vehicles.expenses.edit',
            compact('vehicle', 'expense')
        );
    }

    //  | UPDATE
    //  | - Inline edit (single field)
    //  | - Full modal edit
//     public function update(Request $request, Vehicle $vehicle, VehicleExpense $expense)
//     {
//         logger([
//     'route_vehicle' => $vehicle->id,
//     'expense_vehicle' => $expense->vehicle_id,
//     'expense_id' => $expense->id,
// ]);

//          // Security check
//     abort_if($expense->vehicle_id !== $vehicle->id, 404);
//         // INLINE EDIT (AJAX)
//         if (
//             $request->ajax() &&
//             count($request->except(['_token', '_method'])) === 1
//         ) {
//             $field = array_key_first(
//                 $request->except(['_token', '_method'])
//             );

//             $rules = [
//                 'total'   => ['numeric', 'min:0'],
//                 'mileage' => ['integer', 'min:0'],
//                 'type'    => ['in:fuel,maintenance,other'],
//                 'notes'   => ['nullable', 'string'],
//                 'date'    => ['date'],
//             ];

//             if (!isset($rules[$field])) {
//                 return response()->json([
//                     'message' => 'Invalid field'
//                 ], 422);
//             }

//             $request->validate([$field => $rules[$field]]);
//             $expense->update([$field => $request->input($field)]);

//             return response()->json(['success' => true]);
//         }

//         // FULL FORM UPDATE
//         $data = $request->validate([
//             'total'   => ['required', 'numeric', 'min:0'],
//             'mileage' => ['required', 'integer', 'min:0'],
//             'type'    => ['required', 'in:fuel,maintenance,other'],
//             'date'    => ['required', 'date'],
//             'notes'   => ['nullable', 'string'],
//             'file'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
//         ]);

//         if ($request->hasFile('file')) {
//             $this->storeExpenseFile(
//                 $request->file('file'),
//                 $expense
//             );
//         }

//         $expense->update($data);

//         return response()->json([
//             'success' => true,
//             'message' => 'Expense updated successfully',
//         ]);
//     }
public function update(
    Request $request,
    Vehicle $vehicle,
    VehicleExpense $expense
) {
    abort_if($expense->vehicle_id !== $vehicle->id, 404);

    $data = $request->validate([
        'total'   => ['required', 'numeric', 'min:0'],
        'mileage' => ['required', 'integer', 'min:0'],
        'type'    => ['required', 'in:fuel,maintenance,other'],
        'date'    => ['required', 'date'],
        'notes'   => ['nullable', 'string'],
        'file'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
    ]);

    if ($request->hasFile('file')) {
        $this->storeExpenseFile(
            $request->file('file'),
            $expense
        );
    }

    $expense->update($data);

    return response()->json([
        'success' => true,
        'message' => 'Expense updated successfully'
    ]);
}

public function inlineUpdate(Request $request, VehicleExpense $expense)
{
    abort_unless($request->ajax(), 404);

    $field = array_key_first(
        $request->except(['_token', '_method'])
    );

    $rules = [
        'total'   => ['numeric', 'min:0'],
        'mileage' => ['integer', 'min:0'],
        'type'    => ['in:fuel,maintenance,other'],
        'notes'   => ['nullable', 'string'],
        'date'    => ['date'],
    ];

    abort_unless(isset($rules[$field]), 422);

    $request->validate([$field => $rules[$field]]);

    $expense->update([
        $field => $request->input($field)
    ]);

    return response()->json(['success' => true]);
}


    //  | DELETE (SINGLE)
    public function destroy(VehicleExpense $expense)
    {
        // $this->authorize('delete', $expense);
        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense deleted successfully',
        ]);
    }

    //  | BULK DELETE
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'message' => 'No expenses selected'
            ], 422);
        }

        VehicleExpense::whereIn('id', $ids)->delete();

        return response()->json([
            'message' => 'Expenses deleted successfully'
        ]);
    }

    //  | RECEIPT UPLOAD FORM
    public function uploadReceiptForm(VehicleExpense $expense)
    {
        return view(
            'entity.tabs.vehicles.expenses.upload-receipt',
            [
                'expense' => $expense,
                'vehicle' => $expense->vehicle,
            ]
        );
    }

    //  | STORE FILE (GOOGLE DRIVE STYLE)
    //  | - MD5 de-duplication
    //  | - Safe, isolated
    // protected function storeExpenseFile($file, VehicleExpense $expense): void
    // {
    //     $md5 = md5_file($file->getRealPath());

    //     // Reuse existing file if already uploaded
    //     $existing = File::where('google_drive_md5', $md5)->first();
    //     if ($existing) {
    //         $expense->update(['file_id' => $existing->id]);
    //         return;
    //     }

    //     // Upload to Google Drive
    //         // $driveId = app('google.drive')->upload($file);
    //     $driveId = app(GoogleDriveService::class)->upload($file);
    //     $fileModel = File::create([
    //         'google_drive_id'  => $driveId,
    //         'google_drive_md5' => $md5,
    //         'name'             => $file->getClientOriginalName(),
    //         'notes'            => '',
    //         'file_name'        => $file->getClientOriginalName(),
    //         'file_type'        => $file->getMimeType(),
    //         'added_timestamp'  => now(),
    //         'user_id'    => auth()->id(),
    //         'slug'             => (string) Str::uuid(),
    //     ]);

    //     $expense->update([
    //         'file_id' => $fileModel->id
    //     ]);
    // }
protected function storeExpenseFile($uploadedFile, VehicleExpense $expense): File
{
    //  Ensure directory exists
    $directory = 'vehicle-expense-receipts';

    if (!Storage::disk('private')->exists($directory)) {
        Storage::disk('private')->makeDirectory($directory);
    }

    //  Generate filename
    $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
    $path = $directory . '/' . $filename;

    //  ACTUALLY STORE FILE (THIS WAS MISSING )
    Storage::disk('private')->put(
        $path,
        file_get_contents($uploadedFile->getRealPath())
    );

    //  Create DB record
    $file = File::create([
        'google_drive_id'  => null,
        'google_drive_md5' => md5_file($uploadedFile->getRealPath()),
        'name'             => $uploadedFile->getClientOriginalName(),
        'notes'            => '',
        'file_name'        => $path, //  relative to private disk
        'file_type'        => $uploadedFile->getMimeType(),
        'added_timestamp'  => now(),
        'user_id'          => auth()->id(),
        'slug'             => (string) Str::uuid(),
    ]);

    //  Attach file to expense
    $expense->update([
        'file_id' => $file->id,
    ]);
   return $file;
}

    //  | RECEIPT UPLOAD (INLINE)
public function uploadReceipt(Request $request, VehicleExpense $expense)
{
    $request->validate([
        'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
    ]);

    $file = $this->storeExpenseFile(
        $request->file('file'),
        $expense
    );

    return response()->json([
        'success'     => true,
        'message'     => 'Receipt uploaded successfully',
        'expense_id'  => $expense->id,
        'preview_url' => route('files.show', $file->id),
    ]);
}


    //  | RECEIPT DELETE
    public function deleteReceipt(VehicleExpense $expense)
    {
        $expense->update(['file_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Receipt removed successfully',
        ]);
    }
}

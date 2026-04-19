<?php
namespace App\Http\Controllers\MasterApp;

use App\Models\Module;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Yajra\DataTables\Facades\DataTables;
use App\Policies\ModulePolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Core\Modules\Services\ModulesService;
use App\Http\Requests\MasterApp\Modules\ModulesStoreRequest;
use App\Http\Requests\MasterApp\Modules\ModulesUpdateRequest;
use Illuminate\Http\JsonResponse;


class  ModuleController extends Controller
{
   
    /**
     * Display a listing of the resource.
     */

    public function index()
    { 
         $modules = Module::orderBy('id', 'desc')->paginate(200);
        // No longer fetching modules here. The view will be empty initially.
        return view('masterapp.modules.index', compact('modules'));
    }


    /**
     * Return data for the Modules DataTable.
     * This method handles the AJAX requests from the DataTable.
     */
    // public function getModules(Request $request)
    // {
    //     //  $this->authorize('modules.index');

    //     if ($request->ajax()) {
    //         // ... your yajra datatables code ...
    //         $data = Module::query();
    //         return DataTables::of($data)
    //             ->addIndexColumn()
    //             ->addColumn('checkbox', function ($row) {
    //                 return '<input type="checkbox" class="row-check" value="' . $row->id . '">';
    //             })
    //             ->addColumn('permissions_count', function ($row) {
    //                 return $row->permissions->count();
    //             })
    //             ->rawColumns(['checkbox'])
    //             ->setRowAttr(['data-id' => 'id'])
    //             ->make(true);
    //     }
    //     return response()->json(['error' => 'Not an AJAX request'], 400);
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('masterapp.modules.create');
    }

     public function store(
        ModulesStoreRequest $request,
        ModulesService $service
    ) {
         $service->create($request->validated());

        return response()->json([
            'message' => 'Module created successfully!'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     // 1. Validate the request data.
    //     // If validation fails, Laravel automatically returns a 422 JSON response.
    //     $validatedData = $request->validate([
    //         'name' => 'required|string|max:255|unique:modules,name',
    //         'slug' => 'required|string|max:255|unique:modules,slug',
    //     ]);

    //     // 2. Create the module.
    //     $module = Module::create($validatedData);

    //     // 3. Return a successful JSON response.
    //     return response()->json([
    //         'success' => 'Module created successfully!',
    //         'redirect' => route('masterapp.modules.index')
    //     ], 200);
    // }

    /**
     * Display the specified resource.
     */
    public function show(Module $module)
    {
        // For this CRUD, we can redirect to the edit page or index.
        // Let's redirect to the edit page for simplicity.
        return redirect()->route('masterapp.modules.edit', $module);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Module $module)
    {
        return view('masterapp.modules.edit', compact('module'));
    }

    /**
     * Update the specified resource in storage.
     */

     public function update(ModulesUpdateRequest $request, int $id,ModulesService $service)
    {
         $service->update($id, $request->validated());
        return response()->json([
            'message' => 'Module updated successfully!',
            'redirect' => route('masterapp.modules.index')
        ], 200);
    }

    public function toggleActive(Module $module): JsonResponse
    {
        $module->is_active = ! (bool) $module->is_active;
        $module->save();

        return response()->json([
            'message' => $module->is_active
                ? 'Module activated successfully.'
                : 'Module deactivated successfully.',
            'is_active' => (bool) $module->is_active,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */

      public function destroy(int $id, ModulesService $service)
        { 
            $service->delete($id);
            return response()->json(['message' => 'Module deleted successfully!'], 200);
        }


    public function bulkDestroy(Request $request)
    {
        // $this->authorize('modules.bulk-delete');

        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:modules,id'
            ]);

            $ids = $request->input('ids');
            $deletedCount = Module::whereIn('id', $ids)->delete();

            return response()->json([
                'message' => "{$deletedCount} module(s) deleted successfully!"
            ]);
        } catch (\Exception $e) {
            Log::error('Module Bulk Deletion Error: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while trying to delete the module(s).'], 500);
        }
    }
}

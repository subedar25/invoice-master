<?php

namespace App\Http\Controllers;

use App\Models\DropPoint;
use App\Models\ClientTypes;
use App\Models\Client;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DropPointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
          return view('droppoints.index');
        
    }


    
    /**
     * Return data for the Modules DataTable.
     * This method handles the AJAX requests from the DataTable.
     */
    public function getDropPoints(Request $request)
    {
        //  $this->authorize('modules.index');

        if ($request->ajax()) {
            // ... your yajra datatables code ...
            $data = DropPoint::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="row-check" value="' . $row->id . '">';
                })
                ->rawColumns(['checkbox'])
                ->setRowAttr(['data-id' => 'id'])
                ->make(true);
        }
        return response()->json(['error' => 'Not an AJAX request'], 400);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         $ClientTypes = ClientTypes::all();
         $Clients = Client::all();

        return view('droppoints.create', compact('ClientTypes','Clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DropPoint::create($request->all());

        return redirect()->route('droppoints.index')
                         ->with('success', 'DropPoint created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DropPoint $dropPoint)
    {
        return view('droppoints.show', compact('dropPoint'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DropPoint $droppoint)
    {
         $ClientTypes = ClientTypes::all();
         $Clients = Client::all();
        return view('droppoints.edit', compact('droppoint','ClientTypes','Clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DropPoint $droppoint)
    {
        $request->validate([
            'name' => 'required|string|max:255',
           
        ]);


         try {
      
        $droppoint->update($request->all());
        
        
    } catch (\Exception $e) {
        // Log the error

         return response()->json([
            'message' => $e->getMessage().$e->getTraceAsString(),
            //'redirect' => route('droppoints.index')
        ], 200);

       
    }


//         $dropPoint->update($request->all());
// echo "<pre>"; print_r($request->all());die;
         return response()->json([
            'message' => 'DropPoint updated successfully!',
            'redirect' => route('droppoints.index')
        ], 200);
       
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DropPoint $dropPoint)
    {
        $dropPoint->delete();

        return redirect()->route('droppoints.index')
                         ->with('success', 'DropPoint deleted successfully.');
    }


       public function bulkDestroy(Request $request)
    {
    

        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:modules,id'
            ]);

            $ids = $request->input('ids');
            $deletedCount = DropPoint::whereIn('id', $ids)->delete();

            return response()->json([
                'message' => "{$deletedCount} DropPoint(s) deleted successfully!"
            ]);
        } catch (\Exception $e) {
            Log::error('DropPoint Bulk Deletion Error: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while trying to delete the DropPoint(s).'], 500);
        }
    }
}
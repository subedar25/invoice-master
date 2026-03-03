<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    //Index
public function index(Request $request)
{
    $query = Client::query();

    if ($request->active !== null)
        $query->where('active', $request->active);

    if ($request->open !== null)
        $query->where('open', $request->open);

    if ($request->contact_preference !== null)
        $query->where('contact_preference', $request->contact_preference);
    if($request->address_state !== null)
        $query->where('address_state', $request->address_state);
    $clients = $query->with([
        'primaryContact',
        'primaryAdRep',
        'secondaryAdRep'
    ])->paginate(20);

    //  THESE WERE MISSING
    $contacts = User::select('id','name')->orderBy('name')->get();
    $primaryAdReps = User::select('id','name')->orderBy('name')->get();
    $secondaryAdReps = User::select('id','name')->orderBy('name')->get();
    $adReps   = User::select('id','name')->orderBy('name')->get();

    return view('clients.index', compact('clients','contacts','primaryAdReps','secondaryAdReps', 'adReps'));
}

    // CREATE
    public function create()
    {
        $users = User::select('id','name')->get();
        $adReps = $users; 
        // $states = Client::select('address_state')->all();
        // $states = Client::whereNotNull('address_state')->distinct()->orderBy('address_state')->pluck('address_state');

    $states = config('states');


        return view('clients.create', compact('users', 'adReps', 'states'));
    }

    // STORE
    // public function store(Request $request)
    // {
    //     Client::create($request->only((new Client)->getFillable()));

    //     return redirect()->route('clients.index')
    //         ->with('success', 'Client created successfully.');
    // }
public function store(Request $request)
{
    $client = Client::create($request->only((new Client)->getFillable()));

    if ($request->ajax()) {
        return response()->json(['success' => true]);
    }

    return redirect()->route('masterapp.contacts.index')
        ->with('success', 'Client created successfully.');
}

    // EDIT
    public function edit(Client $client)
    {
        $users = User::select('id','name')->get();
        $adReps = $users;
        $states = config('states');

        return view('clients.edit', compact('client', 'users', 'adReps', 'states'));
    }

    // UPDATE (inline + full update)
    public function update(Request $request, Client $client)
    {
        // Inline update detection (AJAX edit)
        if ($request->ajax() && count($request->except('_token')) === 1) {

            $field = array_keys($request->except('_token'))[0];
            $value = $request->input($field);

            if (!in_array($field, $client->getFillable())) {
                return response()->json(['error' => 'Invalid field'], 422);
            }

            $client->{$field} = $value;
            $client->save();

            return response()->json(['success' => true]);
        }

        // Full update (edit form)
        $client->update($request->only($client->getFillable()));

        return redirect()->route('masterapp.contacts.index')
            ->with('success', 'Client updated successfully.');
    }

    // DELETE (SOFT DELETE)
    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(['success' => true]);
    }

    // BULK DELETE
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids ?? [];
        Client::whereIn('id', $ids)->delete();

        return response()->json(['success' => true]);
    }

    // TOGGLE ACTIVE
    public function toggleActive(Request $request, Client $client)
    {
        $client->active = $request->active ? 1 : 0;
        $client->save();

        return response()->json(['success' => true]);
    }

    // DUPLICATE 
//     public function duplicate(Request $request)
// {
   
//     $request->validate([
//         'source_id' => 'required|exists:clients,id',   
//         'name' => 'required|string',
//         'phone' => 'nullable|string'
//     ]);

//     $source = Client::findOrFail($request->source_id); 

//     $copy = $source->replicate();
//     $copy->name = $request->name;
//     $copy->phone = $request->phone;
//     $copy->save();

//     // return redirect()->route('clients.index')
//     //     ->with('success', 'Client duplicated successfully.');
//     return view('clients.duplicate', [
//         'original' => $client
    
//     ]);
// }
// SHOW DUPLICATE FORM (NO VALIDATION)
public function duplicate(Client $client)
{
    $states = config('states');
    return view('clients.duplicate', compact('client', 'states'));
}


//STORE Duplicate
    public function storeDuplicate(Request $request, Client $client)
{
    $copy = $client->replicate();
    $copy->name = $request->name;
    $copy->phone = $request->phone;
    $copy->save();
   
    // return redirect()->route('clients.index')
    //     ->with('success', 'Client duplicated successfully.');
           return response()->json(['message' => "Client duplicated successfully."],200);
}


    // INLINE UPDATE USING PK, X-editable style
    // public function inlineUpdate(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'pk' => 'required|exists:clients,id',
    //         'name' => 'required|string',
    //         'value' => 'nullable'
    //     ]);

    //     if ($validator->fails())
    //         return response()->json(['error' => $validator->errors()->first()], 422);

    //     $client = Client::find($request->pk);

    //     if (!in_array($request->name, $client->getFillable()))
    //         return response()->json(['error' => 'Invalid field'], 422);

    //     $client->{$request->name} = $request->value;
    //     $client->save();

    //     return response()->json(['success' => true]);

    // }

     
}

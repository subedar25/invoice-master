<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleExpense;

class EntityInlineProxyController extends Controller
{
    /**
     * Proxy inline-edit PATCH calls from entity page
     */
    public function vehicle(Request $request)
{
    // Remove framework/meta fields
    $data = collect($request->all())
        ->except(['_token', '_method'])
        ->toArray();

    if (count($data) !== 1) {
        return response()->json([
            'message' => 'Invalid inline edit payload',
            'received' => $data
        ], 422);
    }

    $field = array_key_first($data);
    $value = $data[$field];

    /**
     * EXPENSE ID SOURCE
     * -----------------
     * inline-edit.js always edits a table row
     * we already have <tr data-id="EXPENSE_ID">
     * jQuery sends it automatically as part of request
     */
    $expenseId =
        $request->input('id') ??
        $request->input('expense_id') ??
        $request->route('expense');

    if (!$expenseId) {
        return response()->json([
            'message' => 'Expense ID missing'
        ], 422);
    }

    $expense = \App\Models\VehicleExpense::findOrFail($expenseId);

    // Field validation
    $rules = match ($field) {
        'total'   => ['required','numeric','min:0'],
        'mileage' => ['required','integer','min:0'],
        'type'    => ['required','in:fuel,maintenance,other'],
        'notes'   => ['nullable','string'],
        'date'    => ['required','date'],
        'user_id' => ['required','exists:users,id'],
        default   => null,
    };

    if (!$rules) {
        return response()->json([
            'message' => "Field [$field] not allowed"
        ], 422);
    }

    $request->validate([$field => $rules]);

    $expense->update([$field => $value]);

    return response()->json(['success' => true]);
}

}

<?php

namespace App\Http\Requests\MasterApp\TimeOffRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeOffRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'paid' => 'boolean',
            'status' => 'required|in:pending,denied,approved_paid,approved_unpaid',
            'notes' => 'nullable|string',
            'submitted' => 'boolean',
            'timesheet_id' => 'nullable|integer',
        ];
    }
}

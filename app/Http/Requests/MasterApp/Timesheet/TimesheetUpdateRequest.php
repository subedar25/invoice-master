<?php

namespace App\Http\Requests\MasterApp\Timesheet;

use Illuminate\Foundation\Http\FormRequest;

class TimesheetUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time' => ['required', 'date'],
            'end_time'   => ['nullable', 'date', 'after:start_time'],
            'type'       => ['required', 'string'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ];
    }
}

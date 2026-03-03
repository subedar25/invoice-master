<?php

namespace App\Http\Requests\MasterApp\Timesheet;

use Illuminate\Foundation\Http\FormRequest;

class TimesheetStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['required', 'exists:users,id'],
            'start_time'    => ['required', 'date'],
            'end_time'      => ['nullable', 'date', 'after:start_time'],
            'clock_in_mode' => ['required', 'in:office,remote,out_of_office,do_not_disturb'],
            'type'          => ['required', 'string'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ];
    }
}

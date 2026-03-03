<?php

namespace App\Http\Requests\MasterApp\Timesheet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClockTimesheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in_mode' => [
                'required',
                Rule::in([
                    'office',
                    'remote',
                    'out_of_office',
                    'do_not_disturb'
                ]),
            ],
            'reason' => ['nullable', 'string'],
        ];
    }
}

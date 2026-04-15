<?php

namespace App\Http\Requests\MasterApp\Location;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('locations', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('location')),
            ],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'regex:/^\+1\s\(\d{3}\)\s\d{3}-\d{4}$/'],
            'show_map' => ['boolean'],
            'show_map_link' => ['boolean'],
        ];
    }
}

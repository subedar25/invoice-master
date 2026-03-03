<?php

namespace App\Http\Requests\MasterApp\Location;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationStoreRequest extends FormRequest
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
                Rule::unique('locations', 'name')->whereNull('deleted_at'),
            ],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string',],
            'country' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            // 'phone' => ['nullable', 'string', 'regex:/^\+1\s\(\d{3}\)\s\d{3}-\d{4}$/'],
            'phone' => ['nullable', 'digits:10'],
            'show_map' => ['boolean'],
            'show_map_link' => ['boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}

<?php

namespace App\Http\Requests\MasterApp\Permissions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PermissionsStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
         return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->where(function ($query) {
                    return $query->where('guard_name', $this->input('guard_name', 'web'));
                }),
            ],
            'display_name' => 'nullable|string|max:255',
            'module_id' => 'required|exists:modules,id',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'guard_name' => 'required|string|in:web,api', // Usually guard_name is either 'web' or 'api'
            'is_active' => 'nullable|boolean',
        ];
    }

     protected function prepareForValidation()
    {
        $this->merge([
            'slug' => Str::slug($this->name),
            'guard_name' => $this->guard_name ?? 'web', // Default to 'web' if not provided
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Permission name already exists.',
            'slug.unique' => 'Permission slug already exists.',
        ];
    }
}

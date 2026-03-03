<?php
namespace App\Http\Requests\MasterApp\Roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RolesUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $roleId = $this->route('role');
       
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Using the Rule facade for clarity and explicitness
                Rule::unique('roles', 'name')->ignore($roleId, 'id'),
            ],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'is_active' => ['nullable', 'boolean'],
            // Require at least one permission when editing a role
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.required' => 'At least select 1 permission.',
            'permissions.min' => 'At least select 1 permission.',
        ];
    }
   
}

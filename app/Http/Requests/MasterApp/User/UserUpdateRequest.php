<?php
namespace App\Http\Requests\MasterApp\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = $this->route('id');
        // $userTable = config('backpack.permissionmanager.models.user', 'users');
        // echo $userId;exit;
        return [
            'first_name' => 'required|string|max:100|regex:/^[A-Za-z\s]+$/',
            'last_name'  => 'required|string|max:100|regex:/^[A-Za-z\s]+$/',
            'email'        => ['required','email',Rule::unique('users')->ignore($userId),],
            'password'     => ['nullable', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'phone'        => 'nullable|string|regex:/^[0-9]+$/',
            'roles'   => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'active' => ['required', Rule::in(['0', '1'])],
            'status_notes'  => 'nullable|string|max:200',
            'organization_ids' => ['nullable', 'array'],
            'organization_ids.*' => ['exists:organizations,id'],
            'reporting_manager_id' => ['nullable', 'exists:users,id'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'other_documents' => ['nullable', 'array'],
            'other_documents.*' => ['file', 'max:10240'],
            'is_wordpress_user' => 'sometimes|boolean',

        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('roles')) {
            $this->merge([
                'roles' => array_filter(array_map('intval', $this->input('roles', []))),
            ]);
        }
    
    if ($this->filled('department_id')) {
        $this->merge([
            'department_id' => (int) $this->input('department_id'),
        ]);
    }

    if ($this->has('organization_ids')) {
        $this->merge([
            'organization_ids' => array_filter(array_map('intval', $this->input('organization_ids', []))),
        ]);
    }

    if ($this->filled('reporting_manager_id')) {
        $this->merge([
            'reporting_manager_id' => (int) $this->input('reporting_manager_id'),
        ]);
    }

    if ($this->has('is_wordpress_user')) {
        $this->merge([
            'is_wordpress_user' => (int) $this->input('is_wordpress_user'),
        ]);
    }
    }
}

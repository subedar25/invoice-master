<?php

namespace App\Http\Requests\MasterApp\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
class UserStoreRequest extends FormRequest
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
        $userTable = config('backpack.permissionmanager.models.user', 'users');

        return [
            'first_name' => 'required|string|max:100|regex:/^[A-Za-z\s]+$/',
            'last_name'  => 'required|string|max:100|regex:/^[A-Za-z\s]+$/',
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (User::withTrashed()->where('email', $value)->exists()) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            // 'email' => ['required','email','max:255',Rule::unique('users', 'email'),],
            'password'     => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'phone' => 'nullable|string|max:10|regex:/^[0-9]+$/',
            'active' => 'required|boolean',
            'status_notes'  => 'nullable|string|max:200',
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
            'department_id' => 'nullable|exists:departments,id',
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
    //   if ($this->has('departments')) {
    //     $this->merge([
    //         'departments' => array_map('intval', $this->input('departments', [])),
    //     ]);
    // }
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

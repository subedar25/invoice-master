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
            'password'     => ['nullable', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'phone' => 'nullable|string|max:10|regex:/^[0-9]+$/',
            'active' => 'required|boolean',
            'driver' => 'required|boolean',
            'status_id'     => 'nullable|exists:user_statuses,id',
            'status_notes'  => 'nullable|string|max:200',
            'roles' => ['nullable', 'array'],
            // 'roles.*'       => 'exists:roles,id',
            'contributor_status' => 'nullable|in:no,current,past',
            'publications'   => 'nullable|array',
            'publications.*' => 'nullable|exists:publications,id',
            'department_id' => 'nullable|exists:departments,id',
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
         if ($this->has('publications')) {
        $this->merge([
            'publications' => array_map('intval', $this->input('publications', [])),
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

    if ($this->filled('status_id')) {
        $this->merge([
            'status_id' => (int) $this->input('status_id'),
        ]);
    }
    if ($this->has('is_wordpress_user')) {
        $this->merge([
            'is_wordpress_user' => (int) $this->input('is_wordpress_user'),
        ]);
    }

    }


}

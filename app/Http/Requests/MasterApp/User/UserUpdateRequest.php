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
            // 'contributor_status' => ['nullable',Rule::in(['No', 'Current', 'Past']),],
            'contributor_status' => 'nullable|in:no,current,past',
            'publications'   => ['nullable', 'array'],
            'publications.*' => ['exists:publications,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'active' => ['required', Rule::in(['0', '1'])],
            'driver' => ['required', Rule::in(['0', '1'])],
            'status_id'     => 'nullable|exists:user_statuses,id',
            'status_notes'  => 'nullable|string|max:200',
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

<?php
namespace App\Http\Requests\MasterApp\User;

use App\Models\Role;
use App\Models\User;
use App\Support\CurrentOrganization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

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
            'designation_id' => ['nullable', 'exists:user_designation,id'],
            'active' => ['required', Rule::in(['0', '1'])],
            'user_type' => ['required', Rule::in(['systemuser', 'superadmin', 'admin', 'user'])],
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
            'remove_photo' => ['nullable', 'boolean'],
            'remove_documents' => ['nullable', 'array'],
            'remove_documents.*' => ['exists:user_documents,id'],

        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $rm = $this->input('reporting_manager_id');
            if ($rm === null || $rm === '' || $rm === '0') {
                return;
            }
            $orgIds = array_values(array_filter(array_map('intval', (array) $this->input('organization_ids', []))));
            if ($orgIds === []) {
                return;
            }
            $manager = User::query()->find((int) $rm);
            if (! $manager) {
                return;
            }
            if (! $manager->organizations()->whereIn('organizations.id', $orgIds)->exists()) {
                $validator->errors()->add(
                    'reporting_manager_id',
                    'The reporting manager must belong to one of the selected organizations.'
                );
            }

            $roleIds = array_values(array_filter(array_map('intval', (array) $this->input('roles', []))));
            if ($roleIds !== [] && $orgIds === []) {
                $validator->errors()->add(
                    'roles',
                    'Select at least one organization before assigning roles.'
                );
            }
            if ($roleIds !== [] && $orgIds !== []) {
                $invalid = Role::query()
                    ->whereIn('id', $roleIds)
                    ->where(function ($q) use ($orgIds) {
                        $q->whereNull('organization_id')->orWhereNotIn('organization_id', $orgIds);
                    })
                    ->exists();
                if ($invalid) {
                    $validator->errors()->add(
                        'roles',
                        'Each selected role must belong to one of the selected organizations.'
                    );
                }
            }
        });
    }

    protected function prepareForValidation()
    {
        $user = $this->user();
        if ($user && ! $user->isSystemUser()) {
            $this->merge([
                'user_type' => 'user',
            ]);
        }

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

    if ($this->filled('designation_id')) {
        $this->merge([
            'designation_id' => (int) $this->input('designation_id'),
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

    if ($this->has('user_type')) {
        $this->merge([
            'user_type' => strtolower(trim((string) $this->input('user_type'))),
        ]);
    }

        if ($user && ! $user->isSystemUser()) {
            $oid = CurrentOrganization::idForUserAssignment();
            if ($oid === null) {
                throw ValidationException::withMessages([
                    'organization_ids' => 'Select an organization in the header before saving.',
                ]);
            }
            $this->merge([
                'organization_ids' => [$oid],
            ]);
        }
    }
}

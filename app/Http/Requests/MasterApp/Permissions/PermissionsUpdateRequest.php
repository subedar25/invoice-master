<?php
namespace App\Http\Requests\MasterApp\Permissions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
class PermissionsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

     public function rules(): array
    {
        $permissionRoute = $this->route('permission') ?? $this->route('id');
        $permissionId = is_object($permissionRoute) ? $permissionRoute->id : $permissionRoute;
        
        return [
            // Use the Rule facade for better readability
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')
                    ->ignore($permissionId, 'id')
                    ->where(function ($query) {
                        return $query->where('guard_name', $this->input('guard_name', 'web'));
                    }),
            ],
            'display_name' => 'nullable|string|max:255',
            'module_id' => 'required|exists:modules,id',
            
            // Add validation for the fields prepared in prepareForValidation()
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'slug')
                    ->ignore($permissionId, 'id'),
            ],
            'guard_name' => 'required|string|in:web,api', // Example: ensure guard_name is either 'web' or 'api'
            'is_active' => 'nullable|boolean',
            'type' => 'required|string|in:system,public',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'slug' => Str::slug($this->name),
            'guard_name' => $this->guard_name ?? 'web',
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : null,
            'type' => $this->input('type', 'public'),
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

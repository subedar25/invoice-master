<?php

namespace App\Http\Requests\MasterApp\Roles;

use App\Models\Permission;
use App\Support\CurrentOrganization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RolesStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $scopes = $this->input('invoice_department_scopes', []);
        foreach (['list-invoices', 'approve-invoice'] as $key) {
            if (! isset($scopes[$key])) {
                continue;
            }
            if ($key === 'list-invoices') {
                $mode = (string) ($scopes[$key]['scope_mode'] ?? '');
                if ($mode === 'reporting') {
                    $scopes[$key]['reporting_only'] = true;
                    $scopes[$key]['own_invoices'] = false;
                    $scopes[$key]['all_departments'] = false;
                } elseif ($mode === 'own') {
                    $scopes[$key]['reporting_only'] = false;
                    $scopes[$key]['own_invoices'] = true;
                    $scopes[$key]['all_departments'] = false;
                } elseif ($mode === 'selected') {
                    $scopes[$key]['reporting_only'] = false;
                    $scopes[$key]['own_invoices'] = false;
                    $scopes[$key]['all_departments'] = false;
                } else {
                    $scopes[$key]['reporting_only'] = false;
                    $scopes[$key]['own_invoices'] = false;
                    $scopes[$key]['all_departments'] = true;
                }
                unset($scopes[$key]['scope_mode']);
            } else {
                $mode = (string) ($scopes[$key]['scope_mode'] ?? '');
                if ($mode === 'reporting') {
                    $scopes[$key]['reporting_only'] = true;
                    $scopes[$key]['all_departments'] = false;
                } elseif ($mode === 'selected') {
                    $scopes[$key]['reporting_only'] = false;
                    $scopes[$key]['all_departments'] = false;
                } else {
                    $scopes[$key]['reporting_only'] = false;
                    $scopes[$key]['all_departments'] = true;
                }
                unset($scopes[$key]['scope_mode']);
            }
        }
        $this->merge(['invoice_department_scopes' => $scopes]);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // return auth()->user()->can('update-roles');
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $assignable = Permission::assignablePermissionIdsFor(auth()->user());

        $deptExists = Rule::exists('departments', 'id')->where(function ($query) {
            $orgId = CurrentOrganization::id();
            if ($orgId === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('organization_id', $orgId);
        });

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where(function ($query) {
                    $orgId = CurrentOrganization::id();
                    if ($orgId === null) {
                        return $query->whereRaw('1 = 0');
                    }

                    return $query->where('guard_name', 'web')->where('organization_id', $orgId);
                }),
            ],
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where(function ($query) {
                    $orgId = CurrentOrganization::id();
                    if ($orgId === null) {
                        return $query->whereRaw('1 = 0');
                    }

                    return $query->where('organization_id', $orgId);
                }),
            ],
            'is_active' => ['nullable', 'boolean'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['integer', Rule::in($assignable)],
            'invoice_department_scopes' => ['nullable', 'array'],
            'invoice_department_scopes.list-invoices' => ['nullable', 'array'],
            'invoice_department_scopes.list-invoices.all_departments' => ['nullable', 'boolean'],
            'invoice_department_scopes.list-invoices.own_invoices' => ['nullable', 'boolean'],
            'invoice_department_scopes.list-invoices.reporting_only' => ['nullable', 'boolean'],
            'invoice_department_scopes.list-invoices.department_ids' => ['nullable', 'array'],
            'invoice_department_scopes.list-invoices.department_ids.*' => ['integer', $deptExists],
            'invoice_department_scopes.approve-invoice' => ['nullable', 'array'],
            'invoice_department_scopes.approve-invoice.all_departments' => ['nullable', 'boolean'],
            'invoice_department_scopes.approve-invoice.reporting_only' => ['nullable', 'boolean'],
            'invoice_department_scopes.approve-invoice.department_ids' => ['nullable', 'array'],
            'invoice_department_scopes.approve-invoice.department_ids.*' => ['integer', $deptExists],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator): void {
            $permissions = array_map('intval', $this->input('permissions', []));
            $listId = (int) (Permission::query()->where('name', 'list-invoices')->where('guard_name', 'web')->value('id') ?? 0);
            $approveId = (int) (Permission::query()->where('name', 'approve-invoice')->where('guard_name', 'web')->value('id') ?? 0);

            $scopes = $this->input('invoice_department_scopes', []);

            if ($listId && in_array($listId, $permissions, true)) {
                $list = $scopes['list-invoices'] ?? [];
                $all = (bool) ($list['all_departments'] ?? true);
                $own = (bool) ($list['own_invoices'] ?? false);
                $reporting = (bool) ($list['reporting_only'] ?? false);
                $depts = array_filter(array_map('intval', $list['department_ids'] ?? []));
                if (! $all && ! $own && ! $reporting && $depts === []) {
                    $validator->errors()->add(
                        'invoice_department_scopes.list-invoices',
                        __('Choose Reporting Only, Own Invoices, All departments, or select at least one department for View Invoices.')
                    );
                }
            }

            if ($approveId && in_array($approveId, $permissions, true)) {
                $ap = $scopes['approve-invoice'] ?? [];
                $all = (bool) ($ap['all_departments'] ?? true);
                $reporting = (bool) ($ap['reporting_only'] ?? false);
                $depts = array_filter(array_map('intval', $ap['department_ids'] ?? []));
                if (! $all && ! $reporting && $depts === []) {
                    $validator->errors()->add(
                        'invoice_department_scopes.approve-invoice',
                        __('Choose Reporting Only, All departments, or select at least one department for Approve Invoice.')
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'permissions.required' => 'At least select 1 permission.',
            'permissions.min' => 'At least select 1 permission.',
        ];
    }
}

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
                } elseif ($mode === 'reporting_with_subordinate') {
                    $scopes[$key]['reporting_only'] = true;
                    $scopes[$key]['own_invoices'] = true;
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
                    $scopes[$key]['own_invoices'] = false;
                    $scopes[$key]['all_departments'] = false;
                } elseif ($mode === 'reporting_with_subordinate') {
                    $scopes[$key]['reporting_only'] = true;
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
            }

            if ($key === 'list-invoices') {
                $allowedStatuses = ['pending', 'in_process', 'approve', 'complete'];
                $statuses = isset($scopes[$key]['statuses']) && is_array($scopes[$key]['statuses'])
                    ? array_values(array_unique(array_filter(array_map(
                        static fn ($s) => strtolower(trim((string) $s)),
                        $scopes[$key]['statuses']
                    ))))
                    : [];
                $statuses = array_values(array_intersect($allowedStatuses, $statuses));
                $scopes[$key]['statuses'] = $statuses === [] ? $allowedStatuses : $statuses;
            }
        }
        $this->merge(['invoice_department_scopes' => $scopes]);

        $userScopes = $this->input('user_department_scopes', []);
        if (isset($userScopes['list-users'])) {
            $mode = (string) ($userScopes['list-users']['scope_mode'] ?? '');
            if ($mode === 'reporting_only') {
                $userScopes['list-users']['reporting_only'] = true;
                $userScopes['list-users']['all_departments'] = false;
                $userScopes['list-users']['own_invoices'] = false;
            } elseif ($mode === 'reporting_with_subordinate') {
                $userScopes['list-users']['reporting_only'] = true;
                $userScopes['list-users']['all_departments'] = false;
                $userScopes['list-users']['own_invoices'] = true;
            } elseif ($mode === 'selected') {
                $userScopes['list-users']['reporting_only'] = false;
                $userScopes['list-users']['own_invoices'] = false;
                $userScopes['list-users']['all_departments'] = false;
            } else {
                $userScopes['list-users']['reporting_only'] = false;
                $userScopes['list-users']['own_invoices'] = false;
                $userScopes['list-users']['all_departments'] = true;
            }
            $roleScopeMode = (string) ($userScopes['list-users']['role_scope_mode'] ?? 'all_roles');
            if ($roleScopeMode === 'selected_roles') {
                $ids = isset($userScopes['list-users']['role_ids']) && is_array($userScopes['list-users']['role_ids'])
                    ? array_values(array_unique(array_filter(array_map('intval', $userScopes['list-users']['role_ids']))))
                    : [];
                $userScopes['list-users']['role_ids'] = $ids;
            } else {
                $userScopes['list-users']['role_ids'] = [];
            }
            unset($userScopes['list-users']['role_scope_mode']);
            unset($userScopes['list-users']['scope_mode']);
        }
        $this->merge(['user_department_scopes' => $userScopes]);
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
            'invoice_department_scopes.list-invoices.statuses' => ['nullable', 'array'],
            'invoice_department_scopes.list-invoices.statuses.*' => ['string', Rule::in(['pending', 'in_process', 'approve', 'complete'])],
            'invoice_department_scopes.approve-invoice' => ['nullable', 'array'],
            'invoice_department_scopes.approve-invoice.all_departments' => ['nullable', 'boolean'],
            'invoice_department_scopes.approve-invoice.own_invoices' => ['nullable', 'boolean'],
            'invoice_department_scopes.approve-invoice.reporting_only' => ['nullable', 'boolean'],
            'invoice_department_scopes.approve-invoice.department_ids' => ['nullable', 'array'],
            'invoice_department_scopes.approve-invoice.department_ids.*' => ['integer', $deptExists],
            'user_department_scopes' => ['nullable', 'array'],
            'user_department_scopes.list-users' => ['nullable', 'array'],
            'user_department_scopes.list-users.all_departments' => ['nullable', 'boolean'],
            'user_department_scopes.list-users.own_invoices' => ['nullable', 'boolean'],
            'user_department_scopes.list-users.reporting_only' => ['nullable', 'boolean'],
            'user_department_scopes.list-users.department_ids' => ['nullable', 'array'],
            'user_department_scopes.list-users.department_ids.*' => ['integer', $deptExists],
            'user_department_scopes.list-users.role_ids' => ['nullable', 'array'],
            'user_department_scopes.list-users.role_ids.*' => ['integer', Rule::exists('roles', 'id')->where(function ($query) {
                $orgId = CurrentOrganization::id();
                if ($orgId === null) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where('organization_id', $orgId);
            })],
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
                        __('Choose Reporting Only, View reportee and subordinates, Own Invoices, All departments, or select at least one department for View Invoices.')
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
                        __('Choose Reporting Only, View reportee and subordinates, All departments, or select at least one department for Approve Invoice.')
                    );
                }
            }

            $listUsersId = (int) (Permission::query()->where('name', 'list-users')->where('guard_name', 'web')->value('id') ?? 0);
            if ($listUsersId && in_array($listUsersId, $permissions, true)) {
                $usr = $this->input('user_department_scopes.list-users', []);
                $all = (bool) ($usr['all_departments'] ?? true);
                $reporting = (bool) ($usr['reporting_only'] ?? false);
                $depts = array_filter(array_map('intval', $usr['department_ids'] ?? []));
                if (! $all && ! $reporting && $depts === []) {
                    $validator->errors()->add(
                        'user_department_scopes.list-users',
                        __('Choose reporting mode, all departments, or select at least one department for View Users.')
                    );
                }
                $roleIds = array_filter(array_map('intval', $usr['role_ids'] ?? []));
                $roleScopeMode = (string) $this->input('user_department_scopes.list-users.role_scope_mode', 'all_roles');
                if ($roleScopeMode === 'selected_roles' && $roleIds === []) {
                    $validator->errors()->add(
                        'user_department_scopes.list-users',
                        __('Choose at least one role when "View selected user roles" is selected.')
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

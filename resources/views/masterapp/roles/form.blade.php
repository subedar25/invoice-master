<div class="form-group">
    <label for="name">Role Name <span class="text-danger">*</span></label>
    <input type="text" id="name" name="name" value="{{ isset($role) ? $role->name : '' }}" class="form-control" placeholder="Enter role name" required>
</div>

<div class="form-group">
    <label for="department_id">Department</label>
    <select id="department_id" name="department_id" class="form-control">
        <option value="" @if(!isset($role) || empty($role->department_id)) selected @endif>Choose a department...</option>
        @if(isset($departments))
            @foreach($departments as $id => $name)
                <option value="{{ $id }}" @if(isset($role) && $role->department_id == $id) selected @endif>
                    {{ $name }}
                </option>
            @endforeach
        @endif
    </select>
</div>

<div class="form-group">
    <label for="is_active">Active Status</label>
    <select id="is_active" name="is_active" class="form-control">
        <option value="1" @if(!isset($role) || (isset($role) && $role->is_active)) selected @endif>Active</option>
        <option value="0" @if(isset($role) && !$role->is_active) selected @endif>Inactive</option>
    </select>
</div>

<div class="mb-2">
    <strong>Assign Permissions</strong>
</div>

@php
    $allDepartmentsForInvoiceScope = $allDepartmentsForInvoiceScope ?? collect();
    $invoiceDepartmentScopes = $invoiceDepartmentScopes ?? [
        'list-invoices' => ['all_departments' => true, 'department_ids' => []],
        'approve-invoice' => ['all_departments' => true, 'department_ids' => []],
    ];
    $listInvoicesPermissionId = $listInvoicesPermissionId ?? null;
    $approveInvoicePermissionId = $approveInvoicePermissionId ?? null;
@endphp

<div id="permissions-container" class="row border rounded p-0" style="border: 1px solid #ced4da !important; border-radius: 8px; min-height: 400px;">
    @if(isset($groupedPermissions))
        {{-- Left panel: list of modules (departments) --}}
        <div class="col-md-5 col-lg-5 border-right bg-light p-0" style="border-right: 1px solid #ced4da !important;">
            <div class="list-group list-group-flush" id="permissions-module-list" style="max-height: 400px; overflow-y: auto;">
                @foreach($groupedPermissions as $moduleName => $modulePermissions)
                    @php $first = $modulePermissions->first(); $moduleId = $first ? $first->module_id : 0; @endphp
                    <a class="list-group-item list-group-item-action permissions-module-btn py-1 px-2 d-flex align-items-center {{ $loop->first ? 'active' : '' }}"
                       href="#"
                       data-module-id="{{ $moduleId }}">
                        <span class="module-tick mr-2 text-muted" data-module-id="{{ $moduleId }}" title="Has selected permissions" style="font-size: 0.75rem;"><i class="fa fa-check"></i></span>
                        <span>{{ $moduleName }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Right panel: permission checkboxes for selected module (single column) --}}
        <div class="col-md-7 col-lg-7 p-3" style="max-height: 400px; overflow-y: auto;">
            @foreach($groupedPermissions as $moduleName => $modulePermissions)
                @php $first = $modulePermissions->first(); $moduleId = $first ? $first->module_id : 0; @endphp
                <div class="permissions-module-panel {{ $loop->first ? '' : 'd-none' }}" id="panel-module-{{ $moduleId }}" data-module-id="{{ $moduleId }}">
                    <div class="custom-control custom-checkbox mb-2 py-2 pl-3 pr-2 mb-3">
                        <input class="custom-control-input parent" name="module[]" id="parent{{ $moduleId }}" value="{{ $moduleId }}" type="checkbox" onchange="selectAll(this);">
                        <label class="custom-control-label font-weight-bold" for="parent{{ $moduleId }}">{{ $moduleName }} (Select All)</label>
                        <input name="moduleid[]" value="{{ $moduleId }}" type="hidden">
                    </div>
                    <div class="row">
                        @foreach($modulePermissions as $permission)
                        <div class="col-12 mb-1">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input permission-checkbox child"
                                    onchange="checkAllBox();"
                                    data-parent="parent{{ $moduleId }}"
                                    data-module-id="{{ $moduleId }}"
                                    data-permission-name="{{ $permission->name }}"
                                    type="checkbox"
                                    name="permissions[]"
                                    id="permission_{{ $permission->id }}"
                                    value="{{ $permission->id }}"
                                    @if(isset($role) && in_array($permission->id, $rolePermissions)) checked @endif>
                                <label class="custom-control-label font-weight-normal" for="permission_{{ $permission->id }}">
                                    {{ $permission->display_name ?? $permission->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                        @if($moduleName === 'Invoice Management')
                            @once
                                @include('masterapp.roles.partials.invoice-department-scopes')
                            @endonce
                        @endif
                        @if($moduleName === 'User Management')
                            @once
                                @include('masterapp.roles.partials.user-department-scopes')
                            @endonce
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
$(function() {
    function updateModuleTicks() {
        $('.module-tick').each(function() {
            var moduleId = $(this).data('module-id');
            var anyChecked = $('.permission-checkbox.child[data-module-id="' + moduleId + '"]:checked').length > 0;
            $(this).removeClass('text-success text-muted').addClass(anyChecked ? 'text-success' : 'text-muted');
        });
    }

    $(document).on('click', '.permissions-module-btn', function(e) {
        e.preventDefault();
        var moduleId = $(this).data('module-id');
        $('.permissions-module-btn').removeClass('active');
        $(this).addClass('active');
        $('.permissions-module-panel').addClass('d-none');
        $('#panel-module-' + moduleId).removeClass('d-none');
    });

    $(document).on('change', '.permission-checkbox, .parent', function() {
        updateModuleTicks();
    });

    updateModuleTicks();
});
</script>

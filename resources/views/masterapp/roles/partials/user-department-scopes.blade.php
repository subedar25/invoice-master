@php
    $userListScope = $userDepartmentScopes['list-users'] ?? ['all_departments' => true, 'own_invoices' => false, 'reporting_only' => false, 'department_ids' => []];
    $userListDeptIds = array_map('intval', $userListScope['department_ids'] ?? []);
    $userListScopeMode = !empty($userListScope['reporting_only'] ?? false)
        ? 'reporting_with_subordinate'
        : (!empty($userListScope['own_invoices'] ?? false)
        ? 'reporting_only'
        : (!empty($userListScope['all_departments'] ?? true) ? 'all' : 'selected'));
@endphp

@if($listUsersPermissionId)
<div class="col-12 mt-3 pt-3 border-top">
    <div class="small font-weight-bold text-secondary mb-2">User Management — listing access</div>
    <p class="small text-muted mb-3">
        For <strong>View Users</strong>, choose <em>View reportee only</em>, <em>View reportee and subordinates</em>,
        <em>View all departments</em>, or <em>View selected departments</em>.
    </p>

    <div id="user-scope-panel-list" class="mb-4">
        <div class="font-weight-bold small mb-2">View Users (list)</div>
        @error('user_department_scopes.list-users')
            <div class="text-danger small mb-2">{{ $message }}</div>
        @enderror

        <div class="custom-control custom-radio mb-2">
            <input type="radio" class="custom-control-input js-user-scope-mode-list"
                id="user_scope_list_reporting"
                name="user_department_scopes[list-users][scope_mode]"
                value="reporting_only"
                @checked($userListScopeMode === 'reporting_only')>
            <label class="custom-control-label" for="user_scope_list_reporting">View reportee only</label>
        </div>
        <div class="custom-control custom-radio mb-2">
            <input type="radio" class="custom-control-input js-user-scope-mode-list"
                id="user_scope_list_reporting_sub"
                name="user_department_scopes[list-users][scope_mode]"
                value="reporting_with_subordinate"
                @checked($userListScopeMode === 'reporting_with_subordinate')>
            <label class="custom-control-label" for="user_scope_list_reporting_sub">View reportee and subordinates</label>
        </div>
        <div class="custom-control custom-radio mb-2">
            <input type="radio" class="custom-control-input js-user-scope-mode-list"
                id="user_scope_list_all"
                name="user_department_scopes[list-users][scope_mode]"
                value="all"
                @checked($userListScopeMode === 'all')>
            <label class="custom-control-label" for="user_scope_list_all">View all departments</label>
        </div>
        <div class="custom-control custom-radio mb-2">
            <input type="radio" class="custom-control-input js-user-scope-mode-list"
                id="user_scope_list_sel"
                name="user_department_scopes[list-users][scope_mode]"
                value="selected"
                @checked($userListScopeMode === 'selected')>
            <label class="custom-control-label" for="user_scope_list_sel">View selected departments</label>
        </div>
        <div id="user-scope-list-depts" class="pl-2 border-left ml-1 {{ $userListScopeMode !== 'selected' ? 'd-none' : '' }}">
            @forelse($allDepartmentsForInvoiceScope as $dept)
                <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input js-user-list-dept"
                        id="user_list_dept_{{ $dept->id }}"
                        name="user_department_scopes[list-users][department_ids][]"
                        value="{{ $dept->id }}"
                        @checked(in_array((int) $dept->id, $userListDeptIds, true))>
                    <label class="custom-control-label font-weight-normal" for="user_list_dept_{{ $dept->id }}">{{ $dept->name }}</label>
                </div>
            @empty
                <span class="text-muted small">No departments in this organization.</span>
            @endforelse
        </div>
    </div>
</div>

<script>
(function() {
    var listUsersPid = {{ (int) ($listUsersPermissionId ?? 0) }};

    function toggleUserListPanel() {
        if (!listUsersPid) return;
        var on = $('#permission_' + listUsersPid).is(':checked');
        $('#user-scope-panel-list').toggleClass('d-none', !on);
    }
    function toggleUserListDepts() {
        var mode = $('input[name="user_department_scopes[list-users][scope_mode]"]:checked').val();
        $('#user-scope-list-depts').toggleClass('d-none', mode !== 'selected');
    }

    $('#permissions-container').on('change', '.permission-checkbox', function() {
        toggleUserListPanel();
    });
    $(document).on('change', '.js-user-scope-mode-list', toggleUserListDepts);

    toggleUserListPanel();
    toggleUserListDepts();
})();
</script>
@endif

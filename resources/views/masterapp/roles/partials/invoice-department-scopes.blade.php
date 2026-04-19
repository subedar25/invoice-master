@php
    $listScope = $invoiceDepartmentScopes['list-invoices'] ?? ['all_departments' => true, 'own_invoices' => false, 'department_ids' => []];
    $approveScope = $invoiceDepartmentScopes['approve-invoice'] ?? ['all_departments' => true, 'own_invoices' => false, 'reporting_only' => false, 'department_ids' => []];
    $listDeptIds = array_map('intval', $listScope['department_ids'] ?? []);
    $approveDeptIds = array_map('intval', $approveScope['department_ids'] ?? []);
    $listScopeMode = !empty($listScope['own_invoices'] ?? false)
        ? 'own'
        : (!empty($listScope['all_departments'] ?? true) ? 'all' : 'selected');
    $approveScopeMode = !empty($approveScope['reporting_only'] ?? false)
        ? 'reporting'
        : (!empty($approveScope['all_departments'] ?? true) ? 'all' : 'selected');
@endphp

@if($listInvoicesPermissionId || $approveInvoicePermissionId)
<div class="col-12 mt-3 pt-3 border-top">
    <div class="small font-weight-bold text-secondary mb-2">Invoice — department access</div>
    <p class="small text-muted mb-3">
        For <strong>View Invoices</strong>, choose <em>Own Invoices</em>, <em>All departments</em>, or <em>Selected departments</em>.
        For <strong>Approve Invoice</strong>, choose <em>Reporting Only</em>, <em>All departments</em>, or <em>Selected departments</em>.
        If you limit departments, pick at least one department for each permission you assign.
    </p>

    @if($listInvoicesPermissionId)
    <div id="invoice-scope-panel-list" class="mb-4">
        <div class="font-weight-bold small mb-2">View Invoices (list)</div>
        @error('invoice_department_scopes.list-invoices')
            <div class="text-danger small mb-2">{{ $message }}</div>
        @enderror
        <div class="custom-control custom-radio mb-2">
            <input type="radio" class="custom-control-input js-invoice-scope-mode-list"
                id="inv_scope_list_own"
                name="invoice_department_scopes[list-invoices][scope_mode]"
                value="own"
                @checked($listScopeMode === 'own')>
            <label class="custom-control-label" for="inv_scope_list_own">Own Invoices</label>
        </div>
        <div class="custom-control custom-radio mb-2">
            <input type="radio" class="custom-control-input js-invoice-scope-mode-list"
                id="inv_scope_list_all"
                name="invoice_department_scopes[list-invoices][scope_mode]"
                value="all"
                @checked($listScopeMode === 'all')>
            <label class="custom-control-label" for="inv_scope_list_all">All departments</label>
        </div>
        <div class="custom-control custom-radio mb-2">
            <input type="radio" class="custom-control-input js-invoice-scope-mode-list"
                id="inv_scope_list_sel"
                name="invoice_department_scopes[list-invoices][scope_mode]"
                value="selected"
                @checked($listScopeMode === 'selected')>
            <label class="custom-control-label" for="inv_scope_list_sel">Selected departments only</label>
        </div>
        <div id="invoice-scope-list-depts" class="pl-2 border-left ml-1 {{ $listScopeMode !== 'selected' ? 'd-none' : '' }}">
            @forelse($allDepartmentsForInvoiceScope as $dept)
                <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input js-inv-list-dept"
                        id="inv_list_dept_{{ $dept->id }}"
                        name="invoice_department_scopes[list-invoices][department_ids][]"
                        value="{{ $dept->id }}"
                        @checked(in_array((int) $dept->id, $listDeptIds, true))>
                    <label class="custom-control-label font-weight-normal" for="inv_list_dept_{{ $dept->id }}">{{ $dept->name }}</label>
                </div>
            @empty
                <span class="text-muted small">No departments in this organization.</span>
            @endforelse
        </div>
    </div>
    @endif

    @if($approveInvoicePermissionId)
    <div id="invoice-scope-panel-approve">
        <div class="font-weight-bold small mb-2">Approve Invoice</div>
        @error('invoice_department_scopes.approve-invoice')
            <div class="text-danger small mb-2">{{ $message }}</div>
        @enderror
        <div class="custom-control custom-radio mb-2">
            <input type="radio" class="custom-control-input js-invoice-scope-mode-approve"
                id="inv_scope_apr_reporting"
                name="invoice_department_scopes[approve-invoice][scope_mode]"
                value="reporting"
                @checked($approveScopeMode === 'reporting')>
            <label class="custom-control-label" for="inv_scope_apr_reporting">Reporting Only</label>
        </div>
        <div class="custom-control custom-radio mb-2">
            <input type="radio" class="custom-control-input js-invoice-scope-mode-approve"
                id="inv_scope_apr_all"
                name="invoice_department_scopes[approve-invoice][scope_mode]"
                value="all"
                @checked($approveScopeMode === 'all')>
            <label class="custom-control-label" for="inv_scope_apr_all">All departments</label>
        </div>
        <div class="custom-control custom-radio mb-2">
            <input type="radio" class="custom-control-input js-invoice-scope-mode-approve"
                id="inv_scope_apr_sel"
                name="invoice_department_scopes[approve-invoice][scope_mode]"
                value="selected"
                @checked($approveScopeMode === 'selected')>
            <label class="custom-control-label" for="inv_scope_apr_sel">Selected departments only</label>
        </div>
        <div id="invoice-scope-approve-depts" class="pl-2 border-left ml-1 {{ $approveScopeMode !== 'selected' ? 'd-none' : '' }}">
            @forelse($allDepartmentsForInvoiceScope as $dept)
                <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input js-inv-approve-dept"
                        id="inv_apr_dept_{{ $dept->id }}"
                        name="invoice_department_scopes[approve-invoice][department_ids][]"
                        value="{{ $dept->id }}"
                        @checked(in_array((int) $dept->id, $approveDeptIds, true))>
                    <label class="custom-control-label font-weight-normal" for="inv_apr_dept_{{ $dept->id }}">{{ $dept->name }}</label>
                </div>
            @empty
                <span class="text-muted small">No departments in this organization.</span>
            @endforelse
        </div>
    </div>
    @endif
</div>

<script>
(function() {
    var listPid = {{ (int) ($listInvoicesPermissionId ?? 0) }};
    var apprPid = {{ (int) ($approveInvoicePermissionId ?? 0) }};

    function toggleListPanel() {
        if (!listPid) return;
        var on = $('#permission_' + listPid).is(':checked');
        $('#invoice-scope-panel-list').toggleClass('d-none', !on);
    }
    function toggleApprovePanel() {
        if (!apprPid) return;
        var on = $('#permission_' + apprPid).is(':checked');
        $('#invoice-scope-panel-approve').toggleClass('d-none', !on);
    }
    function toggleListDepts() {
        var mode = $('input[name="invoice_department_scopes[list-invoices][scope_mode]"]:checked').val();
        $('#invoice-scope-list-depts').toggleClass('d-none', mode !== 'selected');
    }
    function toggleApproveDepts() {
        var mode = $('input[name="invoice_department_scopes[approve-invoice][scope_mode]"]:checked').val();
        $('#invoice-scope-approve-depts').toggleClass('d-none', mode !== 'selected');
    }

    $('#permissions-container').on('change', '.permission-checkbox', function() {
        toggleListPanel();
        toggleApprovePanel();
    });
    $(document).on('change', '.js-invoice-scope-mode-list', toggleListDepts);
    $(document).on('change', '.js-invoice-scope-mode-approve', toggleApproveDepts);

    toggleListPanel();
    toggleApprovePanel();
    toggleListDepts();
    toggleApproveDepts();
})();
</script>
@endif

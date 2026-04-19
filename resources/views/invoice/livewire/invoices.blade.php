<div wire:key="invoice-module" id="master-list">
@push('styles')
<link rel="stylesheet" href="{{ asset('dark_theam/invoice-livewire-invoices.css') }}">
@endpush
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @php
            $statusFilterLabels = [
                'approve' => 'Approve',
                'pending' => 'Pending',
                'in_process' => 'In Process',
                'complete' => 'Complete',
            ];
            $datePresetLabels = [
                'current_month' => 'Current month',
                'last_month' => 'Last month',
                'last_3_months' => 'Last 3 months',
                'last_6_months' => 'Last 6 months',
                'last_12_months' => 'Last one year',
                'custom' => 'Custom range',
            ];
            $hasInvoiceActiveFilters = trim($search) !== ''
                || count($filterStatuses) > 0
                || count($filterDepartmentIds) > 0
                || $invoiceDateFilterPreset !== 'last_3_months';
        @endphp

        @if(!$organization_id)
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Invoices</h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="alert alert-warning border-0 shadow-sm mb-0">
                    <h5 class="alert-heading mb-2"><i class="fa fa-building mr-1"></i> No organization selected</h5>
                    <p class="mb-0">Choose an organization using the switcher in the sidebar (or top bar) to load invoices for that organization.</p>
                </div>
            </div>
        </section>
        @else
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Invoices</h1>
                    </div>
                    <div class="col-sm-6 d-flex justify-content-end">
                        <button type="button" class="btn btn-default mr-2" wire:click="$toggle('invoiceFiltersOpen')">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        @can('create-invoice')
                        @if($organization_id)
                        <button type="button" class="btn btn-primary" style="min-width:150px;" wire:click="openCreateModal">
                            <i class="fa fa-plus mr-1"></i> Create Invoice
                        </button>
                        @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @if($invoiceFiltersOpen)
                    <div class="filter-wrapper" id="invoiceFilterWrapper" wire:key="invoice-filter-panel">
                        <a href="#" class="close-filter-btn" wire:click.prevent="$set('invoiceFiltersOpen', false)" title="Close">&times;</a>
                        <form wire:submit.prevent>
                            <div class="row align-items-end mb-3">
                                <div class="col-md-6 col-lg-4">
                                    <label class="font-weight-bold">Date range</label>
                                    <select wire:model.live="invoiceDateFilterPreset" class="form-control filter-input" aria-label="Invoice date range preset">
                                        @foreach($datePresetLabels as $presetValue => $presetLabel)
                                            <option value="{{ $presetValue }}">{{ $presetLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($invoiceDateFilterPreset === 'custom')
                                    <div class="col-md-3 col-lg-4">
                                        <label class="font-weight-bold">From</label>
                                        <input type="date" wire:model.live="dateFrom" class="form-control filter-input" aria-label="From date">
                                    </div>
                                    <div class="col-md-3 col-lg-4">
                                        <label class="font-weight-bold">To</label>
                                        <input type="date" wire:model.live="dateTo" class="form-control filter-input" aria-label="To date">
                                    </div>
                                @endif
                            </div>
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label class="font-weight-bold">Status</label>
                                    <select
                                        id="invoice_filter_status"
                                        class="form-control filter-input w-100 select2 select2-invoice-filter"
                                        multiple
                                        data-placeholder="All statuses"
                                    >
                                        <option value="approve" @selected(in_array('approve', $filterStatuses, true))>Approve</option>
                                        <option value="pending" @selected(in_array('pending', $filterStatuses, true))>Pending</option>
                                        <option value="in_process" @selected(in_array('in_process', $filterStatuses, true))>In Process</option>
                                        <option value="complete" @selected(in_array('complete', $filterStatuses, true))>Complete</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="font-weight-bold">Department</label>
                                    <select
                                        id="invoice_filter_department"
                                        class="form-control filter-input w-100 select2 select2-invoice-filter"
                                        multiple
                                        data-placeholder="All departments"
                                    >
                                        @foreach($filterDepartments as $department)
                                            <option value="{{ $department->id }}" @selected(in_array((int) $department->id, array_map('intval', (array) $filterDepartmentIds), true))>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

                @if($hasInvoiceActiveFilters)
                    <div id="invoiceActiveFilters" class="mb-3" wire:key="invoice-active-filters">
                        <span class="active-filters-heading">Active filters</span>
                        <span class="users-active-filter-chips d-flex flex-wrap align-items-center">
                            @if(trim($search) !== '')
                                <span class="users-active-filter-chip badge badge-info mr-1 mb-1" title="{{ $search }}">
                                    {{ \Illuminate\Support\Str::limit($search, 40) }}
                                    <i class="fa fa-times remove-filter-chip" wire:click="clearInvoiceSearch" role="button" tabindex="0" title="Remove"></i>
                                </span>
                            @endif
                            @foreach($filterStatuses as $st)
                                <span class="users-active-filter-chip badge badge-info mr-1 mb-1" wire:key="inv-fs-{{ $st }}">
                                    {{ $statusFilterLabels[$st] ?? $st }}
                                    <i class="fa fa-times remove-filter-chip" wire:click="removeInvoiceFilterStatus('{{ $st }}')" role="button" tabindex="0" title="Remove"></i>
                                </span>
                            @endforeach
                            @foreach($filterDepartmentIds as $did)
                                @php $did = (int) $did; $deptRow = $filterDepartments->firstWhere('id', $did); @endphp
                                <span class="users-active-filter-chip badge badge-info mr-1 mb-1" wire:key="inv-fd-{{ $did }}">
                                    {{ optional($deptRow)->name ?? ('#'.$did) }}
                                    <i class="fa fa-times remove-filter-chip" wire:click="removeInvoiceFilterDepartment({{ $did }})" role="button" tabindex="0" title="Remove"></i>
                                </span>
                            @endforeach
                            @if($invoiceDateFilterPreset !== 'last_3_months')
                                <span class="users-active-filter-chip badge badge-secondary mr-1 mb-1" wire:key="inv-date-preset">
                                    {{ $datePresetLabels[$invoiceDateFilterPreset] ?? $invoiceDateFilterPreset }}
                                    ({{ $invoicePeriodStart->format('M j, Y') }} – {{ $invoicePeriodEnd->format('M j, Y') }})
                                    <i class="fa fa-times remove-filter-chip" wire:click="resetInvoiceDateFilterToDefault" role="button" tabindex="0" title="Reset to last 3 months"></i>
                                </span>
                            @endif
                        </span>
                    </div>
                @endif

                <div class="card">
                    {{-- Server-side pagination via Livewire: do not use js-master-datatable (DataTables would add a second pager and break layout). --}}
                    <div class="card-body p-0">
                        <div class="px-3 pt-3 pb-2 bg-light border-bottom small text-muted">
                            <i class="fa fa-calendar-alt mr-1" aria-hidden="true"></i>
                            Created
                            <strong class="text-dark">{{ $invoicePeriodStart->format('M j, Y') }}</strong>
                            –
                            <strong class="text-dark">{{ $invoicePeriodEnd->format('M j, Y') }}</strong>
                            <span class="badge badge-light text-dark border ml-1">{{ $datePresetLabels[$invoiceDateFilterPreset] ?? $invoiceDateFilterPreset }}</span>
                        </div>
                        <div id="invoiceTableToolbar" class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 pt-3 pb-2 bg-light border-bottom">
                            <div id="invoiceDtButtonsWrap" class="d-flex flex-wrap align-items-center"></div>
                            <div class="search-input-wrapper flex-grow-1" style="max-width: 28rem; min-width: 12rem;">
                                <i class="fa fa-search"></i>
                                <input type="search" class="form-control search-input" placeholder="Search invoice, brand, party…" wire:model.live.debounce.300ms="search" autocomplete="off" aria-label="Search invoices">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                id="invoiceTable"
                                class="table table-bordered table-hover table-sm mb-0"
                            >
                                <thead class="thead-light">
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Brand Name</th>
                                        <th>Party Name</th>
                                        <th>Amount</th>
                                        <th class="text-right">Pending amount</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Department</th>
                                        <th>Created</th>
                                        <th class="master-table-actions no-vis">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        @php
                                            $invoicePending = max(0, (float) $invoice->total_amount - (float) ($invoice->paid_amount ?? 0));
                                        @endphp
                                        <tr wire:key="inv-row-{{ $invoice->id }}">
                                            <td>{{ $invoice->invoice_number }}</td>
                                            <td>{{ $invoice->outlet?->name ?? 'N/A' }}</td>
                                            <td>{{ $invoice->vendor?->name ?? 'N/A' }}</td>
                                            <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                            <td class="text-right font-weight-bold">{{ number_format($invoicePending, 2) }}</td>
                                            <td>
                                                @php
                                                    $priority = ucfirst(strtolower((string) ($invoice->priority ?? 'Medium')));
                                                    $priorityBadgeClass = match ($priority) {
                                                        'High' => 'badge-danger',
                                                        'Low' => 'badge-secondary',
                                                        default => 'badge-warning',
                                                    };
                                                @endphp
                                                <span class="badge {{ $priorityBadgeClass }}">{{ $priority }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $rawStatus = strtolower(trim((string) ($invoice->status ?? '')));
                                                    $statusLabel = match ($rawStatus) {
                                                        'approve', 'approved' => 'Approve',
                                                        'in process', 'in_process', 'processing' => 'In Process',
                                                        'complete', 'completed' => 'Complete',
                                                        default => 'Pending',
                                                    };
                                                @endphp
                                                <span class="badge badge-info">{{ $statusLabel }}</span>
                                            </td>
                                            <td>{{ $invoice->department?->name ?? 'N/A' }}</td>
                                            <td>{{ optional($invoice->created_at)->format('Y-m-d') ?? 'N/A' }}</td>
                                            <td>
                                                <div class="action-div master-actions">
                                                    @can('list-invoices')
                                                    <a href="{{ route('invoice.pdf', $invoice->id) }}" class="action-icon text-danger" target="_blank" rel="noopener noreferrer" title="Preview PDF"><i class="fas fa-file-pdf"></i></a>
                                                    <a href="#" wire:click.prevent="openViewModal({{ $invoice->id }})" class="action-icon"><i class="fa fa-eye"></i></a>
                                                    <a href="#" wire:click.prevent="openPaymentHistoryModal({{ $invoice->id }})" class="action-icon text-info" title="Payment history"><i class="fas fa-history"></i></a>
                                                    @endcan
                                                    @can('edit-invoice')
                                                    @if($invoicePending > 0)
                                                    <a href="#" wire:click.prevent="openPaymentModal({{ $invoice->id }})" class="action-icon text-success" title="Record payment"><i class="fas fa-money-check-alt"></i></a>
                                                    @endif
                                                    <a href="#" wire:click.prevent="openEditModal({{ $invoice->id }})" class="action-icon"><i class="fa fa-edit"></i></a>
                                                    @endcan
                                                    @can('approve-invoice')
                                                    @if($this->canApproveRow($invoice))
                                                    <a href="#" wire:click.prevent="openApproveModal({{ $invoice->id }})" class="action-icon text-primary" title="Approve invoice"><i class="fas fa-check-circle"></i></a>
                                                    @endif
                                                    @endcan
                                                    @can('delete-invoice')
                                                    <a href="#" data-master-delete-id="{{ $invoice->id }}" class="action-icon master-delete-link"><i class="fa fa-trash"></i></a>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer py-3 clearfix border-top">
                        <div class="show_page_align invoice-livewire-pagination">
                            <div class="dataTables_info">
                                @if($invoices->total() > 0)
                                    Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }}
                                @else
                                    Nothing to show
                                @endif
                            </div>
                            <div class="length_pagination d-flex flex-wrap align-items-center">
                                <div class="dataTables_length">
                                    <label class="mb-0">
                                        Show
                                        <select id="invoicePerPage" wire:model.live="perPage" class="form-control form-control-sm d-inline-block" style="width: auto; min-width: 4.5rem;" name="invoice_per_page" aria-label="Rows per page">
                                            @foreach([10, 15, 25, 50, 100] as $n)
                                                <option value="{{ $n }}">{{ $n }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>
                                <div class="dataTables_paginate paging_simple_numbers pagination-links">
                                    {{ $invoices->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif
    @endif

    @if($showCreateModal || $showEditModal)
        @component('masterapp.livewire.masters.components.form-card', [
            'formTitleAdd' => 'Create Invoice',
            'formTitleEdit' => $showEditModal && $invoice_number ? "Edit Invoice ($invoice_number)" : 'Edit Invoice',
            'showEditModal' => $showEditModal,
            'backAction' => 'closeModals',
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}">
                <div class="row">
                    <div class="col-12 col-md-6 form-group">
                        <label>Brand Name *</label>
                        <div class="input-group">
                            <select class="form-control @error('outlet_id') is-invalid @enderror" wire:model="outlet_id">
                                <option value="">Select Brand Name</option>
                                @foreach($outlets as $out) <option value="{{ $out->id }}">{{ $out->name }}</option> @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" wire:click="openAddOutletModal">+</button>
                            </div>
                        </div>
                        @error('outlet_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-12 col-md-6 form-group">
                        <label>Party Name *</label>
                        <div class="input-group">
                            <select class="form-control @error('vendor_id') is-invalid @enderror" wire:model="vendor_id">
                                <option value="">Select Party</option>
                                @foreach($vendors as $vendor) <option value="{{ $vendor->id }}">{{ $vendor->name }}</option> @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" wire:click="openAddVendorModal">+</button>
                            </div>
                        </div>
                        @error('vendor_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-{{ $editId ? '4' : '6' }} form-group">
                        <label>Department</label>
                        <select class="form-control @error('department_id') is-invalid @enderror" wire:model.live="department_id">
                            <option value="">Select Department</option>
                            @foreach($departments as $dep) <option value="{{ $dep->id }}">{{ $dep->name }}</option> @endforeach
                        </select>
                        @error('department_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-{{ $editId ? '4' : '6' }} form-group">
                        <label>Priority</label>
                        <select class="form-control @error('priority') is-invalid @enderror" wire:model="priority">
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                        @error('priority') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    @if($editId)
                    <div class="col-md-4 form-group">
                        <label>Status</label>
                        <select class="form-control" wire:model.live="status" wire:key="invoice-status-{{ $department_id }}-{{ $editId }}">
                        <option value="Pending">Pending</option>
                            @if($this->canApproveInForm())
                            <option value="Approve">Approve</option>
                            @endif
                            <option value="in_process">In Process</option>
                            <option value="Complete">Complete</option>
                        </select>
                    </div>
                    @endif
                    <div class="col-md-12 form-group">
                        <label>Task</label>
                        <textarea class="form-control" rows="3" wire:model="description"></textarea>
                    </div>
                </div>

                <h6>Line Items</h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>HSN</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>CGST (%)</th>
                            <th>SGST (%)</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice_items as $index => $item)
                            <tr>
                                <td>
                                    <div class="position-relative">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control bg-white @error('invoice_items.'.$index.'.product_desciption') is-invalid @enderror" style="cursor:pointer;" placeholder="Select Product..."
                                                readonly
                                                wire:click="$set('invoice_items.{{ $index }}.show_dropdown', true)"
                                                value="{{ $invoice_items[$index]['product_desciption'] ?? '' }}">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary" wire:click="openAddProductModal({{ $index }})">+</button>
                                            </div>
                                        </div>

                                        @if($invoice_items[$index]['show_dropdown'] ?? false)
                                            <!-- Transparent Backdrop to close overlay exactly like standard dropdowns -->
                                            <div wire:click="$set('invoice_items.{{ $index }}.show_dropdown', false)" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 998; cursor: default;"></div>
                                            
                                            <!-- The Explicit Searchable Dropdown Overlay -->
                                            <div class="dropdown-menu show w-100 shadow p-0" style="position: absolute; top: 100%; z-index: 1000; max-height: 250px; overflow-y: auto;">
                                                <div class="p-2 bg-light border-bottom position-sticky" style="top: 0; z-index: 1001;">
                                                    <!-- Integrated Search Box -->
                                                    <input type="text" class="form-control form-control-sm" placeholder="Search..." wire:model.live="invoice_items.{{ $index }}.search_query">
                                                </div>
                                                <div class="py-1">
                                                    @php
                                                        $query = $invoice_items[$index]['search_query'] ?? '';
                                                        $filtered = $query ? $products->filter(fn($p) => stripos($p->name, $query) !== false) : $products;
                                                    @endphp
                                                    @forelse($filtered as $prod)
                                                        <a class="dropdown-item" href="javascript:void(0)" wire:click="selectProduct({{ $index }}, {{ $prod->id }})">
                                                            {{ $prod->name }}
                                                        </a>
                                                    @empty
                                                        <span class="dropdown-item text-muted small">No match found.</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td><input type="text" class="form-control form-control-sm" wire:model.live="invoice_items.{{ $index }}.hsn"></td>
                                <td><input type="number" class="form-control form-control-sm @error('invoice_items.'.$index.'.quantity') is-invalid @enderror" style="width:70px" wire:model.live="invoice_items.{{ $index }}.quantity"></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm @error('invoice_items.'.$index.'.unit_price') is-invalid @enderror" style="width:90px" wire:model.live="invoice_items.{{ $index }}.unit_price"></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm" style="width:70px" wire:model.live="invoice_items.{{ $index }}.cgst"></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm" style="width:70px" wire:model.live="invoice_items.{{ $index }}.sgst"></td>
                                <td><span class="form-control form-control-sm bg-light" style="width:100px">{{ $invoice_items[$index]['total_price'] ?? '0.00' }}</span></td>
                                <td><button type="button" class="btn btn-danger btn-sm" wire:click="removeLineItem({{ $index }})">&times;</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="addLineItem">Add Item</button>
                    </div>
                    <div class="text-right" style="width: 300px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <div class="d-flex justify-content-between">
                            <strong>Gross Total:</strong>
                            <span>{{ $gross_total ?? '0.00' }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-2 mt-2">
                            <strong>Tax (Total GST):</strong>
                            <span>{{ $tax_total ?? '0.00' }}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2">
                            <strong>Amount:</strong>
                            <strong>{{ $total_amount ?? '0.00' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card invoice-supporting-files-card border shadow-sm mb-3">
                    <div class="card-header invoice-supporting-files-header d-flex align-items-center py-2">
                        <span class="invoice-supporting-files-header-icon mr-2"><i class="fa fa-paperclip" aria-hidden="true"></i></span>
                        <span class="font-weight-bold">Supporting files</span>
                        <span class="badge badge-light border ml-2 text-muted font-weight-normal">Optional</span>
                    </div>
                    <div class="card-body">
                        <div class="invoice-file-picker-zone @error('uploaded_files') border-danger @enderror @error('uploaded_files.*') border-danger @enderror">
                            <input
                                type="file"
                                id="invoice-supporting-files-input"
                                class="d-none"
                                wire:model="uploaded_files"
                                multiple
                                accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.doc,.docx,.xls,.xlsx,.txt,.csv"
                            >
                            <label for="invoice-supporting-files-input" class="btn btn-primary btn-sm invoice-file-choose-btn mb-2 mb-sm-0">
                                <i class="fa fa-folder-open mr-1" aria-hidden="true"></i> Choose files
                            </label>
                            <p class="invoice-file-picker-hint text-muted small mb-0 ml-sm-3">
                                Select one or more files (e.g. PDF, images, Office). Max 10 MB per file.
                            </p>
                        </div>
                        @error('uploaded_files') <span class="text-danger small d-block mt-2">{{ $message }}</span> @enderror
                        @error('uploaded_files.*') <span class="text-danger small d-block mt-2">{{ $message }}</span> @enderror

                        @if(!empty($uploaded_files))
                            <div class="mt-3 pt-2 border-top">
                                <div class="text-uppercase text-muted small font-weight-bold mb-2">Ready to upload</div>
                                @foreach($uploaded_files as $index => $file)
                                    <div class="d-flex align-items-center justify-content-between invoice-file-row rounded px-3 py-2 mb-2" wire:key="new-file-{{ $index }}">
                                        <span class="text-truncate pr-3 d-flex align-items-center">
                                            <i class="fa fa-file-o text-secondary mr-2 flex-shrink-0" aria-hidden="true"></i>
                                            {{ $file->getClientOriginalName() }}
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeUpload({{ $index }})" title="Remove file">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($existing_files))
                            <div class="mt-3 pt-2 border-top">
                                <div class="text-uppercase text-muted small font-weight-bold mb-2">Already attached</div>
                                @foreach($existing_files as $file)
                                    <div class="d-flex align-items-center justify-content-between invoice-file-row rounded px-3 py-2 mb-2" wire:key="existing-file-{{ $file['id'] }}">
                                        <a href="{{ asset('invoice_files/' . $file['invoice_id'] . '/' . $file['filename']) }}" target="_blank" rel="noopener" class="text-truncate pr-3 d-flex align-items-center text-body">
                                            <i class="fa fa-external-link text-primary mr-2 flex-shrink-0" aria-hidden="true"></i>
                                            {{ $file['filename'] }}
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteFile({{ $file['id'] }})" title="Delete file">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 form-group">
                        <label class="font-weight-bold">Completion date</label>
                        <input type="date" class="form-control @error('comp_date') is-invalid @enderror" wire:model="comp_date">
                        @error('comp_date') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-12 col-md-6 form-group">
                        <label class="font-weight-bold">Payment term</label>
                        <textarea class="form-control @error('pay_term') is-invalid @enderror" rows="4" wire:model="pay_term" placeholder="Describe payment terms (e.g. Net 30, milestones, due dates…)"></textarea>
                        @error('pay_term') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Invoice</button>
                <button type="button" class="btn btn-default" wire:click="closeModals">Cancel</button>
            </form>
        @endcomponent
    @endif

    @if($showViewModal && $viewRecord)
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'Invoice Details'])
            @php
                $viewStatusRaw = strtolower(trim((string) ($viewRecord->status ?? '')));
                $viewStatusLabel = match ($viewStatusRaw) {
                    'approve', 'approved' => 'Approve',
                    'in process', 'in_process', 'processing' => 'In Process',
                    'complete', 'completed' => 'Complete',
                    default => 'Pending',
                };

                $viewPriority = ucfirst(strtolower((string) ($viewRecord->priority ?? 'Medium')));
                $viewPriorityBadgeClass = match ($viewPriority) {
                    'High' => 'badge-danger',
                    'Low' => 'badge-secondary',
                    default => 'badge-warning',
                };
            @endphp

            <div class="row">
                <div class="col-md-12 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <strong>Invoice #:</strong> {{ $viewRecord->invoice_number ?? 'N/A' }}
                    </div>
                    <a href="{{ route('invoice.pdf', $viewRecord->id) }}" class="action-icon text-danger" target="_blank" rel="noopener noreferrer" title="Preview PDF">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                </div>
                <div class="col-md-12 mb-3">
                    <strong>Organization:</strong> {{ $viewRecord->organization?->name ?? 'N/A' }}
                </div>
                <div class="col-md-12 mb-3">
                    <strong>Brand Name:</strong> {{ $viewRecord->outlet?->name ?? 'N/A' }}
                </div>
                <div class="col-md-12 mb-3">
                    <strong>Party Name:</strong> {{ $viewRecord->vendor?->name ?? 'N/A' }}
                </div>

                <div class="col-md-4 mb-3">
                    <strong>Department:</strong> {{ $viewRecord->department?->name ?? 'N/A' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Priority:</strong>
                    <span class="badge {{ $viewPriorityBadgeClass }}">{{ $viewPriority }}</span>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Status:</strong>
                    <span class="badge badge-info">{{ $viewStatusLabel }}</span>
                </div>

                <div class="col-md-12 mb-3">
                    <strong>Task:</strong>
                    <div class="mt-1">{{ $viewRecord->description ?: 'N/A' }}</div>
                </div>
                <div class="col-md-12 mb-3">
                    <strong>Completion date:</strong>
                    {{ $viewRecord->comp_date ? $viewRecord->comp_date->format('M j, Y') : 'N/A' }}
                </div>
                <div class="col-md-12 mb-3">
                    <strong>Payment term:</strong>
                    <div class="mt-1 text-break" style="white-space: pre-wrap;">{{ $viewRecord->pay_term ? $viewRecord->pay_term : 'N/A' }}</div>
                </div>

                <div class="col-md-12 mb-3">
                    <strong>Status history:</strong>
                    @if($viewRecord->statusHistories->isEmpty())
                        <div class="text-muted mt-1">No status updates recorded yet.</div>
                    @else
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>When</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Comment</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewRecord->statusHistories as $statusRow)
                                        <tr>
                                            <td>{{ optional($statusRow->created_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                            <td>{{ $statusRow->from_status ?: '—' }}</td>
                                            <td>{{ $statusRow->to_status }}</td>
                                            <td>{{ $statusRow->comment ?: '—' }}</td>
                                            <td>{{ trim((string) (($statusRow->user?->first_name ?? '') . ' ' . ($statusRow->user?->last_name ?? ''))) ?: ($statusRow->user?->email ?? '—') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="table-responsive mt-2">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>HSN</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>CGST (%)</th>
                            <th>SGST (%)</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($viewRecord->details as $det)
                            @php
                                $qty = (float) ($det->quantity ?? 0);
                                $unitPrice = (float) ($det->unit_price ?? 0);
                                $cgst = (float) ($det->cgst ?? 0);
                                $sgst = (float) ($det->sgst ?? 0);
                                $lineBase = $qty * $unitPrice;
                                $lineTotal = $lineBase + ($lineBase * ($cgst + $sgst) / 100);
                            @endphp
                            <tr>
                                <td>{{ $det->product_desciption ?? 'N/A' }}</td>
                                <td>{{ $det->hsn ?? '-' }}</td>
                                <td>{{ $qty }}</td>
                                <td>{{ number_format($unitPrice, 2) }}</td>
                                <td>{{ number_format($cgst, 2) }}</td>
                                <td>{{ number_format($sgst, 2) }}</td>
                                <td>{{ number_format($lineTotal, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No line items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                @php
                    $viewGrossTotal = (float) $viewRecord->details->sum(function ($d) {
                        return ((float) ($d->quantity ?? 0)) * ((float) ($d->unit_price ?? 0));
                    });
                    $viewTaxTotal = (float) $viewRecord->details->sum(function ($d) {
                        $qty = (float) ($d->quantity ?? 0);
                        $unit = (float) ($d->unit_price ?? 0);
                        $cgst = (float) ($d->cgst ?? 0);
                        $sgst = (float) ($d->sgst ?? 0);
                        $base = $qty * $unit;
                        return $base * ($cgst + $sgst) / 100;
                    });
                @endphp
                <div class="text-right" style="width: 320px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <div class="d-flex justify-content-between">
                        <strong>Gross Total:</strong>
                        <span>{{ number_format($viewGrossTotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2 mt-2">
                        <strong>Tax (Total GST):</strong>
                        <span>{{ number_format($viewTaxTotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between pt-2">
                        <strong>Amount:</strong>
                        <strong>{{ number_format((float) ($viewRecord->total_amount ?? 0), 2) }}</strong>
                    </div>
                </div>
            </div>

            @php
                $viewFiles = \App\Models\InvoiceFile::where('invoice_id', $viewRecord->id)->orderByDesc('id')->get();
            @endphp

            <div class="mt-3">
                <label class="font-weight-bold d-block">Supporting Files</label>
                @if($viewFiles->isEmpty())
                    <div class="text-muted">No files uploaded.</div>
                @else
                    @foreach($viewFiles as $file)
                        <div class="d-flex align-items-center justify-content-between border rounded bg-white px-3 py-2 mb-2">
                            <a href="{{ asset('invoice_files/' . $file->invoice_id . '/' . $file->filename) }}" target="_blank" class="text-truncate pr-3">
                                {{ $file->filename }}
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        @endcomponent
    @endif

    <!-- Approve invoice modal -->
    <div class="modal fade {{ $showApproveModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" wire:key="invoice-approve-modal" style="background: rgba(0,0,0,0.5); z-index: 1060;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-check-circle mr-2 text-primary"></i>Approve invoice</h5>
                    <button type="button" class="close" wire:click="closeApproveModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form wire:submit.prevent="approveInvoice">
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Approval comment <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('approve_comment') is-invalid @enderror" rows="4" wire:model="approve_comment" placeholder="Add approval notes"></textarea>
                            @error('approve_comment') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeApproveModal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Add Outlet Modal -->
    <div class="modal fade {{ $showAddOutletModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Add Outlet</h5>
                    <button type="button" class="close" wire:click="closeAddOutletModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if(empty($organization_id))
                        <div class="alert alert-warning">Please select an Organization in the main form first.</div>
                    @else
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" class="form-control @error('new_outlet_name') is-invalid @enderror" wire:model="new_outlet_name">
                            @error('new_outlet_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Location *</label>
                            <select class="form-control @error('new_outlet_location_id') is-invalid @enderror" wire:model="new_outlet_location_id">
                                <option value="">Select Location</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            @error('new_outlet_location_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeAddOutletModal">Cancel</button>
                    @if(!empty($organization_id))
                        <button type="button" class="btn btn-primary" wire:click="saveNewOutlet">Save Outlet</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Vendor Modal -->
    <div class="modal fade {{ $showAddVendorModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Add Party Name (Vendor)</h5>
                    <button type="button" class="close" wire:click="closeAddVendorModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if(empty($organization_id))
                        <div class="alert alert-warning">Please select an Organization in the main form first.</div>
                    @else
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" class="form-control @error('new_vendor_name') is-invalid @enderror" wire:model="new_vendor_name">
                            @error('new_vendor_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="text" class="form-control @error('new_vendor_mobile') is-invalid @enderror" wire:model="new_vendor_mobile">
                            @error('new_vendor_mobile') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" class="form-control @error('new_vendor_email') is-invalid @enderror" wire:model="new_vendor_email">
                            @error('new_vendor_email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeAddVendorModal">Cancel</button>
                    @if(!empty($organization_id))
                        <button type="button" class="btn btn-primary" wire:click="saveNewVendor">Save Party Name</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Product Modal -->
    <div class="modal fade {{ $showAddProductModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Add Product</h5>
                    <button type="button" class="close" wire:click="closeAddProductModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if(empty($organization_id))
                        <div class="alert alert-warning">Please select an Organization in the main form first.</div>
                    @else
                        <div class="form-group">
                            <label>Product Name *</label>
                            <input type="text" class="form-control @error('new_product_name') is-invalid @enderror" wire:model="new_product_name">
                            @error('new_product_name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Unit Price *</label>
                            <input type="number" step="0.01" class="form-control @error('new_product_price') is-invalid @enderror" wire:model.live="new_product_price">
                            @error('new_product_price') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>HSN / SAC</label>
                            <input type="text" class="form-control @error('new_product_hsn') is-invalid @enderror" wire:model="new_product_hsn">
                            @error('new_product_hsn') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>CGST (%)</label>
                                <input type="number" step="0.01" class="form-control @error('new_product_cgst') is-invalid @enderror" wire:model.live="new_product_cgst">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>SGST (%)</label>
                                <input type="number" step="0.01" class="form-control @error('new_product_sgst') is-invalid @enderror" wire:model.live="new_product_sgst">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Total GST (%)</label>
                                <input type="number" step="0.01" class="form-control @error('new_product_total_gst') is-invalid @enderror" wire:model.live="new_product_total_gst">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Final Price</label>
                            <input type="number" step="0.01" class="form-control @error('new_product_final_price') is-invalid @enderror" wire:model="new_product_final_price">
                            @error('new_product_final_price') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeAddProductModal">Cancel</button>
                    @if(!empty($organization_id))
                        <button type="button" class="btn btn-primary" wire:click="saveNewProduct">Save Product</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Record payment (ledger) -->
    <div class="modal fade {{ $showPaymentModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" wire:key="invoice-payment-modal" style="background: rgba(0,0,0,0.5); z-index: 1060;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-money-check-alt mr-2 text-success"></i>Record payment</h5>
                    <button type="button" class="close" wire:click="closePaymentModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form wire:submit.prevent="savePayment">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Pay to</label>
                            <p class="form-control-plaintext border rounded px-3 py-2 mb-0 bg-light">{{ $payment_pay_to ?: '—' }}</p>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Pending amount</label>
                            <p class="form-control-plaintext border rounded px-3 py-2 mb-0 bg-light font-weight-bold">{{ number_format($payment_pending_amount, 2) }}</p>
                            <small class="text-muted">Invoice total minus amount already paid (read-only).</small>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Payment method <span class="text-danger">*</span></label>
                                <select class="form-control @error('payment_method') is-invalid @enderror" wire:model="payment_method">
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online">Online</option>
                                </select>
                                @error('payment_method') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('payment_status') is-invalid @enderror" wire:model="payment_status">
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="failed">Failed</option>
                                    <option value="invalid">Invalid</option>
                                </select>
                                @error('payment_status') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Amount to pay <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" class="form-control @error('payment_amount') is-invalid @enderror" wire:model.live="payment_amount" placeholder="0.00">
                            @error('payment_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Description</label>
                            <textarea class="form-control @error('payment_description') is-invalid @enderror" rows="3" wire:model="payment_description" placeholder="Notes (optional)"></textarea>
                            @error('payment_description') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closePaymentModal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Payment history (ledger entries) -->
    <div class="modal fade {{ $showPaymentHistoryModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" wire:key="invoice-payment-history-modal" style="background: rgba(0,0,0,0.5); z-index: 1060;">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-history mr-2 text-info"></i>Payment history
                        @if($paymentHistoryInvoiceNumber)
                            <span class="text-muted font-weight-normal">— Invoice {{ $paymentHistoryInvoiceNumber }}</span>
                        @endif
                    </h5>
                    <button type="button" class="close" wire:click="closePaymentHistoryModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    @if(count($paymentHistoryLedgers) === 0 && count($paymentHistoryActivity) === 0)
                        <div class="p-4 text-center text-muted">No payment records for this invoice yet.</div>
                    @else
                        @if(count($paymentHistoryLedgers) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Ledger</th>
                                        <th>Method</th>
                                        <th class="text-right">Amount</th>
                                        <th style="min-width: 11rem;">Status</th>
                                        <th>Description</th>
                                        <th>Recorded by</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentHistoryLedgers as $row)
                                        <tr wire:key="ledger-row-{{ $row['id'] }}">
                                            <td class="text-nowrap">{{ $row['created_date'] }}</td>
                                            <td class="text-muted small">#{{ $row['id'] }}</td>
                                            <td>{{ $row['payment_method'] }}</td>
                                            <td class="text-right font-weight-bold">{{ number_format($row['total_amount'], 2) }}</td>
                                            <td>
                                                @php $rowSt = strtolower((string) ($row['status'] ?? '')); @endphp
                                                @can('edit-invoice')
                                                    @if($rowSt === 'completed')
                                                        <div class="d-flex flex-wrap align-items-center" style="gap: 0.35rem;">
                                                            <span class="badge badge-success text-uppercase">Completed</span>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-outline-warning py-0 px-2"
                                                                title="Revert this payment"
                                                                wire:click="revertCompletedLedgerPayment({{ $row['id'] }})"
                                                                wire:confirm="Revert this completed payment? The amount will be removed from the invoice paid total and recorded in the activity log."
                                                            >
                                                                Revert
                                                            </button>
                                                        </div>
                                                    @else
                                                        <select
                                                            class="form-control form-control-sm"
                                                            wire:change="updateLedgerStatus({{ $row['id'] }}, $event.target.value)"
                                                            wire:key="ledger-status-{{ $row['id'] }}-{{ $row['status'] }}"
                                                        >
                                                            @foreach(['pending', 'cancelled', 'completed', 'failed', 'invalid'] as $st)
                                                                <option value="{{ $st }}" @selected($rowSt === $st)>{{ ucfirst($st) }}</option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                @else
                                                    <span class="badge {{ $rowSt === 'completed' ? 'badge-success' : 'badge-secondary' }} text-uppercase">{{ $row['status'] }}</span>
                                                @endcan
                                            </td>
                                            <td class="small text-break">{{ $row['description'] ?: '—' }}</td>
                                            <td class="small">{{ $row['recorded_by'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        @if(count($paymentHistoryActivity) > 0)
                        <div class="border-top bg-light">
                            <div class="px-3 py-2 font-weight-bold text-secondary small text-uppercase">Status &amp; balance activity</div>
                            <div class="invoice-payment-activity-scroll">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>When</th>
                                            <th>Ledger</th>
                                            <th>Details</th>
                                            <th>By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentHistoryActivity as $act)
                                            <tr wire:key="ledger-act-{{ $loop->index }}-{{ $act['at'] }}-{{ $act['ledger_id'] }}">
                                                <td class="text-nowrap small">{{ $act['at'] }}</td>
                                                <td class="text-muted small">#{{ $act['ledger_id'] }}</td>
                                                <td class="small">{{ $act['detail'] }}</td>
                                                <td class="small">{{ $act['by'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </div>
                        @endif
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closePaymentHistoryModal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';
    var appName = @json(config('app.name', 'Invoice Masters'));
    var invoiceDtTimer = null;

    function destroyInvoiceDt() {
        if (!window.jQuery || !$.fn || !$.fn.DataTable) return;
        var $t = jQuery('#invoiceTable');
        if (!$t.length) return;
        if (jQuery.fn.DataTable.isDataTable($t)) {
            $t.DataTable().destroy();
        }
    }

    function initInvoiceDt() {
        if (!window.jQuery || !$.fn || !$.fn.DataTable) return;
        var $t = jQuery('#invoiceTable');
        if (!$t.length) return;
        destroyInvoiceDt();

        var exportCols = [0, 1, 2, 3, 4, 5, 6, 7, 8];
        var exportOpts = {
            columns: exportCols,
            format: {
                body: function (data, row, column, node) {
                    return jQuery(node).text().trim() || data;
                }
            }
        };
        var title = appName + ' - Invoices';
        var filename = appName.replace(/[^\w\-]+/g, '_') + '_Invoices';

        $t.DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: true,
            order: [[8, 'desc']],
            dom: 'Brt',
            buttons: [
                { extend: 'print', title: title, text: '<i class="fa fa-print"></i> Print', className: 'btn btn-secondary btn-sm', exportOptions: exportOpts },
                { extend: 'copyHtml5', title: title, text: '<i class="fa fa-copy"></i> Copy', className: 'btn btn-primary btn-sm', exportOptions: exportOpts },
                { extend: 'excelHtml5', title: title, filename: filename, text: '<i class="fa fa-download"></i> Excel', className: 'btn btn-success btn-sm', exportOptions: exportOpts },
                { extend: 'pdfHtml5', title: title, filename: filename, text: '<i class="fa fa-download"></i> PDF', className: 'btn btn-danger btn-sm', orientation: 'landscape', pageSize: 'A4', exportOptions: exportOpts },
                { extend: 'colvis', text: '<i class="fa fa-columns"></i> Column visibility', className: 'btn btn-warning btn-sm', columns: ':not(.no-vis)' }
            ],
            columnDefs: [
                { orderable: false, targets: [9] }
            ],
            initComplete: function () {
                var $wrap = jQuery('#invoiceTable').closest('.dataTables_wrapper');
                if ($wrap.length) {
                    $wrap.find('.dt-buttons').first().appendTo('#invoiceDtButtonsWrap');
                }
            }
        });
    }

    function scheduleInvoiceDt() {
        clearTimeout(invoiceDtTimer);
        invoiceDtTimer = setTimeout(initInvoiceDt, 50);
    }

    function getInvoiceLivewireComponent() {
        if (typeof Livewire === 'undefined') return null;
        var root = document.getElementById('master-list');
        if (!root) return null;
        var wid = root.getAttribute('wire:id');
        if (!wid) return null;
        return Livewire.find(wid);
    }

    /** Select2 hides the native select; wire:model often won't update — use $wire.call() to refetch the list. */
    function bindInvoiceFilterSelectsToLivewire() {
        var cmp = getInvoiceLivewireComponent();
        if (!cmp || !window.jQuery || typeof cmp.call !== 'function') return;
        var $status = jQuery('#invoice_filter_status');
        var $dept = jQuery('#invoice_filter_department');
        $status.off('change.invoiceFilterSync');
        $dept.off('change.invoiceFilterSync');
        $status.on('change.invoiceFilterSync', function () {
            var raw = jQuery(this).val();
            var v = raw == null ? [] : (Array.isArray(raw) ? raw : [raw]);
            cmp.call('syncFilterStatusesFromSelect', v);
        });
        $dept.on('change.invoiceFilterSync', function () {
            var raw = jQuery(this).val();
            var arr = raw == null ? [] : (Array.isArray(raw) ? raw : [raw]);
            var nums = arr.map(function (x) { return parseInt(x, 10); }).filter(function (n) { return !isNaN(n); });
            cmp.call('syncFilterDepartmentIdsFromSelect', nums);
        });
    }

    /** Select2 multi filters (Users module pattern); re-init after Livewire morphs DOM. */
    function initInvoiceFilterSelect2() {
        if (!window.jQuery || !$.fn.select2) return;
        var $wrap = jQuery('#invoiceFilterWrapper');
        if (!$wrap.length) return;
        var $status = jQuery('#invoice_filter_status');
        var $dept = jQuery('#invoice_filter_department');
        var cfg = {
            width: '100%',
            allowClear: true,
            dropdownCssClass: 'invoice-filter-select2-dropdown',
            dropdownParent: jQuery(document.body)
        };
        if ($status.length) {
            if ($status.hasClass('select2-hidden-accessible')) {
                $status.select2('destroy');
            }
            $status.select2(jQuery.extend({
                placeholder: $status.data('placeholder') || 'All statuses'
            }, cfg));
        }
        if ($dept.length) {
            if ($dept.hasClass('select2-hidden-accessible')) {
                $dept.select2('destroy');
            }
            $dept.select2(jQuery.extend({
                placeholder: $dept.data('placeholder') || 'All departments'
            }, cfg));
        }
        bindInvoiceFilterSelectsToLivewire();
    }

    function afterInvoiceLivewireUpdate() {
        scheduleInvoiceDt();
        setTimeout(initInvoiceFilterSelect2, 0);
    }

    if (window.jQuery) {
        jQuery(function () { afterInvoiceLivewireUpdate(); });
    }
    document.addEventListener('livewire:load', afterInvoiceLivewireUpdate);
    document.addEventListener('livewire:navigated', function () { setTimeout(afterInvoiceLivewireUpdate, 0); });
    document.addEventListener('livewire:init', function () {
        if (window.Livewire && Livewire.hook) {
            Livewire.hook('commit', function (_ref) {
                var succeed = _ref.succeed;
                succeed(afterInvoiceLivewireUpdate);
            });
            Livewire.hook('message.processed', afterInvoiceLivewireUpdate);
        }
    });
})();
</script>
@endpush

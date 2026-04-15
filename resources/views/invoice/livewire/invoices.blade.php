<div wire:key="invoice-module" id="master-list">
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @component('masterapp.livewire.masters.components.list-card', [
            'title' => 'Invoices',
            'addButtonText' => 'Create Invoice',
            'tableId' => 'invoiceTable',
            'orderCol' => '0',
            'nonOrderableTargets' => '5,6',
        ])
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Vendor</th>
                    <th>Organization</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="master-table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->vendor?->name }}</td>
                        <td>{{ $invoice->organization?->name }}</td>
                        <td>{{ number_format($invoice->total_amount, 2) }}</td>
                        <td>{{ $invoice->created_date }}</td>
                        <td><span class="badge badge-info">{{ strtoupper($invoice->status) }}</span></td>
                        <td>
                            <div class="action-div master-actions">
                                <a href="#" wire:click.prevent="openViewModal({{ $invoice->id }})" class="action-icon"><i class="fa fa-eye"></i></a>
                                <a href="#" wire:click.prevent="openEditModal({{ $invoice->id }})" class="action-icon"><i class="fa fa-edit"></i></a>
                                <a href="#" data-master-delete-id="{{ $invoice->id }}" class="action-icon master-delete-link"><i class="fa fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        @endcomponent
    @endif

    @if($showCreateModal || $showEditModal)
        @component('masterapp.livewire.masters.components.form-card', [
            'formTitleAdd' => 'Create Invoice',
            'formTitleEdit' => $showEditModal && $invoice_number ? "Edit Invoice ($invoice_number)" : 'Edit Invoice',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}">
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label>Organization *</label>
                        <select class="form-control @error('organization_id') is-invalid @enderror" wire:model.live="organization_id">
                            <option value="">Select Organization</option>
                            @foreach($organizations as $org) <option value="{{ $org->id }}">{{ $org->name }}</option> @endforeach
                        </select>
                        @error('organization_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-12 form-group">
                        <label>Select Outlet *</label>
                        <div class="input-group">
                            <select class="form-control @error('outlet_id') is-invalid @enderror" wire:model="outlet_id">
                                <option value="">Select Outlet</option>
                                @foreach($outlets as $out) <option value="{{ $out->id }}">{{ $out->name }}</option> @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" wire:click="openAddOutletModal">+</button>
                            </div>
                        </div>
                        @error('outlet_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-12 form-group">
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
                        <select class="form-control @error('department_id') is-invalid @enderror" wire:model="department_id">
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
                        <select class="form-control" wire:model="status">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>
                    @endif
                    <div class="col-md-12 form-group">
                        <label>Task</label>
                        <textarea class="form-control" rows="5" wire:model="description"></textarea>
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

                <div class="card border-0 bg-light mb-3">
                    <div class="card-body py-3">
                        <label class="font-weight-bold d-block">Supporting Files</label>
                        <input type="file" class="form-control-file @error('uploaded_files.*') is-invalid @enderror" wire:model="uploaded_files" multiple>
                        <small class="text-muted d-block mt-1">Upload multiple files. Files are stored in `public/invoice_files/{invoice_id}`.</small>
                        @error('uploaded_files') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        @error('uploaded_files.*') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror

                        <div wire:loading wire:target="uploaded_files" class="text-primary small mt-2">
                            Uploading files...
                        </div>

                        @if(!empty($uploaded_files))
                            <div class="mt-3">
                                <div class="font-weight-bold small text-muted mb-2">Files to upload</div>
                                @foreach($uploaded_files as $index => $file)
                                    <div class="d-flex align-items-center justify-content-between border rounded bg-white px-3 py-2 mb-2" wire:key="new-file-{{ $index }}">
                                        <span class="text-truncate pr-3">
                                            {{ $file->getClientOriginalName() }}
                                        </span>
                                        <button type="button" class="btn btn-link text-danger p-0" wire:click="removeUpload({{ $index }})" title="Remove file">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($existing_files))
                            <div class="mt-3">
                                <div class="font-weight-bold small text-muted mb-2">Uploaded files</div>
                                @foreach($existing_files as $file)
                                    <div class="d-flex align-items-center justify-content-between border rounded bg-white px-3 py-2 mb-2" wire:key="existing-file-{{ $file['id'] }}">
                                        <a href="{{ asset('invoice_files/' . $file['invoice_id'] . '/' . $file['filename']) }}" target="_blank" class="text-truncate pr-3">
                                            {{ $file['filename'] }}
                                        </a>
                                        <button type="button" class="btn btn-link text-danger p-0" wire:click="deleteFile({{ $file['id'] }})" title="Delete file">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>



                <button type="submit" class="btn btn-primary">Save Invoice</button>
                <button type="button" class="btn btn-default" wire:click="closeModals">Cancel</button>
            </form>
        @endcomponent
    @endif

    @if($showViewModal && $viewRecord)
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'Invoice Details'])
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Invoice #:</strong> {{ $viewRecord->invoice_number }}</p>
                    <p><strong>Vendor:</strong> {{ $viewRecord->vendor?->name }}</p>
                </div>
                <div class="col-md-6 text-right">
                    <p><strong>Date:</strong> {{ $viewRecord->created_date }}</p>
                    <p><strong>Total:</strong> {{ number_format($viewRecord->total_amount, 2) }}</p>
                </div>
            </div>
            <table class="table mt-3">
                @foreach($viewRecord->details as $det)
                    <tr><td>{{ $det->product_desciption }}</td><td>{{ $det->quantity }}</td><td>{{ number_format($det->total_amount, 2) }}</td></tr>
                @endforeach
            </table>
        @endcomponent
    @endif

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
</div>

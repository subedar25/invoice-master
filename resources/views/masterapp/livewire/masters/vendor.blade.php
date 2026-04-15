<div wire:key="vendor-master" id="master-list">
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @component('masterapp.livewire.masters.components.list-card', [
            'title' => 'Vendors',
            'addButtonText' => 'Add Vendor',
            'tableId' => 'vendorMasterTable',
            'orderCol' => '0',
            'nonOrderableTargets' => '6,7',
        ])
            <div class="master-toolbar mb-3">
                <div class="master-toolbar__filters">
                    <div class="d-inline-flex align-items-center">
                        <label for="vendor_organization_filter" class="mr-2 mb-0">Organization</label>
                        <select id="vendor_organization_filter" class="form-control form-control-sm" style="min-width: 240px;" wire:model.live="organizationFilter">
                            <option value="">All Organizations</option>
                            @foreach($organizationOptions as $opt)
                                <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <thead>
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Organization</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th class="master-table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->companyname ?: '—' }}</td>
                        <td>{{ $item->organization?->name ?? '—' }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->mobile ?: '—' }}</td>
                        <td>{{ $item->category?->name ?? '—' }}</td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="status_{{ $item->id }}" @if($item->status) checked @endif wire:change="toggleStatus({{ $item->id }})">
                                <label class="custom-control-label" for="status_{{ $item->id }}"></label>
                            </div>
                        </td>
                        <td>
                            <div class="action-div master-actions">
                                <a href="#" wire:click.prevent="openViewModal({{ $item->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye"></i></a>
                                <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                <a href="#" data-master-delete-id="{{ $item->id }}" data-master-delete-title="Delete Vendor?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        @endcomponent
    @endif

    @if($showCreateModal || $showEditModal)
        @component('masterapp.livewire.masters.components.form-card', [
            'formTitleAdd' => 'Add Vendor',
            'formTitleEdit' => 'Edit Vendor',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}" class="w-100">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.live="name">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.live="email">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group"><label>Company Name</label><input type="text" class="form-control" wire:model="companyname"></div>
                    <div class="col-md-6 form-group"><label>Mobile</label><input type="text" class="form-control" wire:model="mobile"></div>
                </div>
                <div class="form-group">
                    <label>Organization</label>
                    <select class="form-control @error('organization_id') is-invalid @enderror" wire:model="organization_id">
                        <option value="">Select Organization</option>
                        @foreach($organizationOptions as $opt) <option value="{{ $opt->id }}">{{ $opt->name }}</option> @endforeach
                    </select>
                    @error('organization_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select class="form-control" wire:model="category_id">
                        <option value="">Select Category</option>
                        @foreach($categoryOptions as $opt) <option value="{{ $opt->id }}">{{ $opt->name }}</option> @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Address</label><textarea class="form-control" rows="2" wire:model="address"></textarea></div>
                <div class="row">
                    <div class="col-md-4 form-group"><label>City</label><input type="text" class="form-control" wire:model="city"></div>
                    <div class="col-md-4 form-group"><label>State</label><input type="text" class="form-control" wire:model="state"></div>
                    <div class="col-md-4 form-group"><label>Pincode</label><input type="text" class="form-control" wire:model="pin"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group"><label>PAN</label><input type="text" class="form-control" wire:model="PAN"></div>
                    <div class="col-md-6 form-group"><label>GST Number</label><input type="text" class="form-control" wire:model="gst"></div>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="vendor_status" wire:model="status">
                        <label class="custom-control-label" for="vendor_status">Active</label>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Bank Details</h6>
                    <button type="button" class="btn btn-success btn-sm" wire:click="addBank">
                        <i class="fa fa-plus"></i> Add Bank
                    </button>
                </div>

                @foreach($banks as $index => $bank)
                    <div class="border rounded p-3 mb-3 bg-light position-relative">
                        @if(count($banks) > 1)
                            <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 10px; right: 10px;" wire:click="removeBank({{ $index }})">
                                <i class="fa fa-times"></i>
                            </button>
                        @endif
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Bank Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('banks.'.$index.'.bank_name') is-invalid @enderror" wire:model="banks.{{ $index }}.bank_name">
                                @error('banks.'.$index.'.bank_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Account Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('banks.'.$index.'.ac_number') is-invalid @enderror" wire:model="banks.{{ $index }}.ac_number">
                                @error('banks.'.$index.'.ac_number') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>IFSC Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('banks.'.$index.'.ifsc_number') is-invalid @enderror" wire:model="banks.{{ $index }}.ifsc_number">
                                @error('banks.'.$index.'.ifsc_number') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Account Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('banks.'.$index.'.ac_type') is-invalid @enderror" wire:model="banks.{{ $index }}.ac_type">
                                    <option value="">Select Type</option>
                                    <option value="Savings">Savings</option>
                                    <option value="Current">Current</option>
                                </select>
                                @error('banks.'.$index.'.ac_type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary">{{ $showEditModal ? 'Update' : 'Save' }}</button>
                <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
            </form>
        @endcomponent
    @endif

    @if($showViewModal && $viewRecord)
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'View Vendor'])
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $viewRecord->name }}</dd>
                <dt class="col-sm-3">Company</dt><dd class="col-sm-9">{{ $viewRecord->companyname ?: '—' }}</dd>
                <dt class="col-sm-3">Organization</dt><dd class="col-sm-9">{{ $viewRecord->organization?->name ?? '—' }}</dd>
                <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $viewRecord->email }}</dd>
                <dt class="col-sm-3">Mobile</dt><dd class="col-sm-9">{{ $viewRecord->mobile ?: '—' }}</dd>
                <dt class="col-sm-3">Category</dt><dd class="col-sm-9">{{ $viewRecord->category?->name ?? '—' }}</dd>
                <dt class="col-sm-3">Address</dt><dd class="col-sm-9">{{ $viewRecord->address ?: '—' }}</dd>
                <dt class="col-sm-3">City/State/PIN</dt><dd class="col-sm-9">{{ $viewRecord->city }} {{ $viewRecord->state }} {{ $viewRecord->pin }}</dd>
                <dt class="col-sm-3">PAN/GST</dt><dd class="col-sm-9">{{ $viewRecord->PAN ?: '—' }} / {{ $viewRecord->gst ?: '—' }}</dd>
            </dl>

            @if($viewRecord->banks->isNotEmpty())
                <hr>
                <h6 class="mb-3">Bank Details</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Bank Name</th>
                                <th>A/C Number</th>
                                <th>IFSC</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($viewRecord->banks as $bank)
                                <tr>
                                    <td>{{ $bank->bank_name }}</td>
                                    <td>{{ $bank->ac_number }}</td>
                                    <td>{{ $bank->ifsc_number }}</td>
                                    <td>{{ $bank->ac_type }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endcomponent
    @endif
</div>
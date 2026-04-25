<div wire:key="state" id="master-list">
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @if($items->isNotEmpty())
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">States</h5>
                    </div>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-primary btn-sm" wire:click.prevent="openCreateModal">
                            <i class="fa fa-plus"></i> Add State
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="master-toolbar">
                        <div class="master-toolbar__filters">
                            <div id="stateCountryFilterContainer" class="d-inline-flex align-items-center">
                                <label for="state_country_filter" class="mr-2 mb-0">Country</label>
                                <select id="state_country_filter" class="form-control form-control-sm" style="min-width: 240px;" wire:model.live="countryFilter">
                                    <option value="">All Countries</option>
                                    @foreach($this->countryOptions as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="stateMasterTable" class="table table-bordered table-hover table-sm js-master-datatable" data-order-col="0" data-order-dir="asc" data-non-orderable-targets="4">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Country</th>
                                    <th>Status</th>
                                    <th class="master-table-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->code ?: '—' }}</td>
                                        <td>{{ $item->country?->name ?? '—' }}</td>
                                        <td>
                                            @if($item->status)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-div master-actions">
                                                <a href="#" wire:click.prevent="openViewModal({{ $item->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                                @can('edit-state')
                                    <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                    @endcan
                                                @can('delete-state')
                                    <a href="#" data-master-delete-id="{{ $item->id }}" data-master-delete-title="Delete State?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                    @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">States</h5>
                        <div id="stateCountryFilterContainer" class="d-inline-flex align-items-center">
                            <label for="state_country_filter" class="mr-2 mb-0">Country</label>
                            <select id="state_country_filter" class="form-control form-control-sm" style="min-width: 240px;" wire:model.live="countryFilter">
                                <option value="">All Countries</option>
                                @foreach($this->countryOptions as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-primary btn-sm" wire:click.prevent="openCreateModal">
                            <i class="fa fa-plus"></i> Add State
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted mb-0">No states found.</p>
                </div>
            </div>
        @endif
    @endif

    @if($showCreateModal || $showEditModal)
        @component('masterapp.livewire.masters.components.form-card', [
            'formTitleAdd' => 'Add State',
            'formTitleEdit' => 'Edit State',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}" class="w-75">
                <div class="form-group">
                    <label for="state_country_id">Country <span class="text-danger">*</span></label>
                    <select id="state_country_id" class="form-control @error('country_id') is-invalid @enderror" wire:model="country_id">
                        <option value="">Select Country</option>
                        @foreach($this->countryOptions as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                    @error('country_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="state_name">Name <span class="text-danger">*</span></label>
                    <input type="text" id="state_name" class="form-control @error('name') is-invalid @enderror" wire:model.live="name">
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="state_code">Code</label>
                    <input type="text" id="state_code" class="form-control @error('code') is-invalid @enderror" wire:model.live="code" maxlength="10">
                    @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="state_status">Status</label>
                    <select id="state_status" class="form-control @error('status') is-invalid @enderror" wire:model="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary">{{ $showEditModal ? 'Update' : 'Save' }}</button>
                <button type="button" class="btn btn-secondary" wire:click="backFromForm">Cancel</button>
            </form>
        @endcomponent
    @endif

    @if($showViewModal && $this->viewRecord)
        @php $viewRecord = $this->viewRecord; @endphp
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'View State'])
            <dl class="row mb-0">
                <dt class="col-sm-3">ID</dt>
                <dd class="col-sm-9">{{ $viewRecord->id }}</dd>

                <dt class="col-sm-3">Country</dt>
                <dd class="col-sm-9">{{ $viewRecord->country?->name ?? '—' }}</dd>

                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $viewRecord->name }}</dd>

                <dt class="col-sm-3">Code</dt>
                <dd class="col-sm-9">{{ $viewRecord->code ?: '—' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{{ $viewRecord->status ? 'Active' : 'Inactive' }}</dd>
            </dl>
        @endcomponent
    @endif
</div>

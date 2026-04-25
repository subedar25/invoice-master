<div wire:key="location-master" id="master-list">
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @if($items->isNotEmpty())
            @component('masterapp.livewire.masters.components.list-card', [
                'title' => 'Locations',
                'addButtonText' => 'Add Location',
                'tableId' => 'locationMasterTable',
                'orderCol' => '0',
                'orderDir' => 'asc',
                'nonOrderableTargets' => '6',
            ])
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Country</th>
                        <th>State</th>
                        <th>City</th>
                        <th>PIN Code</th>
                        <th class="master-table-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr @class(['text-muted' => (bool) $item->deleted_at])>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->address ?: '—' }}</td>
                            <td>{{ $item->country ?: '—' }}</td>
                            <td>{{ $item->state ?: '—' }}</td>
                            <td>{{ $item->city ?: '—' }}</td>
                            <td>{{ $item->postal_code ?: '—' }}</td>
                            <td>
                                <div class="action-div master-actions">
                                    <a href="#" wire:click.prevent="openViewModal({{ $item->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                    @if(!$item->deleted_at)
                                        @can('edit-location')
                                    <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                    @endcan
                                        @can('delete-location')
                                    <a href="#" data-master-delete-id="{{ $item->id }}" data-master-delete-title="Delete Location?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                    @endcan
                                    @elseif((auth()->user()?->user_type ?? '') === 'systemuser')
                                        <a href="#" wire:click.prevent="restoreById({{ $item->id }})" title="Revert" class="action-icon entity-link"><i class="fa fa-undo" aria-hidden="true"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            @endcomponent
        @else
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Locations</h5>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-primary btn-sm" wire:click.prevent="openCreateModal">
                            <i class="fa fa-plus"></i> Add Location
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted mb-0">No locations found.</p>
                </div>
            </div>
        @endif
    @endif

    @if($showCreateModal || $showEditModal)
        @component('masterapp.livewire.masters.components.form-card', [
            'formTitleAdd' => 'Add Location',
            'formTitleEdit' => 'Edit Location',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}" class="w-75">
                <div class="form-group">
                    <label for="loc_name">Name <span class="text-danger">*</span></label>
                    <input type="text" id="loc_name" class="form-control @error('name') is-invalid @enderror" wire:model.live="name">
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <input type="hidden" wire:model="organization_id">
                    @error('organization_id') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="loc_address">Address</label>
                    <textarea id="loc_address" rows="3" class="form-control @error('address') is-invalid @enderror" wire:model="address"></textarea>
                    @error('address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="loc_country">Country</label>
                    <select id="loc_country" class="form-control @error('country_id') is-invalid @enderror" wire:model.live="country_id">
                        <option value="">Select Country</option>
                        @foreach($this->countryOptions as $countryOption)
                            <option value="{{ $countryOption->id }}">{{ $countryOption->name }}</option>
                        @endforeach
                    </select>
                    @error('country_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="loc_state">State</label>
                    <select id="loc_state" class="form-control @error('state') is-invalid @enderror" wire:model="state">
                        <option value="">Select State</option>
                        @foreach($this->stateOptions as $stateOption)
                            <option value="{{ $stateOption->name }}">{{ $stateOption->name }}</option>
                        @endforeach
                    </select>
                    @error('state') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="loc_city">City</label>
                    <input type="text" id="loc_city" class="form-control @error('city') is-invalid @enderror" wire:model="city">
                    @error('city') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="loc_postal_code">PIN Code</label>
                    <input type="text" id="loc_postal_code" class="form-control @error('postal_code') is-invalid @enderror" wire:model="postal_code">
                    @error('postal_code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary">{{ $showEditModal ? 'Update' : 'Save' }}</button>
                <button type="button" class="btn btn-secondary" wire:click="backFromForm">Cancel</button>
            </form>
        @endcomponent
    @endif

    @if($showViewModal && $this->viewRecord)
        @php $viewRecord = $this->viewRecord; @endphp
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'View Location'])
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $viewRecord->name }}</dd>

                <dt class="col-sm-3">Organization</dt>
                <dd class="col-sm-9">{{ $viewRecord->organization?->name ?? '—' }}</dd>

                <dt class="col-sm-3">Address</dt>
                <dd class="col-sm-9">{{ $viewRecord->address ?: '—' }}</dd>

                <dt class="col-sm-3">Country</dt>
                <dd class="col-sm-9">{{ $viewRecord->country ?: '—' }}</dd>

                <dt class="col-sm-3">State</dt>
                <dd class="col-sm-9">{{ $viewRecord->state ?: '—' }}</dd>

                <dt class="col-sm-3">City</dt>
                <dd class="col-sm-9">{{ $viewRecord->city ?: '—' }}</dd>

                <dt class="col-sm-3">PIN Code</dt>
                <dd class="col-sm-9">{{ $viewRecord->postal_code ?: '—' }}</dd>
            </dl>
        @endcomponent
    @endif
</div>

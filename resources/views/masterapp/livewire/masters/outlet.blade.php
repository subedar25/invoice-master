<div wire:key="outlet-master" id="master-list">
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @component('masterapp.livewire.masters.components.list-card', [
            'title' => 'Outlets',
            'addButtonText' => 'Add Outlet',
            'tableId' => 'outletMasterTable',
            'orderCol' => '0',
            'nonOrderableTargets' => '4,5',
        ])
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Area Manager</th>
                    <th>City</th>
                    <th>Status</th>
                    <th class="master-table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->location?->name ?? '—' }}</td>
                        <td>{{ $item->areaManager ? $item->areaManager->first_name . ' ' . $item->areaManager->last_name : '—' }}</td>
                        <td>{{ $item->city ?: '—' }}</td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="status_{{ $item->id }}" @if($item->status) checked @endif wire:change="toggleStatus({{ $item->id }})">
                                <label class="custom-control-label" for="status_{{ $item->id }}"></label>
                            </div>
                        </td>
                        <td>
                            <div class="action-div master-actions">
                                <a href="#" wire:click.prevent="openViewModal({{ $item->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye"></i></a>
                                <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit"></i></a>
                                <a href="#" data-master-delete-id="{{ $item->id }}" data-master-delete-title="Delete Outlet?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        @endcomponent
    @endif

    @if($showCreateModal || $showEditModal)
        @component('masterapp.livewire.masters.components.form-card', [
            'formTitleAdd' => 'Add Outlet',
            'formTitleEdit' => 'Edit Outlet',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}" class="w-75">
                <div class="form-group">
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.live="name">
                </div>

                <div class="form-group">
                    <input type="hidden" wire:model="organization_id">
                    @error('organization_id') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Location <span class="text-danger">*</span></label>
                    <select class="form-control @error('location_id') is-invalid @enderror" wire:model="location_id">
                        <option value="">Select Location</option>
                        @foreach($this->locationOptions as $opt) <option value="{{ $opt->id }}">{{ $opt->name }}</option> @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Area Manager</label>
                    <select class="form-control" wire:model="area_manager_id">
                        <option value="">Select Manager</option>
                        @foreach($this->areaManagerOptions as $opt) <option value="{{ $opt->id }}">{{ $opt->first_name }} {{ $opt->last_name }}</option> @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea class="form-control" rows="2" wire:model="address"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Country</label>
                        <select class="form-control" wire:model.live="country_id">
                            <option value="">Select Country</option>
                            @foreach($this->countryOptions as $opt) <option value="{{ $opt->id }}">{{ $opt->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>State</label>
                        <select class="form-control" wire:model="state_id">
                            <option value="">Select State</option>
                            @foreach($this->stateOptions as $opt) <option value="{{ $opt->id }}">{{ $opt->name }}</option> @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>City</label>
                        <input type="text" class="form-control" wire:model="city">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Pincode</label>
                        <input type="text" class="form-control" wire:model="pincode">
                    </div>
                </div>

                <div class="form-group">
                    <label>Photo</label>
                    <input type="file" class="form-control-file @error('photo') is-invalid @enderror" wire:model="photo">
                    @if ($photo)
                        <div class="mt-2 position-relative d-inline-block">
                            <img src="{{ $photo->temporaryUrl() }}" style="max-height: 80px;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute" style="top:-5px; right:-5px; border-radius:50%; padding:2px 6px;" wire:click="removePhoto"><i class="fa fa-times"></i></button>
                        </div>
                    @elseif ($existingPhoto)
                        <div class="mt-2 position-relative d-inline-block">
                            <img src="{{ asset($existingPhoto) }}" style="max-height: 80px;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute" style="top:-5px; right:-5px; border-radius:50%; padding:2px 6px;" wire:click="removePhoto"><i class="fa fa-times"></i></button>
                        </div>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">{{ $showEditModal ? 'Update' : 'Save' }}</button>
                <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
            </form>
        @endcomponent
    @endif

    @if($showViewModal && ($viewRecord = $this->viewRecord))
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'View Outlet'])
            <div class="row">
                <div class="col-sm-8">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Name</dt><dd class="col-sm-8">{{ $viewRecord->name }}</dd>
                        <dt class="col-sm-4">Location</dt><dd class="col-sm-8">{{ $viewRecord->location?->name }}</dd>
                        <dt class="col-sm-4">Organization</dt><dd class="col-sm-8">{{ $viewRecord->organization?->name ?? '—' }}</dd>
                        <dt class="col-sm-4">Manager</dt><dd class="col-sm-8">{{ $viewRecord->areaManager ? $viewRecord->areaManager->first_name . ' ' . $viewRecord->areaManager->last_name : '—' }}</dd>
                        <dt class="col-sm-4">Address</dt><dd class="col-sm-8">{{ $viewRecord->address ?: '—' }}</dd>
                        <dt class="col-sm-4">City/PIN</dt><dd class="col-sm-8">{{ $viewRecord->city }} {{ $viewRecord->pincode ? '- ' . $viewRecord->pincode : '' }}</dd>
                        <dt class="col-sm-4">State/Country</dt><dd class="col-sm-8">{{ $viewRecord->state?->name }} / {{ $viewRecord->country?->name }}</dd>
                    </dl>
                </div>
                <div class="col-sm-4 text-center">
                    @if($viewRecord->photo)
                        <img src="{{ asset($viewRecord->photo) }}" class="img-thumbnail" style="max-height: 150px;">
                    @else
                        <div class="p-4 bg-light text-muted">No Photo</div>
                    @endif
                </div>
            </div>
        @endcomponent
    @endif
</div>
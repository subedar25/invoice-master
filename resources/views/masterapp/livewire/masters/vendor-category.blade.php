<div wire:key="vendor-category-master" id="master-list">
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @component('masterapp.livewire.masters.components.list-card', [
            'title' => 'Vendor Categories',
            'addButtonText' => 'Add Vendor Category',
            'tableId' => 'vendorCategoryMasterTable',
            'orderCol' => '0',
            'nonOrderableTargets' => '2,3',
        ])
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="master-table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->desc ?: '—' }}</td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="vendor_category_status_{{ $item->id }}" @checked($item->status) wire:change="toggleStatus({{ $item->id }})">
                                <label class="custom-control-label" for="vendor_category_status_{{ $item->id }}"></label>
                            </div>
                        </td>
                        <td>
                            <div class="action-div master-actions">
                                <a href="#" wire:click.prevent="openViewModal({{ $item->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                <a href="#" data-master-delete-id="{{ $item->id }}" data-master-delete-title="Delete Vendor Category?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash" aria-hidden="true"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        @endcomponent
    @endif

    @if($showCreateModal || $showEditModal)
        @component('masterapp.livewire.masters.components.form-card', [
            'formTitleAdd' => 'Add Vendor Category',
            'formTitleEdit' => 'Edit Vendor Category',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}" class="w-75">
                <div class="form-group">
                    <label for="vendor_category_name">Category Name <span class="text-danger">*</span></label>
                    <input type="text" id="vendor_category_name" class="form-control @error('name') is-invalid @enderror" wire:model.live="name">
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="vendor_category_desc">Description</label>
                    <textarea id="vendor_category_desc" class="form-control @error('desc') is-invalid @enderror" rows="3" wire:model="desc"></textarea>
                    @error('desc') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="vendor_category_status">Status</label>
                    <select id="vendor_category_status" class="form-control @error('status') is-invalid @enderror" wire:model="status">
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
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'View Vendor Category'])
            <dl class="row mb-0">
                <dt class="col-sm-3">Organization</dt>
                <dd class="col-sm-9">{{ $viewRecord->organization?->name ?? '—' }}</dd>

                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $viewRecord->name }}</dd>

                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $viewRecord->desc ?: '—' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{{ $viewRecord->status ? 'Active' : 'Inactive' }}</dd>
            </dl>
        @endcomponent
    @endif
</div>


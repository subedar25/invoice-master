<div wire:key="publication" id="master-list">
    {{-- List screen --}}
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @if($items->isNotEmpty())
            @component('masterapp.livewire.masters.components.list-card', [
                'title' => 'Publications',
                'addButtonText' => 'Add Publication',
                'tableId' => 'publicationMasterTable',
                'orderCol' => '2',
                'nonOrderableTargets' => '3,4',
            ])
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Publication Type</th>
                        <th>Created Date</th>
                        <th>Active</th>
                        <th class="master-table-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->publicationType?->publication_type ?? '—' }}</td>
                            <td>{{ $item->created_at?->format('M j, Y') ?? '—' }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="status_toggle_{{ $item->id }}" @if($item->status) checked @endif wire:change="toggleStatus({{ $item->id }})">
                                    <label class="custom-control-label" for="status_toggle_{{ $item->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <div class="action-div master-actions">
                                    <a href="#" wire:click.prevent="openViewModal({{ $item->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                    <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                    <a href="#" data-master-delete-id="{{ $item->id }}" data-master-delete-title="Delete Publication?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            @endcomponent
        @else
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Publications</h5>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-primary btn-sm" wire:click.prevent="openCreateModal">
                            <i class="fa fa-plus"></i> Add Publication
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted mb-0">No publications found.</p>
                </div>
            </div>
        @endif
    @endif

    {{-- Add/Edit form --}}
    @if($showCreateModal || $showEditModal)
        @component('masterapp.livewire.masters.components.form-card', [
            'formTitleAdd' => 'Add Publication',
            'formTitleEdit' => 'Edit Publication',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}" class="w-75">
                <div class="form-group">
                    <label for="pub_publication_type_id">Publication Type <span class="text-danger">*</span></label>
                    <select id="pub_publication_type_id" class="form-control @error('publication_type_id') is-invalid @enderror" wire:model="publication_type_id">
                        <option value="">— Select —</option>
                        @foreach($this->publicationTypeOptions as $opt)
                            <option value="{{ $opt->id }}">{{ $opt->publication_type }}</option>
                        @endforeach
                    </select>
                    @error('publication_type_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="pub_name">Name <span class="text-danger">*</span></label>
                    <input type="text" id="pub_name" class="form-control @error('name') is-invalid @enderror" wire:model.live="name">
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="pub_code">Code</label>
                    <input type="text" id="pub_code" class="form-control bg-light" readonly value="{{ $this->displayCode }}" placeholder="Auto-generated from name">
                </div>
                <div class="form-group">
                    <label for="pub_description">Description</label>
                    <textarea id="pub_description" class="form-control" rows="3" wire:model="description"></textarea>
                    @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="pub_status" wire:model="status">
                        <label class="custom-control-label" for="pub_status">Active</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ $showEditModal ? 'Update' : 'Save' }}</button>
                <button type="button" class="btn btn-secondary" wire:click="backFromForm">Cancel</button>
            </form>
        @endcomponent
    @endif

    {{-- View (read-only) --}}
    @if($showViewModal && $this->viewRecord)
        @php $viewRecord = $this->viewRecord; @endphp
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'View Publication'])
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $viewRecord->name }}</dd>
                <dt class="col-sm-3">Publication Type</dt>
                <dd class="col-sm-9">{{ $viewRecord->publicationType?->publication_type ?? '—' }}</dd>
                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $viewRecord->description ?: '—' }}</dd>
                <dt class="col-sm-3">Active</dt>
                <dd class="col-sm-9">{{ $viewRecord->status ? 'Active' : 'Inactive' }}</dd>
                <dt class="col-sm-3">Created</dt>
                <dd class="col-sm-9">{{ $viewRecord->created_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                <dt class="col-sm-3">Updated</dt>
                <dd class="col-sm-9">{{ $viewRecord->updated_at?->format('M j, Y g:i A') ?? '—' }}</dd>
            </dl>
        @endcomponent
    @endif
</div>

{{-- Shared master data JS: DataTable, delete confirmation, toasts — see public/js/masterapp/master-data-livewire.js --}}

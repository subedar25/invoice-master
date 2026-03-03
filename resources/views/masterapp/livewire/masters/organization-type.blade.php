<div wire:key="organization-type" id="master-list">
    {{-- List screen --}}
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @if($items->isNotEmpty())
            @component('masterapp.livewire.masters.components.list-card', [
                'title' => 'Organization Types',
                'addButtonText' => 'Add Organization Type',
                'tableId' => 'masterTable',
                'orderCol' => '2',
                'nonOrderableTargets' => '3,4',
            ])
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Created Date</th>
                        <th>Active</th>
                        <th class="master-table-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->parent?->name ?? '—' }}</td>
                            <td>{{ $item->created_at?->format('M j, Y') ?? '—' }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox"
                                           class="custom-control-input"
                                           id="status_toggle_{{ $item->id }}"
                                           @if($item->active) checked @endif
                                           wire:change="toggleStatus({{ $item->id }})">
                                    <label class="custom-control-label" for="status_toggle_{{ $item->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <div class="action-div master-actions">
                                    <a href="#" wire:click.prevent="openViewModal({{ $item->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                    <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                    <a href="#" data-master-delete-id="{{ $item->id }}" data-master-delete-title="Delete Organization Type?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            @endcomponent
        @else
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Organization Types</h5>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-primary btn-sm" wire:click.prevent="openCreateModal">
                            <i class="fa fa-plus"></i> Add Organization Type
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted mb-0">No organization types found.</p>
                </div>
            </div>
        @endif
    @endif

    {{-- Add/Edit form --}}
    @if($showCreateModal || $showEditModal)
        @component('masterapp.livewire.masters.components.form-card', [
            'formTitleAdd' => 'Add Organization Type',
            'formTitleEdit' => 'Edit Organization Type',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}" class="w-75">
                <div class="form-group">
                    <label for="org_type_name">Name <span class="text-danger">*</span></label>
                    <input type="text" id="org_type_name" class="form-control @error('name') is-invalid @enderror" wire:model.live="name">
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <input type="hidden" id="org_type_code" class="form-control bg-light" readonly value="{{ $this->displayCode }}" placeholder="Auto-generated from name">
                </div>
                <div class="form-group">
                    <label for="org_type_parent">Parent</label>
                    <select id="org_type_parent" class="form-control" wire:model="parent_id">
                        <option value="">— None —</option>
                        @foreach($this->parentOptions as $opt)
                            <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="org_type_description">Description</label>
                    <textarea id="org_type_description" class="form-control" rows="2" wire:model="description"></textarea>
                    @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="org_type_status" wire:model="status">
                        <label class="custom-control-label" for="org_type_status">Active</label>
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
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'View Organization Type'])
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $viewRecord->name }}</dd>
                <dt class="col-sm-3">Parent</dt>
                <dd class="col-sm-9">{{ $viewRecord->parent?->name ?? '—' }}</dd>
                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $viewRecord->description ?: '—' }}</dd>
                <dt class="col-sm-3">Active</dt>
                <dd class="col-sm-9">{{ $viewRecord->active ? 'Yes' : 'No' }}</dd>
                <dt class="col-sm-3">Created</dt>
                <dd class="col-sm-9">{{ $viewRecord->created_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                <dt class="col-sm-3">Updated</dt>
                <dd class="col-sm-9">{{ $viewRecord->updated_at?->format('M j, Y g:i A') ?? '—' }}</dd>
            </dl>
            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center py-2 px-3 mb-3 rounded bg-light border" style="border-left: 3px solid #007bff !important;">
                <h6 class="mb-0 d-flex align-items-center">
                    <i class="fa fa-sitemap text-muted mr-2" aria-hidden="true"></i>
                    <span>Child Organization Types</span>
                    <span class="badge badge-secondary font-weight-normal ml-2">{{ $viewRecord->children->count() }}</span>
                </h6>
                <button type="button" class="btn btn-primary btn-sm" wire:click.prevent="openCreateChildModal({{ $viewRecord->id }})">
                    <i class="fa fa-plus"></i> Add Child Type
                </button>
            </div>
            @if($viewRecord->children->isNotEmpty())
                <div class="table-responsive">
                    <table id="childMasterTable" class="table table-bordered table-hover table-sm js-master-child-datatable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Created Date</th>
                                <th>Active</th>
                                <th class="master-table-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($viewRecord->children as $child)
                                <tr>
                                    <td>{{ $child->name }}</td>
                                    <td>{{ $child->created_at?->format('M j, Y') ?? '—' }}</td>
                                    <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="view_child_status_toggle_{{ $child->id }}" @if($child->active) checked @endif wire:change="toggleStatus({{ $child->id }})">
                                            <label class="custom-control-label" for="view_child_status_toggle_{{ $child->id }}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-div master-actions">
                                            <a href="#" wire:click.prevent="openViewModal({{ $child->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                            <a href="#" wire:click.prevent="openEditFromView({{ $child->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                            <a href="#" data-master-delete-id="{{ $child->id }}" data-master-delete-title="Delete Organization Type?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small mb-0">No child types. Click <strong>Add Child Type</strong> to add one under this parent.</p>
            @endif
        @endcomponent
    @endif
</div>

{{-- Shared master data JS: DataTable, delete confirmation, toasts — see public/js/masterapp/master-data-livewire.js --}}

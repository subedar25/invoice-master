<div wire:key="department" id="master-list">
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @if($items->isNotEmpty())
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">Departments</h5>
                    </div>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-primary btn-sm" wire:click.prevent="openCreateModal">
                            <i class="fa fa-plus"></i> Add Department
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="departmentMasterTable" class="table table-bordered table-hover table-sm js-master-datatable" data-order-col="1" data-non-orderable-targets="2">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Created Date</th>
                                    <th class="master-table-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr @class(['text-muted' => (bool) $item->deleted_at])>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->created_at?->format('M j, Y') ?? '—' }}</td>
                                        <td>
                                            <div class="action-div master-actions">
                                                <a href="#" wire:click.prevent="openViewModal({{ $item->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                                @if(!$item->deleted_at)
                                                    <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                                    <a href="#" data-master-delete-id="{{ $item->id }}" data-master-delete-title="Delete Department?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                                @elseif((auth()->user()?->user_type ?? '') === 'systemuser')
                                                    <a href="#" wire:click.prevent="restoreById({{ $item->id }})" title="Revert" class="action-icon entity-link"><i class="fa fa-undo" aria-hidden="true"></i></a>
                                                @endif
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
                        <h5 class="mb-1">Departments</h5>
                    </div>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-primary btn-sm" wire:click.prevent="openCreateModal">
                            <i class="fa fa-plus"></i> Add Department
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted mb-0">No departments found.</p>
                </div>
            </div>
        @endif
    @endif

    @if($showCreateModal || $showEditModal)
        @component('masterapp.livewire.masters.components.form-card', [
            'formTitleAdd' => 'Add Department',
            'formTitleEdit' => 'Edit Department',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}" class="w-75">
                <div class="form-group">
                    <label for="dept_name">Name <span class="text-danger">*</span></label>
                    <input type="text" id="dept_name" class="form-control @error('name') is-invalid @enderror" wire:model.live="name">
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <input type="hidden" wire:model="organization_id">
                    @error('organization_id') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="dept_description">Description</label>
                    <textarea id="dept_description" class="form-control @error('description') is-invalid @enderror" rows="3" wire:model="description"></textarea>
                    @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary">{{ $showEditModal ? 'Update' : 'Save' }}</button>
                <button type="button" class="btn btn-secondary" wire:click="backFromForm">Cancel</button>
            </form>
        @endcomponent
    @endif

    @if($showViewModal && $this->viewRecord)
        @php $viewRecord = $this->viewRecord; @endphp
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'View Department'])
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $viewRecord->name }}</dd>

                <dt class="col-sm-3">Parent</dt>
                <dd class="col-sm-9">{{ $viewRecord->parent?->name ?? '—' }}</dd>

                <dt class="col-sm-3">Organization</dt>
                <dd class="col-sm-9">{{ $viewRecord->organization?->name ?? '—' }}</dd>

                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $viewRecord->description ?: '—' }}</dd>

                <dt class="col-sm-3">Created</dt>
                <dd class="col-sm-9">{{ $viewRecord->created_at?->format('M j, Y g:i A') ?? '—' }}</dd>

                <dt class="col-sm-3">Updated</dt>
                <dd class="col-sm-9">{{ $viewRecord->updated_at?->format('M j, Y g:i A') ?? '—' }}</dd>
            </dl>

            <hr class="my-4">
            <h6 class="mb-2">Child Departments</h6>
            @if($viewRecord->children->isNotEmpty())
                <ul class="mb-3 pl-3">
                    @foreach($viewRecord->children as $child)
                        <li>{{ $child->name }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted small">No child departments.</p>
            @endif

            <h6 class="mb-2">Users</h6>
            @if($viewRecord->users->isNotEmpty())
                <ul class="mb-0 pl-3">
                    @foreach($viewRecord->users as $user)
                        <li>{{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->email ?? 'User') }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted small mb-0">No users assigned.</p>
            @endif
        @endcomponent
    @endif
</div>

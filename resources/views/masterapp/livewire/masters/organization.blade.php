<div wire:key="organization-master-root">
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @component('masterapp.livewire.masters.components.list-card', [
            'title' => 'Organizations',
            'addButtonText' => 'Add Organization',
            'tableId' => 'organizationMasterTable',
            'orderCol' => '0',
            'nonOrderableTargets' => '2,3',
        ])
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Created Date</th>
                    <th>Active</th>
                    <th class="master-table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr @class(['text-muted' => (bool) $item->deleted_at])>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->created_date ? $item->created_date->format('M j, Y') : '—' }}</td>
                        <td>
                            @if($item->deleted_at)
                                <span class="badge badge-secondary">Deleted</span>
                            @else
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="status_toggle_{{ $item->id }}" @if($item->status) checked @endif wire:change="toggleStatus({{ $item->id }})">
                                    <label class="custom-control-label" for="status_toggle_{{ $item->id }}"></label>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="action-div master-actions">
                                <a href="#" wire:click.prevent="openViewModal({{ $item->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                @if(!$item->deleted_at)
                                    <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                    <a href="#" data-master-delete-id="{{ $item->id }}" data-master-delete-title="Delete Organization?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                @else
                                    <a href="#" wire:click.prevent="restoreById({{ $item->id }})" title="Revert" class="action-icon entity-link"><i class="fa fa-undo" aria-hidden="true"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        @endcomponent
    @endif

    @if($showCreateModal || $showEditModal)
        @component('masterapp.livewire.masters.components.form-card', [
            'formTitleAdd' => 'Add Organization',
            'formTitleEdit' => 'Edit Organization',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}" class="w-75">
                <div class="form-group">
                    <label for="org_name">Name <span class="text-danger">*</span></label>
                    <input type="text" id="org_name" class="form-control @error('name') is-invalid @enderror" wire:model.live="name">
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="org_address">Address</label>
                    <textarea id="org_address" class="form-control @error('address') is-invalid @enderror" rows="3" wire:model="address" placeholder="Street, city, state, postal code…"></textarea>
                    @error('address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="org_description">Description</label>
                    <textarea id="org_description" class="form-control @error('description') is-invalid @enderror" rows="3" wire:model="description"></textarea>
                    @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="org_invoice_prefix">Invoice Prefix</label>
                    <input type="text" id="org_invoice_prefix" class="form-control @error('invoice_prefix') is-invalid @enderror" wire:model="invoice_prefix">
                    @error('invoice_prefix') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="org_theme">Theme <span class="text-danger">*</span></label>
                    <select id="org_theme" class="form-control @error('theme') is-invalid @enderror" wire:model.live="theme">
                        @foreach(($themeOptions ?? []) as $folder => $label)
                            <option value="{{ $folder }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('theme') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    <small class="form-text text-muted">Controls stylesheet bundle for this organization when it is selected in the header.</small>
                </div>

                <div class="form-group">
                    <label for="org_logo">Logo</label>
                    <input type="file" id="org_logo" class="form-control-file @error('logo') is-invalid @enderror" wire:model="logo">
                    <div wire:loading wire:target="logo" class="text-primary small mt-1">Uploading...</div>
                    @error('logo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    
                    @if ($logo)
                        <div class="mt-2 border rounded p-1 d-inline-block position-relative">
                            <img src="{{ $logo->temporaryUrl() }}" style="max-height: 80px;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: -10px; right: -10px; border-radius: 50%; padding: 2px 6px;" wire:click="removeLogo" title="Remove Selection">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    @elseif ($existingLogo)
                        <div class="mt-2 border rounded p-1 d-inline-block position-relative">
                            <img src="{{ asset($existingLogo) }}" style="max-height: 80px;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: -10px; right: -10px; border-radius: 50%; padding: 2px 6px;" wire:click="removeLogo" title="Delete Logo">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="org_status" wire:model="status">
                        <label class="custom-control-label" for="org_status">Active</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ $showEditModal ? 'Update' : 'Save' }}</button>
                <button type="button" class="btn btn-secondary" wire:click="backFromForm">Cancel</button>
            </form>
        @endcomponent
    @endif

    @if($showViewModal && ($viewRecord = \App\Models\Organization::withTrashed()->find($viewId)))
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'View Organization'])
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $viewRecord->name }}</dd>

                <dt class="col-sm-3">Address</dt>
                <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $viewRecord->address ?: '—' }}</dd>

                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $viewRecord->description ?: '—' }}</dd>

                <dt class="col-sm-3">Invoice Prefix</dt>
                <dd class="col-sm-9">{{ $viewRecord->invoice_prefix ?: '—' }}</dd>

                <dt class="col-sm-3">Theme</dt>
                <dd class="col-sm-9">{{ ($themeOptions[$viewRecord->theme] ?? null) ?: ($viewRecord->theme ?: '—') }}</dd>

                <dt class="col-sm-3">Logo</dt>
                <dd class="col-sm-9">
                    @if($viewRecord->logo)
                        <img src="{{ asset($viewRecord->logo) }}" class="img-thumbnail" style="max-height: 60px;">
                    @else
                        —
                    @endif
                </dd>

                <dt class="col-sm-3">Active</dt>
                <dd class="col-sm-9">{{ $viewRecord->status ? 'Yes' : 'No' }}</dd>

                <dt class="col-sm-3">Created</dt>
                <dd class="col-sm-9">{{ $viewRecord->created_date ? $viewRecord->created_date->format('M j, Y g:i A') : '—' }}</dd>

                <dt class="col-sm-3">Last edited</dt>
                <dd class="col-sm-9">{{ $viewRecord->edited_date ? $viewRecord->edited_date->format('M j, Y g:i A') : '—' }}</dd>
            </dl>

            <hr class="my-4">
            <h6 class="mb-2">Departments</h6>
            @php $depts = \App\Models\Department::where('organization_id', $viewRecord->id)->get(); @endphp
            @if($depts->isNotEmpty())
                <ul class="mb-0 pl-3">
                    @foreach($depts as $dept)
                        <li>{{ $dept->name }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted small mb-0">No departments assigned.</p>
            @endif
        @endcomponent
    @endif
</div>
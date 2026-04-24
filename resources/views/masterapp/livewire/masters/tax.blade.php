<div wire:key="tax-master" id="master-list">
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @component('masterapp.livewire.masters.components.list-card', [
            'title' => 'Taxes',
            'addButtonText' => 'Add Tax',
            'tableId' => 'taxMasterTable',
            'orderCol' => '2',
            'nonOrderableTargets' => '3,4',
        ])
            <thead>
                <tr>
                    <th>Tax Name</th>
                    <th>Tax Value (%)</th>
                    <th>Created Date</th>
                    <th>Status</th>
                    <th class="master-table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr @class(['text-muted' => (bool) $item->deleted_at])>
                        <td>{{ $item->tax_name }}</td>
                        <td>{{ number_format((float) $item->tax_value, 2) }}</td>
                        <td>{{ $item->created_at?->format('M j, Y') ?? '—' }}</td>
                        <td>
                            @if($item->deleted_at)
                                <span class="badge badge-secondary">Deleted</span>
                            @else
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="tax_status_{{ $item->id }}" @checked($item->tax_status) wire:change="toggleStatus({{ $item->id }})">
                                    <label class="custom-control-label" for="tax_status_{{ $item->id }}"></label>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="action-div master-actions">
                                <a href="#" wire:click.prevent="openViewModal({{ $item->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                @if(!$item->deleted_at)
                                    <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                    <a href="#" data-master-delete-id="{{ $item->id }}" data-master-delete-title="Delete Tax?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                @elseif((auth()->user()?->user_type ?? '') === 'systemuser')
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
            'formTitleAdd' => 'Add Tax',
            'formTitleEdit' => 'Edit Tax',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}" class="w-75">
                <div class="form-group">
                    <label for="tax_name">Tax Name <span class="text-danger">*</span></label>
                    <input type="text" id="tax_name" class="form-control @error('tax_name') is-invalid @enderror" wire:model.live="tax_name">
                    @error('tax_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="tax_value">Tax Value (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="100" id="tax_value" class="form-control @error('tax_value') is-invalid @enderror" wire:model.live="tax_value">
                    @error('tax_value') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="tax_status">Status</label>
                    <select id="tax_status" class="form-control @error('tax_status') is-invalid @enderror" wire:model="tax_status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    @error('tax_status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary">{{ $showEditModal ? 'Update' : 'Save' }}</button>
                <button type="button" class="btn btn-secondary" wire:click="backFromForm">Cancel</button>
            </form>
        @endcomponent
    @endif

    @if($showViewModal && $this->viewRecord)
        @php $viewRecord = $this->viewRecord; @endphp
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'View Tax'])
            <dl class="row mb-0">
                <dt class="col-sm-3">Tax Name</dt>
                <dd class="col-sm-9">{{ $viewRecord->tax_name }}</dd>

                <dt class="col-sm-3">Tax Value</dt>
                <dd class="col-sm-9">{{ number_format((float) $viewRecord->tax_value, 2) }}%</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{{ $viewRecord->tax_status ? 'Active' : 'Inactive' }}</dd>

                <dt class="col-sm-3">Created</dt>
                <dd class="col-sm-9">{{ $viewRecord->created_at?->format('M j, Y g:i A') ?? '—' }}</dd>

                <dt class="col-sm-3">Updated</dt>
                <dd class="col-sm-9">{{ $viewRecord->updated_at?->format('M j, Y g:i A') ?? '—' }}</dd>
            </dl>
        @endcomponent
    @endif
</div>

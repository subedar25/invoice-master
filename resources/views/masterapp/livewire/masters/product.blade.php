<div wire:key="product-master" id="master-list">
    @if(!$showCreateModal && !$showEditModal && !$showViewModal)
        @component('masterapp.livewire.masters.components.list-card', [
            'title' => 'Products',
            'addButtonText' => 'Add Product',
            'tableId' => 'productMasterTable',
            'orderCol' => '4',
            'nonOrderableTargets' => '5,6',
        ])
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>HSN</th>
                    <th>Unit Price</th>
                    <th>GST (%)</th>
                    <th>Final Price</th>
                    <th>Status</th>
                    <th class="master-table-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr @class(['text-muted' => (bool) $item->deleted_at])>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->hsn ?: '—' }}</td>
                        <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td>{{ number_format((float) $item->total_gst, 2) }}</td>
                        <td>{{ number_format((float) $item->final_price, 2) }}</td>
                        <td>
                            @if($item->deleted_at)
                                <span class="badge badge-secondary">Deleted</span>
                            @else
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="product_status_{{ $item->id }}" @checked($item->status) wire:change="toggleStatus({{ $item->id }})">
                                    <label class="custom-control-label" for="product_status_{{ $item->id }}"></label>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="action-div master-actions">
                                <a href="#" wire:click.prevent="openViewModal({{ $item->id }})" title="View" class="action-icon entity-link"><i class="fa fa-eye"></i></a>
                                @if(!$item->deleted_at)
                                    @can('edit-product')
                                    <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" title="Edit" class="action-icon entity-link"><i class="fa fa-edit"></i></a>
                                    @endcan
                                    @can('delete-product')
                                    <a href="#" data-master-delete-id="{{ $item->id }}" data-master-delete-title="Delete Product?" title="Delete" class="action-icon entity-link master-delete-link"><i class="fa fa-trash"></i></a>
                                    @endcan
                                @elseif((auth()->user()?->user_type ?? '') === 'systemuser')
                                    <a href="#" wire:click.prevent="restoreById({{ $item->id }})" title="Revert" class="action-icon entity-link"><i class="fa fa-undo"></i></a>
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
            'formTitleAdd' => 'Add Product',
            'formTitleEdit' => 'Edit Product',
            'showEditModal' => $showEditModal,
        ])
            <form wire:submit.prevent="{{ $showEditModal ? 'saveEdit' : 'saveCreate' }}" class="w-75">
                <div class="form-group">
                    <label for="product_name">Product Name <span class="text-danger">*</span></label>
                    <input type="text" id="product_name" class="form-control @error('name') is-invalid @enderror" wire:model.live="name">
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="product_unit_price">Unit Price <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" id="product_unit_price" class="form-control @error('unit_price') is-invalid @enderror" wire:model.live="unit_price">
                        @error('unit_price') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="product_hsn">HSN</label>
                        <input type="text" id="product_hsn" class="form-control @error('hsn') is-invalid @enderror" wire:model.live="hsn">
                        @error('hsn') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="product_cgst">CGST (%)</label>
                        <input type="number" step="0.01" min="0" id="product_cgst" class="form-control @error('cgst') is-invalid @enderror" wire:model.live="cgst">
                        @error('cgst') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="product_sgst">SGST (%)</label>
                        <input type="number" step="0.01" min="0" id="product_sgst" class="form-control @error('sgst') is-invalid @enderror" wire:model.live="sgst">
                        @error('sgst') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="product_total_gst">Total GST (%)</label>
                        <input type="text" id="product_total_gst" class="form-control @error('total_gst') is-invalid @enderror bg-light" wire:model="total_gst" readonly>
                        @error('total_gst') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="product_final_price">Final Price</label>
                        <input type="text" id="product_final_price" class="form-control @error('final_price') is-invalid @enderror bg-light" wire:model="final_price" readonly>
                        @error('final_price') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="product_status">Status</label>
                        <select id="product_status" class="form-control @error('status') is-invalid @enderror" wire:model="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ $showEditModal ? 'Update' : 'Save' }}</button>
                <button type="button" class="btn btn-secondary" wire:click="backFromForm">Cancel</button>
            </form>
        @endcomponent
    @endif

    @if($showViewModal && $this->viewRecord)
        @php $viewRecord = $this->viewRecord; @endphp
        @component('masterapp.livewire.masters.components.view-card', ['viewTitle' => 'View Product'])
            <dl class="row mb-0">
                <dt class="col-sm-3">Product Name</dt>
                <dd class="col-sm-9">{{ $viewRecord->name }}</dd>

                <dt class="col-sm-3">Organization</dt>
                <dd class="col-sm-9">{{ $viewRecord->organization?->name ?? '—' }}</dd>

                <dt class="col-sm-3">HSN</dt>
                <dd class="col-sm-9">{{ $viewRecord->hsn ?: '—' }}</dd>

                <dt class="col-sm-3">Unit Price</dt>
                <dd class="col-sm-9">{{ number_format((float) $viewRecord->unit_price, 2) }}</dd>

                <dt class="col-sm-3">CGST</dt>
                <dd class="col-sm-9">{{ number_format((float) $viewRecord->cgst, 2) }}%</dd>

                <dt class="col-sm-3">SGST</dt>
                <dd class="col-sm-9">{{ number_format((float) $viewRecord->sgst, 2) }}%</dd>

                <dt class="col-sm-3">Total GST</dt>
                <dd class="col-sm-9">{{ number_format((float) $viewRecord->total_gst, 2) }}%</dd>

                <dt class="col-sm-3">Final Price</dt>
                <dd class="col-sm-9">{{ number_format((float) $viewRecord->final_price, 2) }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{{ $viewRecord->status ? 'Active' : 'Inactive' }}</dd>
            </dl>
        @endcomponent
    @endif
</div>

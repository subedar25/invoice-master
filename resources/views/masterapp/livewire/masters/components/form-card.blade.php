@php
    $formTitleAdd = $formTitleAdd ?? 'Add';
    $formTitleEdit = $formTitleEdit ?? 'Edit';
    $showEditModal = $showEditModal ?? false;
@endphp
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $showEditModal ? $formTitleEdit : $formTitleAdd }}</h5>
        <div class="ml-auto">
            <button type="button" class="btn btn-sm btn-secondary" wire:click="backFromForm">
                <i class="fa fa-arrow-left"></i> Back to List
            </button>
        </div>
    </div>
    <div class="card-body">
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible small mb-3">
                {{ session('message') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif
        <div class="d-flex">
            {{ $slot }}
        </div>
    </div>
</div>

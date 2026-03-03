@php
    $viewTitle = $viewTitle ?? 'View';
@endphp
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $viewTitle }}</h5>
        <div class="ml-auto">
            <button type="button" class="btn btn-sm btn-secondary" wire:click="closeModals">
                <i class="fa fa-arrow-left"></i> Back to List
            </button>
        </div>
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
</div>

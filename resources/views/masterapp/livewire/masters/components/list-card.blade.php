@php
    $title = $title ?? 'List';
    $addButtonText = $addButtonText ?? 'Add';
    $tableId = $tableId ?? 'masterTable';
    $orderCol = $orderCol ?? '1';
    $orderDir = $orderDir ?? 'desc';
    $nonOrderableTargets = $nonOrderableTargets ?? '2,3';
@endphp
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $title }}</h5>
        <div class="ml-auto">
            <button type="button" class="btn btn-primary btn-sm" wire:click.prevent="openCreateModal">
                <i class="fa fa-plus"></i> {{ $addButtonText }}
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="master-toolbar">
            <div class="master-toolbar__filters"></div>
        </div>
        <div class="table-responsive">
            <table id="{{ $tableId }}" class="table table-bordered table-hover table-sm js-master-datatable" data-order-col="{{ $orderCol }}" data-order-dir="{{ $orderDir }}" data-non-orderable-targets="{{ $nonOrderableTargets }}">
                {{ $slot }}
            </table>
        </div>
    </div>
</div>

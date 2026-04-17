@extends('masterapp.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Organizations</h1>
            </div>
            <div class="col-sm-6 d-flex justify-content-end">
                <button type="button" class="btn btn-default mr-2" id="toggleFilterBtn">
                    <i class="fa fa-filter"></i> Filter
                </button>
                @can('create-organization')
                <a href="{{ route('masterapp.organizations.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus mr-1"></i> Add Organization
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('masterapp.organizations._searchfilters')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end mb-3" id="organization-search-wrap">
                            <div class="search-input-wrapper">
                                <i class="fa fa-search"></i>
                                <input type="search" id="customSearchInput" class="form-control search-input" placeholder="Search..">
                            </div>
                        </div>
                        <table id="dataTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Organization Types</th>
                                    <th>Seasons</th>
                                    <th>Open</th>
                                    <th>Active</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/organization.js') }}"></script>
<script>
$(function () {
    var exportCols = [0, 1, 2, 3, 4];
    var exportOpts = {
        columns: exportCols,
        format: {
            body: function (data, row, column, node) {
                return $(node).text().trim() || data;
            }
        }
    };

    CRUDManager.init({
        resource: 'Organizations',
        serverSide: {
            url: "{{ route('masterapp.organizations.data') }}",
            columns: [
                { data: 'name', name: 'name' },
                { data: 'organization_types', name: 'organization_types', orderable: false, searchable: false },
                { data: 'seasons', name: 'seasons', orderable: false, searchable: false },
                { data: 'open', name: 'open' },
                { data: 'active', name: 'active' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center no-vis' }
            ],
            columnDefs: [
                { targets: -1, orderable: false, searchable: false, className: 'text-center no-vis' }
            ]
        },
        order: [[0, 'asc']],
        buttons: [
            { extend: 'print', title: '{{ config('app.name', 'Invoice Masters') }} - Organizations', text: '<i class="fa fa-print"></i> Print', className: 'btn btn-secondary', exportOptions: exportOpts },
            { extend: 'copyHtml5', title: '{{ config('app.name', 'Invoice Masters') }} - Organizations', text: '<i class="fa fa-copy"></i> Copy Data', className: 'btn btn-primary', exportOptions: exportOpts },
            { extend: 'excelHtml5', title: '{{ config('app.name', 'Invoice Masters') }} - Organizations', filename: '{{ config('app.name', 'Invoice Masters') }} - Organizations', text: '<i class="fa fa-download"></i> Excel', className: 'btn btn-success', exportOptions: exportOpts },
            { extend: 'pdfHtml5', title: '{{ config('app.name', 'Invoice Masters') }} - Organizations', filename: '{{ config('app.name', 'Invoice Masters') }} - Organizations', text: '<i class="fa fa-download"></i> PDF', className: 'btn btn-danger', orientation: 'landscape', pageSize: 'A4', exportOptions: exportOpts },
            { extend: 'colvis', text: '<i class="fa fa-columns"></i> Column visibility', className: 'btn btn-warning', columns: ':not(.no-vis)' }
        ],
        filterInputs: [
            { id: 'customSearchInput', name: 'organization_search', type: 'text' },
            { id: 'filter_active', name: 'active', type: 'manual' }
        ]
    });

    if (typeof window.OrganizationIndex !== 'undefined') {
        window.OrganizationIndex.init({ successMessage: @json(session('success')) });
    }
});
</script>
@endpush

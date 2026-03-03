@extends('layouts.custom-admin')

@section('title', 'Drop Points Management', 'bold')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <!-- The table is now empty. DataTables will populate it. -->
            <!-- We add an ID for the DataTables initialization -->
            <table id="dataTable" class="table table-bordered table-hover w-100 table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Name</th>
                        <th>client</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <!-- The <tbody> can be empty or omitted -->
            </table>
        </div>
    </div>
</div>

<!-- Generic Modal -->
@include('partials.generic-model')
@endsection
@push('scripts')
<!-- Include the generic CRUD manager -->
<script src="{{ asset('js/generic-datatable.js') }}"></script>
<script src="{{ asset('js/ajax-form-handler.js') }}"></script>

<script>

     $.fn.dataTable.ext.errMode = 'none';
     
    $(document).ready(function() {

        // 1. Global AJAX Error Handler - Catches 403s from all AJAX calls
      $(document).ajaxError(function(event, xhr, settings, thrownError) {
            // Store the route URL in a variable for clarity
            const moduleDataRoute = "{{ route('droppoints.getdata') }}";

            // Check if the failed request's URL CONTAINS our route's URL.
            // settings.indexOf() returns -1 if the substring is not found.
            if (settings.url.indexOf(moduleDataRoute) !== -1) {
                
                console.log('Caught error for droppoints data:', xhr); // For debugging

                let errorMessage = '';
                let alertClass = 'alert-danger'; // Default to danger

                // Customize the message and alert type based on the status code
                if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to view  data.';
                } else if (xhr.status === 404) {
                    errorMessage = 'The data endpoint could not be found.';
                    alertClass = 'alert-warning';
                } else if (xhr.status >= 500) {
                    errorMessage = 'A server error occurred. Please try again later.';
                } else {
                    errorMessage = 'An unknown error occurred while loading the data.';
                }

                // Create the HTML for our custom error message div
                const errorHtml = `
                    <div class="alert ${alertClass} text-center m-3" role="alert">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        <strong>Error:44</strong> ${errorMessage}
                    </div>
                `;

                // Replace the DataTable's content with our error message
                $('#dataTable_wrapper').html(errorHtml);
            }
        });

        // 2. Initialize the CRUD Manager
        CRUDManager.init({
            resource: 'droppoints',
            serverSide: {
                url: "{{ route('droppoints.getdata') }}",
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'address_line1',
                        name: 'address_line1',
                        orderable: false,
                        searchable: false
                    }
                ],
              
            },
            endpoints: {
                create: "/droppoints/create",
                edit: "/droppoints/{id}/edit",
                delete: "/droppoints/bulk-delete"
            },
            buttons: [
                 @can('create-dropoint')
                {
                    text: '<i class="fa fa-plus"></i> New',
                    className: 'btn btn-success',
                    action: 'create',
                    requireSelection: false
                },
                @endcan
                @can('edit-dropoint')
                {
                    text: '<i class="fa fa-edit"></i> Edit',
                    className: 'btn btn-primary buttons-edit',
                    action: 'edit',
                    requireSingle: true
                },
                @endcan
                @can('delete-dropoint')
                {
                    text: '<i class="fa fa-trash"></i> Delete',
                    className: 'btn btn-danger buttons-del',
                    action: 'delete',
                    requireSelection: true
                },
                @endcan
                 {
                extend: 'colvis',
                text: '<i class="fa fa-columns"></i> Columns'
                },
                {
                    extend: 'copy',
                    text: '<i class="fa fa-copy"></i> Copy'
                },
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel"></i> Excel'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf"></i> PDF'
                }
            ]
        });

        // The rest of your form handlers remain the same
        // The global ajaxError handler will catch any 403s from them.
        handleAjaxForm("#form-droppoint", {
            loadingIndicator: 'button',
            buttonTextSelector: '#btn-text',
            buttonSpinnerSelector: '#btn-spinner',
            modalToClose: "#genericModal",
            reloadOnSuccess: true
        });

        handleAjaxForm("#form-edit-droppoint", {
            loadingIndicator: 'button',
            buttonTextSelector: '#btn-edit-text',
            buttonSpinnerSelector: '#btn-edit-spinner',
            successTitle: "Module Updated!",
            reloadOnSuccess: true
        });
    });
</script>
@endpush
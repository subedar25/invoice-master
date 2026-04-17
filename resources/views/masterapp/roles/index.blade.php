@extends('masterapp.layouts.app')
@section('content')

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Roles</h1>
      </div>
      <div class="col-sm-6 d-flex justify-content-end add-new">

       <button type="button" class="btn btn-default ml-2" id="toggleFilterBtn">
          <i class="fa fa-filter"></i> Filter
        </button>
        &nbsp;
          @can('create-role')
        <button type="button" class="btn btn-primary" id="addModuleBtn"
          data-url="{{ route('masterapp.roles.create') }}"
          data-title="Add New Role">
          <i class="fa fa-plus"></i> Add Role
        </button>
          @endcan
      </div>
    </div>
  </div>
</div>
<!-- Main content -->
<section class="content">
  <div class="container-fluid">

   @include('masterapp.roles._searchfilters')
    <div class="row">
      <div class="col-12">
        <div class="card">

          <!-- /.card-header -->
          <div class="card-body">
            <div class="d-flex justify-content-end mb-3" id="roles-search-wrap">
              <div class="search-input-wrapper">
                <i class="fa fa-search"></i>
                <input type="search" id="customSearchInput" class="form-control search-input" placeholder="Search..">
              </div>
            </div>
            <table id="dataTable" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <!-- <th><input type="checkbox" id="selectAll"></th> -->
                  <th class="d-none">ID</th>
                  <th> Role Name</th>
                  <th>Department</th>
                  <th>Active</th>
                  <th>Module /Permissions</th>
                  <th>Actions</th>
                </tr>
              </thead>


              </tfoot>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div>
</section>


<!-- Generic Modal -->
@include('partials.generic-model')
@endsection

@push('scripts')

 <script src="{{ asset('js/permission-checkboxes.js') }}" defer></script>
<script>

     $.fn.dataTable.ext.errMode = 'none';


     $(document).ready(function() {


         $(document).ajaxError(function(event, xhr, settings, thrownError) {
            // Store the route URL in a variable for clarity
            const moduleDataRoute = "{{ route('masterapp.roles.data') }}";

            if (settings.url.indexOf(moduleDataRoute) !== -1) {

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
                        <strong>Error:</strong> ${errorMessage}
                    </div>
                `;

                // Replace the DataTable's content with our error message
                $('#dataTable_wrapper').html(errorHtml);
            }
        });

     ModalFormManager.init();

    CRUDManager.init({
            resource: 'Roles',
            serverSide: {
                url: "{{ route('masterapp.roles.data') }}",
                columns: [
                    { data: 'id', name: 'id', visible: false },
                    { data: 'name', name: 'name' },
                    { data: 'department', name: 'department.name' },
                    { data: 'status', name: 'is_active', orderable: false, searchable: false },
                    { data: 'permissions', name: 'permissions', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
            },
            order: [[0, 'desc']],
            buttons:[],

            filterInputs: [
                {
                    id: 'customSearchInput',
                    name: 'search',
                    type: 'text'
                },
                {
                    id: 'filter_department_id',
                    name: 'department_id',
                    type: 'manual'
                }
            ],

            endpoints: {
                create: "{{ route('masterapp.roles.create') }}",
                edit: "",
                delete: ""
            },
        });

    $('#customSearchInput').on('search', function () {
        if ($.fn.DataTable.isDataTable('#dataTable')) {
            $('#dataTable').DataTable().draw();
        }
    });

    setTimeout(function() {
        var $top = $('#dataTable_wrapper .top');
        var $buttons = $top.find('.dt-buttons');
        var $searchWrap = $('#roles-search-wrap');
        if ($buttons.length && $searchWrap.length) {
            var $row = $('<div class="btn_filter_align d-flex align-items-center flex-wrap gap-2 w-100"></div>');
            $row.append($buttons);
            $row.append($searchWrap);
            $searchWrap.removeClass('mb-3').addClass('mb-0 ml-auto');
            $top.prepend($row);
        }
    }, 0);

});


// Filter Toggle
    $('#toggleFilterBtn').on('click', function() {
      $('#filterWrapper').slideToggle();
    });

    $('#toggleFilterclear').on('click', function(e) {
      e.preventDefault();
      $('#filterWrapper').slideToggle();
    });

    $('#applyFilterBtn').on('click', function() {
      $('#filter_department_id').val($('#filter_department_ui').val());
      if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().page(0).draw(false);
      }
    });

    $('#clearFilterBtn').on('click', function() {
      $('#filter_department_ui').val('');
      $('#filter_department_id').val('');
      if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().page(0).draw(false);
      }
    });

    $('#addModuleBtn').on('click', function() {
      const url = $(this).data('url');
      const title = $(this).data('title');
      ModalFormManager.openModal(url, title);
    });

    $(document).on('click', '.edit-item', function(e) {
      const url = $(this).data('url');
      const title = $(this).data('title');
      ModalFormManager.openModal(url, title);

    });

    // Permissions column: toggle module to show/hide permissions
    // Use namespaced handlers + collapse events so we don't double-toggle on redraw.
    $(document)
      .off('click.rolePerms', '.role-module-toggle')
      .on('click.rolePerms', '.role-module-toggle', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        var $toggle = $(this);
        var $module = $toggle.closest('.role-perms-module');
        var $list = $module.find('.role-perms-list').first();
        if (!$list.length) return;

        // Guard against duplicate click events (e.g. touch + click),
        // which can cause instant open-then-close behavior.
        var now = Date.now();
        var lastToggleAt = Number($toggle.data('lastToggleAt') || 0);
        if (now - lastToggleAt < 300) return;
        $toggle.data('lastToggleAt', now);

        // Keep module open on click; do not auto-collapse on repeated clicks.
        if (!$list.is(':visible')) {
          $list.stop(true, true).slideDown(120);
        }
        $module.find('.role-module-icon').removeClass('fa-chevron-right').addClass('fa-chevron-down');
        $toggle.attr('aria-expanded', 'true');
      });

// Your form submission handlers remain the same and are correct.
 $(document).ready(function() {
    handleAjaxForm("#form-create-role", {
        loadingIndicator: 'button',
        buttonTextSelector: '#btn-text',
        buttonSpinnerSelector: '#btn-spinner',
        modalToClose: "#genericModal",
        successTitle: "Role Created!",
        reloadOnSuccess: false,
        onSuccess: function () {
            if ($.fn.DataTable.isDataTable('#dataTable')) {
                $('#dataTable').DataTable().ajax.reload(null, false);
            }
        }
    });
});

 $(document).ready(function() {
    handleAjaxForm("#form-edit-role", {
        loadingIndicator: 'button',
        buttonTextSelector: '#btn-text',
        buttonSpinnerSelector: '#btn-spinner',
        modalToClose: "#genericModal",
        successTitle: "Role Updated!",
        reloadOnSuccess: false,
        onSuccess: function () {
            if ($.fn.DataTable.isDataTable('#dataTable')) {
                $('#dataTable').DataTable().ajax.reload(null, false);
            }
        }
    });

     // for delete functionality
    handleDelete();
});

$(document).on('change', '.js-toggle-role-active', function () {
    const checkbox = $(this);
    const roleId = checkbox.data('id');
    const isActive = checkbox.prop('checked');

    $.ajax({
        url: `{{ route('masterapp.roles.toggle-active', ':id') }}`.replace(':id', roleId),
        type: 'PATCH',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function () {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: isActive ? 'Role activated' : 'Role deactivated',
                timer: 2000,
                showConfirmButton: false
            });
        },
        error: function () {
            checkbox.prop('checked', !isActive);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Failed to update role status',
                timer: 3000,
                showConfirmButton: false
            });
        }
    });
});
</script>
@endpush

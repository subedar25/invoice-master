@extends('masterapp.layouts.app')
@section('title', 'Permissions')
@section('content')

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Permissions</h1>
      </div>
      <div class="col-sm-6 d-flex justify-content-end add-new">

        <button type="button" class="btn btn-default ml-2" id="toggleFilterBtn">
          <i class="fa fa-filter"></i> Filter
        </button>
        &nbsp;
        @can('create-permission')
        <button type="button" class="btn btn-primary" id="addpermissionBtn"
          data-url="{{ route('masterapp.permissions.create') }}"
          data-title="Add New Permissions">
          <i class="fa fa-plus"></i> Add Permission
        </button>
        @endcan
      </div>
    </div>
  </div>
</div>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Search filter -->
    @include('masterapp.permissions._searchfilters')
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
              <div class="search-input-wrapper">
                <i class="fa fa-search"></i>
                <input type="search" id="customSearchInput" class="form-control search-input" placeholder="Search..">
              </div>
            </div>
            <table id="permissionstable" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Display Name</th>
                  <th>slug</th>
                  <th>Guard Name</th>
                  <th>Module Name</th>
                  <th>Type</th>
                  <th>Active</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($permissions as $permission)
                <tr data-id="{{ $permission->id }}">
                  <td data-field="name">{{ $permission->name }}</td>
                  <td data-field="email">{{ $permission->display_name }}</td>
                  <td data-field="email">{{ $permission->slug }}</td>
                  <td data-field="email">{{ $permission->guard_name }}</td>
                  <td data-field="module_name">{{ optional($permission->module)->name }}</td>
                  <td data-field="type">{{ ucfirst($permission->type ?? 'public') }}</td>
                  <td data-field="status">
                    @can('activate-deactivate-permission')
                    <div class="text-center">
                      <div class="custom-control custom-switch d-inline-block">
                        <input type="checkbox"
                               class="custom-control-input js-toggle-permission-active"
                               id="permissionActiveSwitch{{ $permission->id }}"
                               data-id="{{ $permission->id }}"
                               {{ $permission->is_active ? 'checked' : '' }}>
                        <label class="custom-control-label" for="permissionActiveSwitch{{ $permission->id }}"></label>
                      </div>
                    </div>
                    @else
                    @if($permission->is_active)
                    <span class="badge badge-success">Active</span>
                    @else
                    <span class="badge badge-secondary">Inactive</span>
                    @endif
                    @endcan
                  </td>
                  <td data-field="actions">
                    <div class="action-div">
                      @can('edit-permission')
                      <button type="button" class="btn btn-link p-0 action-icon edit-item"
                        data-url="{{ route('masterapp.permissions.edit', ['permission' => $permission->id]) }}"
                        data-title="Edit permission"
                        title="Edit permission">
                        <i class="fa fa-edit"></i>
                      </button>
                      @endcan
                      @can('delete-permission')
                      <button type="button"
                        class="btn btn-link p-0 action-icon text-danger delete-item"
                        data-url="{{ route('masterapp.permissions.destroy', ['permission' => $permission->id]) }}"
                        data-name="{{ $permission->name }}"
                        title="Delete permission">
                        <i class="fa fa-trash"></i>
                      </button>
                      @endcan
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Generic Modal -->
@include('masterapp.partials.generic-model')

@push('scripts')
<script>
  // Declare dataTable in a broader scope so it's accessible to other functions
  var dataTable;

  $(function() {
    dataTable = $('#permissionstable').DataTable({
      order: [[0, 'desc']],
      "pageLength": 10,
      responsive: true,
      scrollX: false,
      autoWidth: false,
      lengthMenu: [
        [-1, 10, 50, 100],
        ["All", 10, 50, 100]
      ],
      language: {
        lengthMenu: 'Show _MENU_',
        paginate: {
          next: '<i class="fa  fa-angle-double-right "></i>',
          previous: '<i class="fa  fa-angle-double-left"></i>'
        }
        // Removed search: '' since we're using custom search
      },
      dom: '<"top"Bipl>rt<"bottom bottomAlign"ip><"clear">', // Removed 'f' to hide default search
      buttons: [],
      fixedColumns: {
        rightColumns: 1
      },
      initComplete: function() {
        $('.dataTables_length').appendTo('.dataTables_wrapper .top');
        $('.dataTables_length').addClass('ml-2 d-flex align-items-center');
        var $topContainer = $('.top .dataTables_length').parent();
        $('.top .dataTables_length, .top .dataTables_paginate').wrapAll('<div class="length_pagination"></div>');
        var $topContaine1 = $('.length_pagination').parent();
        $('.top .dataTables_info, .top .length_pagination').wrapAll('<div class="show_page_align"></div>');

        // Setup custom filters after DataTable initialization
        setupCustomFilters();
      }
    });
  });

  function upsertPermissionRow(permission) {
    if (!permission || !permission.id || !dataTable) {
      return;
    }

    const rowData = [
      permission.name || '',
      permission.display_name || '',
      permission.slug || '',
      permission.guard_name || '',
      permission.module_name || '',
      permission.type_label || 'Public',
      permission.status_html || '',
      permission.actions_html || ''
    ];

    const $existingRow = $('#permissionstable tbody tr[data-id="' + permission.id + '"]');
    if ($existingRow.length) {
      const row = dataTable.row($existingRow);
      row.data(rowData).draw(false);
      $(row.node()).attr('data-id', permission.id);
    } else {
      const newRow = dataTable.row.add(rowData).draw(false);
      $(newRow.node()).attr('data-id', permission.id);
    }
  }

  // Define setupCustomFilters function once
  function setupCustomFilters() {
    var moduleColumnIndex = 4; // Module Name column (0-indexed)

    // Custom search input (top of table) - applies as you type
    $('#customSearchInput').on('keyup change', function() {
      dataTable.search(this.value).draw();
    });

    $('#customSearchInput').on('search', function() {
      dataTable.search(this.value).draw();
    });

    // Filter Toggle
    $('#toggleFilterBtn').on('click', function() {
      $('#filterWrapper').slideToggle();
    });

    $('#toggleFilterclear').on('click', function(e) {
      e.preventDefault();
      $('#filterWrapper').slideToggle();
    });


    // Apply Filter button - copy dropdown to hidden, then apply module column filter
    $('#applyFilterBtn').on('click', function() {
      var moduleValue = $('#filter_module_ui').val();
      $('#filter_module').val(moduleValue);

      dataTable.columns().search('');
      dataTable.search($('#customSearchInput').val());
      if (moduleValue) {
        dataTable.column(moduleColumnIndex).search(moduleValue);
      }
      dataTable.draw();
    });

    // Clear All Filters - clear inputs and redraw
    $('#clearFilterBtn').on('click', function() {
      $('#filter_module_ui').val('');
      $('#filter_module').val('');
      $('#customSearchInput').val('');
      dataTable.search('').columns().search('').draw();
    });

  }

  $(document).ready(function() {
    // 1. Initialize the manager
    ModalFormManager.init();

    // 2. Bind click events for both Add and Edit buttons
    $('#addpermissionBtn').on('click', function() {
      const url = $(this).data('url');
      const title = $(this).data('title');
      ModalFormManager.openModal(url, title);
    });

    $(document).on('click', '.edit-item', function(e) {
      const url = $(this).data('url');
      const title = $(this).data('title');
      ModalFormManager.openModal(url, title);
    });

    // function for handle add form
    handleAjaxForm("#form-permission", {
      loadingIndicator: 'button',
      buttonTextSelector: '#btn-text',
      buttonSpinnerSelector: '#btn-spinner',
      modalToClose: "#genericModal",
      closeModalOnSuccess: true,
      reloadOnSuccess: false,
      onSuccess: function (res) {
        if (typeof dataTable !== 'undefined') {
          upsertPermissionRow(res?.permission);
        }
      }
    });

    // form for edit form
    handleAjaxForm("#form-edit-permission", {
      loadingIndicator: 'button',
      buttonTextSelector: '#btn-edit-text',
      buttonSpinnerSelector: '#btn-edit-spinner',
      modalToClose: "#genericModal",
      closeModalOnSuccess: true,
      successTitle: "Module Updated!",
      reloadOnSuccess: false,
      onSuccess: function (res) {
        if (typeof dataTable !== 'undefined') {
          upsertPermissionRow(res?.permission);
        }
      }
    });

    // for delete functionality
    handleDelete();
  });

  $(document).on('change', '.js-toggle-permission-active', function() {
    const checkbox = $(this);
    const permissionId = checkbox.data('id');
    const isActive = checkbox.prop('checked');

    $.ajax({
      url: `{{ route('masterapp.permissions.toggle-active', ':id') }}`.replace(':id', permissionId),
      type: 'PATCH',
      data: {
        _token: '{{ csrf_token() }}'
      },
      success: function() {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: isActive ? 'Permission activated' : 'Permission deactivated',
          timer: 2000,
          showConfirmButton: false
        });
      },
      error: function() {
        checkbox.prop('checked', !isActive);
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: 'Failed to update permission status',
          timer: 3000,
          showConfirmButton: false
        });
      }
    });
  });
</script>
@endpush

@endsection

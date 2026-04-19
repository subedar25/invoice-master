@extends('masterapp.layouts.app')
@section('content')
@push('styles')
<style>
    .modules-table-card .dataTables_wrapper .search-input-wrapper {
      position: relative;
      display: inline-block;
      max-width: 100%;
    }

    .modules-table-card .dataTables_wrapper .search-input-wrapper .fa-search {
      position: absolute;
      left: 17px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      pointer-events: none;
    }

    .modules-table-card .dataTables_wrapper .dataTables_filter input.search-input {
      width: min(455px, 100%) !important;
      max-width: 100%;
      padding-left: 34px !important;
      box-sizing: border-box;
    }
  </style>
@endpush
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Modules</h1>
      </div>
      <div class="col-sm-6 d-flex justify-content-end add-new">
          @can('create-modules')
        <button type="button" class="btn btn-primary" id="addModuleBtn"
          data-url="{{ route('masterapp.modules.create') }}"
          data-title="Add New Module">
          <i class="fa fa-plus"></i> Add Module
        </button>
          @endcan
      </div>
    </div>
  </div>
</div>
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card modules-table-card">

          <!-- /.card-header -->
          <div class="card-body">
            <table id="modulestable" class="table table-bordered table-hover">
              <thead>
                <th>ID</th>
                <th>Name</th>
                <th>slug</th>
                <th>Type</th>
                <th>Active</th>

                <th>Actions</th>
              </thead>
              <tbody>
                @foreach ($modules as $module)
                <tr data-id="{{ $module->id }}">

                  <td data-field="id">{{ $module->id }}</td>
                  <td data-field="name">
                      {{ $module->name }}
                  </td>
                  <td data-field="email">{{ $module->slug }}</td>
                  <td data-field="type">{{ ucfirst($module->type ?? 'public') }}</td>
                  <td data-field="is_active">
                    @can('edit-modules')
                    <div class="text-center">
                      <div class="custom-control custom-switch d-inline-block">
                        <input type="checkbox"
                               class="custom-control-input js-toggle-module-active"
                               id="moduleActiveSwitch{{ $module->id }}"
                               data-id="{{ $module->id }}"
                               {{ ($module->is_active ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="moduleActiveSwitch{{ $module->id }}"></label>
                      </div>
                    </div>
                    @else
                      @if($module->is_active ?? true)
                        <span class="badge badge-success">Active</span>
                      @else
                        <span class="badge badge-secondary">Inactive</span>
                      @endif
                    @endcan
                  </td>
                  <td data-field="actions">
                    <div class="action-div">
                       @can('edit-modules')
                      <button type="button" class="btn btn-link p-0 action-icon edit-item"
                        data-url="{{ route('masterapp.modules.edit', ['module' => $module->id]) }}"
                        data-title="Edit Module"
                        title="Edit module">
                        <i class="fa fa-edit"></i>
                      </button>
                       @endcan
                      @can('delete-modules')
                      <button type="button"
                        class="btn btn-link p-0 action-icon text-danger delete-item"
                        data-url="{{ route('masterapp.modules.destroy', ['module' => $module->id]) }}"
                        data-name="{{ $module->name }}"
                        title="Delete module">
                        <i class="fa fa-trash"></i>
                      </button>
                       @endcan
                    </div>
                  </td>


                </tr>
                @endforeach
              </tbody>

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


@push('scripts')
<script>
  $(function() {
    var dataTable = $('#modulestable').DataTable({
      order: [[0, 'desc']],
      columnDefs: [
        { targets: 0, visible: false, searchable: false }
      ],
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
        },
        search: ''
      },

      dom: '<"top"Biplf>rt<"bottom bottomAlign"ip><"clear">',
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
        var $topContaine2 = $('.dataTables_filter').parent();
        $(' .top .dt-buttons , .top .dataTables_filter').wrapAll('<div class=" btn_filter_align "></div>');
        // Set placeholder for search input and add search icon
        var $searchInput = $('.dataTables_filter input');
        $searchInput.attr('placeholder', 'Search..');
        // wrap input
        $searchInput.wrap('<div class="search-input-wrapper"></div>');
        // add class
        $searchInput.addClass('search-input');
        // ADD SEARCH ICON ELEMENT
        $searchInput.before('<i class="fa fa-search"></i>');

      }
    });

  });

  $(document).ready(function() {
    // 1. Initialize the manager
    ModalFormManager.init();


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
    // The global ajaxError handler will catch any 403s from them.
    handleAjaxForm("#form-modules", {
      loadingIndicator: 'button',
      buttonTextSelector: '#btn-text',
      buttonSpinnerSelector: '#btn-spinner',
      modalToClose: "#genericModal",
      closeModalOnSuccess: true,
      reloadOnSuccess: false,
      onSuccess: function () {
        if ($.fn.DataTable.isDataTable('#dataTable')) {
          $('#dataTable').DataTable().ajax.reload(null, false);
        }
      }
    });

    handleAjaxForm("#form-edit-module", {
      loadingIndicator: 'button',
      buttonTextSelector: '#btn-edit-text',
      buttonSpinnerSelector: '#btn-edit-spinner',
      modalToClose: "#genericModal",
      closeModalOnSuccess: true,
      successTitle: "Module Updated!",
      reloadOnSuccess: false,
      onSuccess: function () {
        if ($.fn.DataTable.isDataTable('#dataTable')) {
          $('#dataTable').DataTable().ajax.reload(null, false);
        }
      }
    });


    handleDelete();

    $(document).on('change', '.js-toggle-module-active', function() {
      const checkbox = $(this);
      const moduleId = checkbox.data('id');
      const isActive = checkbox.prop('checked');

      $.ajax({
        url: `{{ route('masterapp.modules.toggle-active', ':id') }}`.replace(':id', moduleId),
        type: 'PATCH',
        data: {
          _token: '{{ csrf_token() }}'
        },
        success: function() {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: isActive ? 'Module activated' : 'Module deactivated',
              timer: 2000,
              showConfirmButton: false
            });
          }
        },
        error: function() {
          checkbox.prop('checked', !isActive);
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'error',
              title: 'Failed to update module status',
              timer: 3000,
              showConfirmButton: false
            });
          }
        }
      });
    });

  });
</script>
@endpush
@endsection

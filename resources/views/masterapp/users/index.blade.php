@extends('masterapp.layouts.app')
@section('content')
@push('styles')
<style>
  #example2_wrapper .search-input-wrapper {
    position: relative;
    display: inline-block;
    max-width: 100%;
  }

  #example2_wrapper .search-input-wrapper .fa-search {
    position: absolute;
    left: 17px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
  }

  #example2_wrapper .dataTables_filter input.search-input {
    width: min(455px, 100%) !important;
    max-width: 100%;
    padding-left: 34px !important;
    box-sizing: border-box;
  }
</style>
@endpush

            <div class="content-header">
                    <div class="container-fluid">
                            <div class="row mb-2 align-items-center">
                                <div class="col-sm-6">
                                    <h1 class="m-0 text-dark">All Users</h1>
                                </div>

                                <div class="col-sm-6 d-flex justify-content-end">
                                    <button type="button" class="btn btn-default mr-2" id="toggleFilterBtn">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                    @can('create-user')
                                    <a href="{{ route('masterapp.users.create') }}"
                                    class="btn btn-primary"
                                    style="width:150px;">
                                        <i class="fa fa-plus mr-1"></i> Add User
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>

                </div>
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">

                {{-- Filters (Active column only) --}}
                @php
                    $hasFilters = request()->has('active');
                    $displayFilter = $hasFilters ? 'block' : 'none';
                @endphp

                <div class="filter-wrapper" id="filterWrapper" style="display: {{ $displayFilter }};">
                    <a href="#" class="close-filter-btn" id="toggleFilterclear" title="Clear Filters & Close">
                        &times;
                    </a>
                    <form id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="font-weight-bold">Active</label>
                                <select id="filter_active" name="active" class="form-control filter-input">
                                    <option value="">All</option>
                                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Deactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="applyFilterBtn" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Apply Filter</button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12 text-right">
                                <a href="{{ route('masterapp.users.index') }}" class="btn btn-link btn-sm text-secondary">Clear All Filters</a>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Active Filters Badges --}}
                <div id="activeFilters" class="mb-3" style="display:none;">
                    <strong>Active Filters:</strong>
                    <span id="activeFiltersList"></span>
                </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="card">
                        <div class="card-header">

                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                          <table id="example2" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                  <th class="d-none no-vis">ID</th>
                                  <th>Name</th>
                                  <th>Email</th>
                                  <th>Phone</th>
                                  {{-- <th>Change Password</th> --}}
                                  <th>Role</th>
                                  {{-- <th>Permissions</th> --}}
                                  <th>Added Timestamp</th>
                                  <th>Driver</th>
                                  <th>Department</th>
                                  <th>Publications</th>
                                  <th>Contributor Status</th>
                                  <th>Status</th>
                                  <th>Notes</th>
                                  @can('active-deactive')
                                  <th>Active</th>
                                  @endcan
                                  <th class="no-export no-vis">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($users as $user)
                              <tr data-id="{{ $user->id }}">

                                  <td class="d-none" data-field="id">{{ $user->id }}</td>
                                  <td data-field="name">
                                  {{-- <a href="{{ route('masterapp.users.show', $user->id) }}"
                                      class="entity-link" > --}}
                                  <a href="{{ route('masterapp.entity.info', ['type' => 'users', 'id' => $user->id]) }}"
                                   class="entity-link" >
                                    {{ $user->first_name }}  {{ $user->last_name }}
                                  </a>
                                  </td>
                                  <td data-field="email">{{ $user->email }}</td>
                                  <td data-field="phone">{{ $user->phone }}</td>

                                  {{-- CHANGE PASSWORD (boolean) --}}
                                  {{-- <td data-field="change_password">
                                      {{ $user->change_password ? "Yes" : "No" }}
                                  </td> --}}

                                  {{-- ROLES (multi) --}}
                                  <td data-field="roles">
                                      {{ $user->roles->pluck("name")->implode(", ") ?: "NONE" }}
                                  </td>

                                  {{-- PERMISSIONS (multi) --}}
                                  {{-- <td data-field="permissions">
                                      {{ $user->getAllPermissions()->pluck("name")->implode(", ") ?: "NONE" }}
                                  </td> --}}
                                  <td>{{ $user->created_at->format('m/d/Y h:i A') }}</td>

                                  {{-- DRIVER (boolean) --}}
                                  <td data-field="driver">
                                      {{ $user->driver ? "Yes" : "No" }}
                                  </td>
                                    <td data-field="department_id">
                                      {{ $user->department->name ?? "N/A" }}
                                  </td>
                                  {{-- <td data-field="publications">
                                    {{ $user->publication_users->publication_id ?? "N/A" }} --}}
                                    <td data-field="publications">
                                    @forelse ($user->publications as $publication)
                                        {{-- <span class="badge badge-info mr-1"> --}}
                                            {{ $publication->name }}

                                        {{-- </span> --}}
                                    @empty
                                        <span class="text-muted">N/A</span>
                                    @endforelse
                                </td>

                                  <td data-field="contributor_status">
                                      {{ $user->contributor_status }}
                                  </td>
                                  {{-- STATUS (select) --}}
                                    <td data-field="status_id">
                                        <div class="status-container d-inline-block"
                                            data-id="{{ $user->id }}"
                                            data-status-id="{{ $user->status_id ?? '' }}">

                                            {{-- Status badge: from current timesheet when active; when not clocked in show "Not Available" --}}
                                            @php
                                                $shift = $currentShifts[$user->id] ?? null;
                                                if ($shift && isset($clockInModeToStatusLabel[$shift->clock_in_mode ?? ''])) {
                                                    $displayStatus = $statusesList->firstWhere('label', $clockInModeToStatusLabel[$shift->clock_in_mode]);
                                                } else {
                                                    $displayStatus = $statusesList->firstWhere('label', 'Not Available');
                                                }
                                                $displayStatus = $displayStatus ?? $statusesList->firstWhere('label', 'Not Available');
                                            @endphp
                                            <span
                                                class="badge {{ $displayStatus->badge_class ?? 'badge-secondary' }} status-badge"
                                                title="{{ $shift ? 'From current shift (click to override)' : 'Click to change status' }}"
                                                style="cursor: pointer;"
                                            >
                                                {{ $displayStatus->label ?? 'N/A' }}
                                            </span>

                                            {{-- Hidden select (inline edit): show currently displayed status as selected --}}
                                            <div class="status-select-wrapper mt-1" style="display: none;">
                                                <select class="form-control form-control-sm status-change-select">
                                                    @foreach ($statusesList as $status)
                                                        <option
                                                            value="{{ $status->id }}"
                                                            {{ (int) ($displayStatus->id ?? 0) === (int) $status->id ? 'selected' : '' }}
                                                        >
                                                            {{ $status->label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="status-spinner d-none ml-2">
                                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                </div>
                                            </div>

                                        </div>
                                    </td>

                                  {{-- STATUS NOTES (textarea) --}}
                                  <td data-field="status_notes">
                                      {{ $user->status_notes }}
                                  </td>

                                {{-- ajax toggle without page reload --}}
                                @can('active-deactive')
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="custom-control custom-switch">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input js-toggle-active"
                                                id="activeSwitch{{ $user->id }}"
                                                data-id="{{ $user->id }}"
                                                {{ $user->active ? 'checked' : '' }}
                                            >
                                            <label class="custom-control-label" for="activeSwitch{{ $user->id }}"></label>
                                        </div>
                                        <div class="active-spinner d-none ml-2">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        </div>
                                    </div>
                                </td>
                                @endcan

                                    {{-- ACTIONS --}}
                                    <td data-field="actions" class="no-export">
                                        <div class="action-div d-flex gap-2">

                                            {{-- View --}}
                                            <a href="{{ route('masterapp.entity.info', ['type' => 'users', 'id' => $user->id]) }}"
                                                title="View user" class="action-icon entity-link">
                                                <i class="fa fa-eye" aria-hidden="true"></i>
                                            </a>

                                            {{-- Edit --}}
                                            @can('edit-user')
                                            <a href="{{ route('masterapp.users.edit', $user->id) }}"
                                                title="Edit user" class="action-icon">
                                                <i class="fa fa-edit" aria-hidden="true"></i>
                                            </a>
                                            @endcan
                                            {{-- <form class="d-inline js-delete-user"
                                                data-url="{{ route('masterapp.users.destroy', $user->id) }}">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="action-icon text-danger btn btn-link p-0"
                                                        title="Delete user" onclick=del_user($id)>
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form> --}}
                                             {{-- @can('delete-users') --}}

                                             {{-- Hide soft delete button after discussing with client --}}
                                        {{-- <button type="button"
                                            class="btn btn-link p-0 action-icon text-danger delete-item"
                                            data-url="{{ route('masterapp.users.destroy',  $user->id) }}"
                                            data-name="{{ $user->name }}"
                                            title="Delete User">
                                            <i class="fa fa-trash"></i>
                                        </button> --}}
                                        {{-- @endcan --}}
                                        </div>
                                    </td>
                              </tr>
                          @endforeach
                            </tbody>
                            <!-- <tfoot>
                            <tr>
                              <th>Rendering engine</th>
                              <th>Browser</th>
                              <th>Platform(s)</th>
                              <th>Engine version</th>
                              <th>CSS grade</th>
                            </tr> -->
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
<script src="{{ asset('js/ajax-form-handler.js') }}"></script>

<script>

$(function () {
     // --- URL Param Handling (Active filter only) ---
     var urlParams = new URLSearchParams(window.location.search);

     // Initialize filter from URL
     if (urlParams.has('active')) $('#filter_active').val(urlParams.get('active'));

     // Update URL from current filter
     function updateUrl() {
         var params = new URLSearchParams();
         var active = $('#filter_active').val();
         if (active) params.set('active', active);
         var newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
         history.pushState(null, '', newUrl);
         updateActiveFilterBadges();
     }

     // Active filter badges
     function updateActiveFilterBadges() {
         var container = $('#activeFilters');
         var list = $('#activeFiltersList');
         list.empty();
         var activeVal = $('#filter_active').val();
         var activeText = $('#filter_active option:selected').text();
         if (activeVal !== '') {
             container.show();
             var badge = $('<span class="badge badge-info ml-2 p-2" style="font-size: 100%;">Active: ' + activeText + ' <i class="fa fa-times cursor-pointer remove-filter" data-target="#filter_active" style="margin-left:5px;"></i></span>');
             list.append(badge);
         } else {
             container.hide();
         }
     }

     // Custom search: filter by Active column (checkbox in row)
     $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
         if (settings.nTable.id !== 'example2') return true;
         var activeFilter = $('#filter_active').val();
         if (!activeFilter) return true;
         var table = $('#example2').DataTable();
         var $row = $(table.row(dataIndex).node());
         var isChecked = $row.find('.js-toggle-active').prop('checked');
         if (activeFilter === '1') return !!isChecked;
         if (activeFilter === '0') return !isChecked;
         return true;
     });

     // Filter panel toggle
     $('#toggleFilterBtn').click(function () {
         $('#filterWrapper').slideToggle();
     });
     $('#toggleFilterclear').click(function (e) {
         e.preventDefault();
         $('#filterWrapper').slideToggle();
     });

     // Apply Filter (will run after table is created)
     function applyFilter() {
         $('#example2').DataTable().draw();
         updateUrl();
     }

     // Remove single filter badge
     $(document).on('click', '.remove-filter', function () {
         var target = $(this).data('target');
         $(target).val('');
         $('#example2').DataTable().draw();
         updateUrl();
     });

     var dataTable=$('#example2').DataTable({
      order: [[0, 'desc']],
      "pageLength": 10,
      responsive: true,
      scrollX: false,
      autoWidth: false,
      lengthMenu: [[-1, 10, 50, 100], ["All", 10, 50, 100]],
      language: {
          lengthMenu: 'Show _MENU_',
          paginate: {
              next: '<i class="fa  fa-angle-double-right "></i>',
              previous: '<i class="fa  fa-angle-double-left"></i>'
          },
          search: ''
      },

      dom: '<"top"Biplf>rt<"bottom bottomAlign"ip><"clear">',
      buttons: [
        {
            extend: 'print',
            title: 'Pulse Publication - Users',
            filename: 'Pulse Publication - Users',
            text: '<i class="fa fa-print"></i> Print',
            className: 'btn btn-secondary',
            exportOptions: {
                columns: function (idx, data, node) {
                    const table = $('#example2').DataTable();
                    if ($(node).hasClass('no-vis')) return false;
                    return table.column(idx).visible();
                },
                format: { body: exportFormatter }
            },
            action: function (e, dt, button, config) {
                $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                setTimeout(function () {
                    dt.draw(false);
                }, 300);
                setTimeout(function () {
                    dt.draw(false);
                }, 1000);
            },
            customize: function (win) {

                // Smaller font
                $(win.document.body).css('font-size', '8px');

                // Landscape + tight margins
                $(win.document.head).append(`
                    <style>
                        @page {
                            size: A4 landscape;
                            margin: 8mm;
                        }

                        table {
                            width: 100% !important;
                            table-layout: fixed;
                        }

                        th, td {
                            white-space: normal !important;
                            word-break: break-word;
                            overflow-wrap: break-word;
                            padding: 3px 4px !important;
                        }

                        th {
                            font-size: 8.5px;
                        }
                    </style>
                `);
            },
            // default print action
        },
        {
            extend: 'copyHtml5',
            title: 'Pulse Publication - Users',
            filename: 'Pulse Publication - Users',
            text: '<i class="fa fa-copy"></i> Copy Data',
            className: 'btn btn-primary',
            exportOptions: {
                columns: exportVisibleColumns,
                format: { body: exportFormatter }
            }
        },

        {
            extend: 'excelHtml5',
            title: 'Pulse Publication - Users',
            filename: 'Pulse Publication - Users',
            text: '<i class="fa fa-download"></i> Excel',
            className: 'btn btn-success',
            exportOptions: {
                columns: exportVisibleColumns,
                format: {
                    body: function (data, row, column, node) {

                        // Active toggle
                        if ($(node).find('.js-toggle-active').length) {
                            return $(node).find('.js-toggle-active').prop('checked')
                                ? 'Active'
                                : 'Inactive';
                        }

                        return $(node).text().trim();
                    }
                }
            }
        },

          // {
          //     extend: 'csvHtml5',
          //     text: '<i class="fa fa-download"></i> CSV',
          //     className: 'btn btn-info',
          //     exportOptions: {
          //         columns: [0, 1, 2, 3, 4, 5]
          //     }
          // },
        {
            extend: 'pdfHtml5',
            title: 'Pulse Publication - Users',
            filename: 'Pulse Publication - Users',
            text: '<i class="fa fa-download"></i> PDF',
            className: 'btn btn-danger',
            orientation: 'landscape',
            pageSize: 'A4',
            exportOptions: {
                columns: exportVisibleColumns,
                format: { body: exportFormatter }
            },
            customize: function (doc) {

                const table = doc.content.find(c => c.table).table;
                const colCount = table.body[0].length;
                //    GLOBAL PDF STYLES
                doc.pageMargins = [6, 6, 6, 6];
                doc.defaultStyle.fontSize = 6;
                doc.styles.tableHeader.fontSize = 6.5;

                //    FORCE TABLE TO FIT PAGE
                table.widths = Array(colCount).fill((100 / colCount).toFixed(2) + '%');
                //    TEXT WRAPPING (CRITICAL)
                doc.styles.tableBodyEven = {
                    fontSize: 6,
                    margin: [0, 1, 0, 1]
                };
                doc.styles.tableBodyOdd = {
                    fontSize: 6,
                    margin: [0, 1, 0, 1]
                };
                //    HEADER STYLE
                table.body[0].forEach(cell => {
                    cell.fillColor = '#2c3e50';
                    cell.color = '#ffffff';
                    cell.alignment = 'left';
                });
            }
        },
        {
            extend: 'colvis',
            className: 'btn btn-warning',
            columns: ':not(.no-vis)'
        }
    ],
      columnDefs: [
        {
            targets: [0],
            visible: false,
            searchable: false
        },
        {
            targets: [5, 4, 6, 9, 10, 11, 12],
            // hidden initially
            visible: false
        },
        {
            targets: -1,
            orderable: false,
            searchable: true,
            className: 'no-vis'

            // targets: -1,
            // orderable: false,
            // searchable: false,
            // className: 'no-vis action-column'
        }
      ],
      fixedColumns: {
          rightColumns: 1
      },
      initComplete: function () {
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
          $searchInput.prop('disabled', false);
          // wrap input
          $searchInput.wrap('<div class="search-input-wrapper"></div>');
          // add class
          $searchInput.addClass('search-input');
          // ADD SEARCH ICON ELEMENT
          $searchInput.before('<i class="fa fa-search"></i>');

          // Initial active filter badges
          updateActiveFilterBadges();
      }
  });

    // When returning from print tab/window, force a redraw to restore search behavior.
    if (!window.__usersPrintFocusHandlerBound) {
        window.__usersPrintFocusHandlerBound = true;
        window.addEventListener('focus', function () {
            if ($.fn.DataTable.isDataTable('#example2')) {
                $('#example2').DataTable().draw(false);
                reenableUsersSearchInput();
            }
        });
        window.addEventListener('afterprint', function () {
            if ($.fn.DataTable.isDataTable('#example2')) {
                $('#example2').DataTable().draw(false);
                reenableUsersSearchInput();
            }
        });
        if (window.matchMedia) {
            var mediaQueryList = window.matchMedia('print');
            mediaQueryList.addEventListener('change', function (mql) {
                if (!mql.matches && $.fn.DataTable.isDataTable('#example2')) {
                    $('#example2').DataTable().draw(false);
                    reenableUsersSearchInput();
                }
            });
        }
    }

     // Apply Filter button
     $('#applyFilterBtn').click(function () {
         applyFilter();
     });

});
//  {{-- ajax toggle without page reload --}}
$(function () {

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        showClass: {
            popup: 'animate__animated animate__fadeInUp'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutDown'
        }
    });

    //  EVENT DELEGATION (IMPORTANT)
    $(document).on('change', '.js-toggle-active', function () {

        const checkbox = $(this);
        const userId = checkbox.data('id');
        const isActive = checkbox.prop('checked');

        // Show spinner
        checkbox.closest('td').find('.active-spinner').removeClass('d-none');

        $.ajax({
            url: `{{ url('master-app/users') }}/${userId}/toggle-active`,
            type: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}'
            },

            success: function () {
                // Hide spinner
                checkbox.closest('td').find('.active-spinner').addClass('d-none');

                Toast.fire({
                    icon: 'success',
                    title: isActive
                        ? 'User activated successfully'
                        : 'User deactivated successfully'
                });
            },

            error: function () {
                // Hide spinner
                checkbox.closest('td').find('.active-spinner').addClass('d-none');

                // rollback UI
                checkbox.prop('checked', !isActive);

                Toast.fire({
                    icon: 'error',
                    title: 'Failed to update user status'
                });
            }
        });
    });

});


$(function () {

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        showClass: {
            popup: 'animate__animated animate__fadeInUp'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutDown'
        }
    });

    $(document).on('click', '.status-badge', function () {
        const $badge = $(this);
        const $container = $badge.closest('.status-container');

        $badge.hide();
        $container
            .find('.status-select-wrapper')
            .show()
            .find('.status-change-select')
            .focus();
    });

    function runStatusUpdate($container, statusId) {
        const userId = $container.data('id');
        const $select = $container.find('.status-change-select');

        $container.data('status-updating', true);
        $container.find('.status-spinner').removeClass('d-none');

        let url = "{{ route('masterapp.users.updateStatus', ':id') }}";
        url = url.replace(':id', userId);

        $.ajax({
            url: url,
            type: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}',
                status_id: statusId
            },

            success: function (response) {
                $container.find('.status-spinner').addClass('d-none');
                $container.data('status-updating', false);

                if (response.success) {
                    const newLabel = $select.find('option[value="' + statusId + '"]').text();
                    const newClass = response.badge_class || 'badge-secondary';

                    const $badge = $container.find('.status-badge');

                    $badge
                        .removeClass(function (_, cls) {
                            return (cls.match(/badge-\S+/g) || []).join(' ');
                        })
                        .addClass(newClass)
                        .text(newLabel)
                        .show();

                    $container.find('.status-select-wrapper').hide();
                    $container.data('status-id', statusId);

                    Toast.fire({
                        icon: 'success',
                        title: 'User status updated successfully'
                    });
                }
            },

            error: function () {
                $container.find('.status-spinner').addClass('d-none');
                $container.data('status-updating', false);

                alert('Failed to update status. Please try again.');
                resetStatusUI($container);
            }
        });
    }

    // Update status on change
    $(document).on('change', '.status-change-select', function () {
        const $select = $(this);
        const $container = $select.closest('.status-container');
        const statusId = $select.val();

        if ($container.data('status-updating')) return;

        runStatusUpdate($container, statusId);
    });

    // Blur: if value changed but change didn't fire (e.g. selecting first option when none was selected), run update
    $(document).on('blur', '.status-change-select', function () {
        const $select = $(this);
        const $container = $select.closest('.status-container');

        setTimeout(() => {
            if ($container.data('status-updating')) return;
            if (!$container.find('.status-select-wrapper').is(':visible')) return;

            const currentVal = String($select.val() || '');
            const initialVal = String($container.data('status-id') || '');

            if (currentVal !== initialVal) {
                runStatusUpdate($container, currentVal);
            } else {
                resetStatusUI($container);
            }
        }, 200);
    });

    // Helper: Reset UI
    function resetStatusUI($container) {
        $container.find('.status-select-wrapper').hide();
        $container.find('.status-badge').show();
    }

});




</script>

@php
    $successMessage = session()->pull('success');
@endphp

<script>
document.addEventListener('DOMContentLoaded', () => {

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        showClass: {
            popup: 'animate__animated animate__fadeInUp'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutDown'
        }
    });

    //  . Normal redirect success (non-AJAX)
    @if ($successMessage)
        Toast.fire({
            icon: 'success',
            title: @json($successMessage)
        });
    @endif

    //  . AJAX redirect success (?created=1)
    const params = new URLSearchParams(window.location.search);

    if (params.get('created') === '1') {
        Toast.fire({
            icon: 'success',
            title: params.get('message') || 'User created successfully'
        });

        //  remove query params so it never repeats
        window.history.replaceState({}, document.title, window.location.pathname);
    }

});

</script>
<script>
    handleDelete();
// $(function () {

//     const Toast = Swal.mixin({
//         toast: true,
//         position: 'top-end',
//         showConfirmButton: false,
//         timer: 2000,
//         timerProgressBar: true,
//         showClass: {
//             popup: 'animate__animated animate__fadeInUp'
//         },
//         hideClass: {
//             popup: 'animate__animated animate__fadeOutDown'
//         }
//     });

//     //  EVENT DELEGATION (IMPORTANT)
//     $(document).on('submit', '.js-delete-user', function (e) {
//         e.preventDefault();

//         const $form = $(this);
//         const url = $form.data('url');
//         const token = $form.find('input[name="_token"]').val();

//         Swal.fire({
//             title: 'Are you sure?',
//             text: 'This user will be deleted.',
//             icon: 'warning',
//             showCancelButton: true,
//             confirmButtonColor: '#d33',
//             cancelButtonColor: '#6c757d',
//             confirmButtonText: 'Yes',
//             cancelButtonText: 'Cancel',
//             position: 'top-center'
//         }).then((result) => {

//             if (!result.isConfirmed) return;

//             $.ajax({
//                 url: url,
//                 type: 'POST',
//                 data: {
//                     _token: token,
//                     _method: 'DELETE'
//                 },
//                 dataType: 'json',

//                 success: function (res) {
//                     Toast.fire({
//                         icon: 'success',
//                         title: res.message || 'User deleted successfully'
//                     });

//                     $form.closest('tr').fadeOut(300, function () {
//                         $(this).remove();
//                     });
//                 },

//                 error: function () {
//                     Toast.fire({
//                         icon: 'error',
//                         title: 'Failed to delete user'
//                     });
//                 }
//             });
//         });
//     });

// });

//  {{-- export formatter function --}}
function exportFormatter(data, row, column, node) {

    // ACTIVE TOGGLE (checkbox switch)
    if ($(node).find('.js-toggle-active').length) {
        return $(node).find('.js-toggle-active').prop('checked')
            ? 'Active'
            : 'Inactive';
    }

    // STATUS BADGE (UI truth FIRST)
    const badge = $(node).find('.badge');
    if (badge.length) {
        const text = badge.text().trim();
        return text !== '' ? text : 'N/A';
    }

    // STATUS DROPDOWN (only if no badge exists)
    const select = $(node).find('select');
    if (select.length) {
        const selected = select.find('option:selected').text().trim();
        return selected !== '' ? selected : 'N/A';
    }

    // DEFAULT: clean text
    const clean = $('<div>').html(data).text().trim();
    return clean !== '' ? clean : 'N/A';
}

//  Helper to temporarily disable responsive, perform action, then re-enable (fixes export issues)

function exportVisibleColumns(idx, data, node) {
    const table = $('#example2').DataTable();

    // Exclude action column
    if ($(node).hasClass('no-vis')) {
        return false;
    }

    // Export only columns enabled via Column Visibility
    return table.column(idx).visible();
}

function reenableUsersSearchInput() {
    var $searchInput = $('.dataTables_filter input');
    if (!$searchInput.length) return;
    $searchInput.prop('disabled', false);
}

</script>
@endpush
@endsection

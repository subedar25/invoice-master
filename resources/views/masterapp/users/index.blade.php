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

  /* Select2 multi-select filters: full width, readable tags */
  #filterWrapper .select2-container {
    width: 100% !important;
  }

  /* Float dropdown over content (dropdownParent: body); high z-index above sidebar/cards */
  .select2-dropdown.users-filter-select2-dropdown {
    z-index: 9999 !important;
  }

  #filterWrapper .select2-container .select2-selection--multiple {
    min-height: 38px;
    border-radius: 0.25rem;
  }

  #filterWrapper .select2-container .select2-selection--multiple .select2-selection__rendered {
    padding-bottom: 2px;
  }

  /* Smaller text only for selected values (tags) inside Department / Designation — full column width unchanged */
  #filterWrapper .select2-container .select2-selection--multiple .select2-selection__choice {
    font-size: 0.72rem;
    line-height: 1.3;
    padding: 0.1rem 0.4rem 0.1rem 0.35rem;
    margin-top: 0.2rem;
  }

  #filterWrapper .select2-container .select2-selection--multiple .select2-selection__choice__remove {
    font-size: 0.68rem;
    margin-right: 0.25rem;
  }

  /* Active filters: label + chips on one line (does not affect filter row width) */
  #activeFilters {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem 0.5rem;
    font-size: 0.8125rem;
  }

  #activeFilters .active-filters-heading {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0;
    flex-shrink: 0;
  }

  #activeFilters #activeFiltersList {
    flex: 1 1 auto;
    min-width: 0;
  }

  .users-active-filter-chips {
    gap: 0.35rem 0.25rem;
  }

  .users-active-filter-chip {
    font-size: 0.72rem;
    font-weight: 500;
    line-height: 1.3;
    padding: 0.2rem 0.45rem 0.2rem 0.5rem;
    border-radius: 0.2rem;
    display: inline-flex;
    align-items: center;
    max-width: min(100%, 20rem);
  }

  .users-active-filter-chip .remove-filter-chip {
    cursor: pointer;
    font-size: 0.7rem;
    padding: 0.1rem 0.15rem;
    margin-left: 0.25rem;
    opacity: 0.85;
    flex-shrink: 0;
  }

  .users-active-filter-chip .remove-filter-chip:hover {
    opacity: 1;
  }
</style>
@endpush

            <div class="content-header">
                    <div class="container-fluid">
                            <div class="row mb-2 align-items-center">
                                <div class="col-sm-6">
                                    <h1 class="m-0 text-dark">Users</h1>
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

                {{-- Filters (scope follows org from header switcher) --}}
                @php
                    $rawDept = request()->input('department_id');
                    $selectedDepartmentIds = is_array($rawDept)
                        ? array_values(array_filter(array_map('strval', $rawDept)))
                        : (($rawDept !== null && $rawDept !== '') ? [(string) $rawDept] : []);
                    $rawDesig = request()->input('designation_id');
                    $selectedDesignationIds = is_array($rawDesig)
                        ? array_values(array_filter(array_map('strval', $rawDesig)))
                        : (($rawDesig !== null && $rawDesig !== '') ? [(string) $rawDesig] : []);
                    $hasFilters = request()->filled('active') || $selectedDepartmentIds !== [] || $selectedDesignationIds !== [];
                    $displayFilter = $hasFilters ? 'block' : 'none';
                @endphp

                <div class="filter-wrapper" id="filterWrapper" style="display: {{ $displayFilter }};">
                    <a href="#" class="close-filter-btn" id="toggleFilterclear" title="Clear Filters & Close">
                        &times;
                    </a>
                    <form id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="font-weight-bold">Department</label>
                                <select id="filter_department_id" name="department_id[]" class="form-control filter-input select2 select2-users-filter" multiple="multiple" style="width: 100%;" data-placeholder="All departments">
                                    <option value=""></option>
                                    @foreach (($filterDepartments ?? []) as $dept)
                                        <option value="{{ $dept->id }}" @selected(in_array((string) $dept->id, $selectedDepartmentIds, true))>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold">Designation</label>
                                <select id="filter_designation_id" name="designation_id[]" class="form-control filter-input select2 select2-users-filter" multiple="multiple" style="width: 100%;" data-placeholder="All designations">
                                    <option value=""></option>
                                    @foreach (($filterDesignations ?? []) as $desig)
                                        <option value="{{ $desig->id }}" @selected(in_array((string) $desig->id, $selectedDesignationIds, true))>
                                            {{ $desig->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold">Active</label>
                                <select id="filter_active" name="active" class="form-control filter-input">
                                    <option value="">All</option>
                                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Deactive</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Active Filters: one chip per selected value, each with its own remove --}}
                <div id="activeFilters" class="mb-3" style="display:none;">
                    <span class="active-filters-heading">Active filters</span>
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
                                  <th class="hide-initial">Role</th>
                                  {{-- <th>Permissions</th> --}}
                                  <th class="hide-initial">Department</th>
                                  <th class="hide-initial">Organizations</th>
                                  <th class="hide-initial">Active</th>
                                  <th class="no-export no-vis">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($users as $user)
                              <tr
                                  data-id="{{ $user->id }}"
                                  data-active="{{ $user->active ? 1 : 0 }}"
                                  data-org-ids="{{ $user->organizations->pluck('id')->implode(',') }}"
                                  data-department-id="{{ $user->department_id ?? '' }}"
                                  data-designation-id="{{ $user->designation_id ?? '' }}"
                              >

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

                                  <td data-field="department_id">
                                      {{ $user->department->name ?? "N/A" }}
                                  </td>

                                  <td data-field="organizations">
                                      {{ $user->organizations->pluck('name')->implode(', ') ?: 'N/A' }}
                                  </td>
                                  {{-- ACTIVE (DB) --}}
                                  <td class="text-center" data-field="active">
                                      @can('active-deactive')
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
                                      @else
                                          <span class="badge {{ $user->active ? 'badge-success' : 'badge-secondary' }}">
                                              {{ $user->active ? 'Active' : 'Inactive' }}
                                          </span>
                                      @endcan
                                  </td>

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
     // --- URL param handling (department[], designation[], active; org from header) ---
     var urlParams = new URLSearchParams(window.location.search);

     if (urlParams.has('active')) $('#filter_active').val(urlParams.get('active'));

     function readMultiParam(paramBase) {
         var bracket = paramBase + '[]';
         var vals = urlParams.getAll(bracket);
         if (vals.length) return vals;
         var legacy = urlParams.get(paramBase);
         if (legacy) return legacy.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
         vals = [];
         for (var i = 0; i < 50; i++) {
             var k = paramBase + '[' + i + ']';
             if (!urlParams.has(k)) break;
             vals.push(urlParams.get(k));
         }
         return vals;
     }

     if ($.fn.select2) {
         var $dropdownParent = $(document.body);
         $('#filter_department_id').select2({
             width: '100%',
             placeholder: $('#filter_department_id').data('placeholder') || 'All departments',
             allowClear: true,
             closeOnSelect: false,
             dropdownParent: $dropdownParent,
             dropdownCssClass: 'users-filter-select2-dropdown'
         });
         $('#filter_designation_id').select2({
             width: '100%',
             placeholder: $('#filter_designation_id').data('placeholder') || 'All designations',
             allowClear: true,
             closeOnSelect: false,
             dropdownParent: $dropdownParent,
             dropdownCssClass: 'users-filter-select2-dropdown'
         });
     }

     var deptFromUrl = readMultiParam('department_id');
     if (deptFromUrl.length) {
         $('#filter_department_id').val(deptFromUrl).trigger('change');
     }
     var desigFromUrl = readMultiParam('designation_id');
     if (desigFromUrl.length) {
         $('#filter_designation_id').val(desigFromUrl).trigger('change');
     }

     function updateUrl() {
         var params = new URLSearchParams();
         var active = $('#filter_active').val();
         if (active) params.set('active', active);
         var departmentIds = $('#filter_department_id').val() || [];
         departmentIds.forEach(function (id) {
             if (id) params.append('department_id[]', id);
         });
         var designationIds = $('#filter_designation_id').val() || [];
         designationIds.forEach(function (id) {
             if (id) params.append('designation_id[]', id);
         });
         var newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
         history.pushState(null, '', newUrl);
         updateActiveFilterBadges();
     }

     function optionLabel($select, value) {
         var s = String(value);
         var t = $select.find('option').filter(function () { return $(this).val() === s; }).first().text();
         return (t || '').trim() || s;
     }

     function updateActiveFilterBadges() {
         var container = $('#activeFilters');
         var list = $('#activeFiltersList');
         list.empty();

         var $wrap = $('<div class="users-active-filter-chips d-flex flex-wrap align-items-center"></div>');
         var hasAny = false;

         var $dept = $('#filter_department_id');
         ($dept.val() || []).filter(function (v) { return v !== ''; }).forEach(function (id) {
             hasAny = true;
             var label = optionLabel($dept, id);
             var $chip = $('<span class="users-active-filter-chip badge badge-info"></span>');
             $chip.attr('title', 'Department: ' + label);
             $chip.append(document.createTextNode(label + ' '));
             var $x = $('<i class="fa fa-times remove-filter-chip" role="button" tabindex="0" title="Remove"></i>');
             $x.attr('data-filter', 'department').attr('data-value', id);
             $chip.append($x);
             $wrap.append($chip);
         });

         var $desig = $('#filter_designation_id');
         ($desig.val() || []).filter(function (v) { return v !== ''; }).forEach(function (id) {
             hasAny = true;
             var label = optionLabel($desig, id);
             var $chip = $('<span class="users-active-filter-chip badge badge-info"></span>');
             $chip.attr('title', 'Designation: ' + label);
             $chip.append(document.createTextNode(label + ' '));
             var $x = $('<i class="fa fa-times remove-filter-chip" role="button" tabindex="0" title="Remove"></i>');
             $x.attr('data-filter', 'designation').attr('data-value', id);
             $chip.append($x);
             $wrap.append($chip);
         });

         var activeVal = $('#filter_active').val();
         if (activeVal !== '') {
             hasAny = true;
             var activeText = $('#filter_active option:selected').text();
             var $chip = $('<span class="users-active-filter-chip badge badge-info"></span>');
             $chip.attr('title', 'Status: ' + activeText);
             $chip.append(document.createTextNode(activeText + ' '));
             var $x = $('<i class="fa fa-times remove-filter-chip" role="button" tabindex="0" title="Remove"></i>');
             $x.attr('data-filter', 'active');
             $chip.append($x);
             $wrap.append($chip);
         }

         if (hasAny) {
             list.append($wrap);
             container.show();
         } else {
             container.hide();
         }
     }

     $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
         if (settings.nTable.id !== 'example2') return true;
         var activeFilter = $('#filter_active').val();
         var table = $('#example2').DataTable();
         var $row = $(table.row(dataIndex).node());
         var isActive = String($row.data('active')) === '1';

         var departmentFilters = ($('#filter_department_id').val() || []).filter(function (v) { return v !== ''; });
         var rowDeptId = String($row.data('departmentId') ?? $row.attr('data-department-id') ?? '');
         var departmentMatch = true;
         if (departmentFilters.length) {
             departmentMatch = departmentFilters.some(function (id) {
                 return String(id) === rowDeptId;
             });
         }

         var designationFilters = ($('#filter_designation_id').val() || []).filter(function (v) { return v !== ''; });
         var rowDesigId = String($row.data('designationId') ?? $row.attr('data-designation-id') ?? '');
         var designationMatch = true;
         if (designationFilters.length) {
             designationMatch = designationFilters.some(function (id) {
                 return String(id) === rowDesigId;
             });
         }

         var activeMatch = true;
         if (activeFilter === '1') activeMatch = isActive;
         else if (activeFilter === '0') activeMatch = !isActive;

         return departmentMatch && designationMatch && activeMatch;
     });

     // Filter panel toggle
     $('#toggleFilterBtn').click(function () {
         $('#filterWrapper').slideToggle();
     });
     $('#toggleFilterclear').click(function (e) {
         e.preventDefault();
         $('#filterWrapper').slideToggle();
     });

     $(document).on('click', '.remove-filter-chip', function (e) {
         e.preventDefault();
         var filter = $(this).data('filter');
         var value = $(this).data('value');
         if (filter === 'department') {
             var $sel = $('#filter_department_id');
             var vals = ($sel.val() || []).filter(function (v) {
                 return v !== '' && String(v) !== String(value);
             });
             $sel.val(vals.length ? vals : null).trigger('change');
         } else if (filter === 'designation') {
             var $selD = $('#filter_designation_id');
             var valsD = ($selD.val() || []).filter(function (v) {
                 return v !== '' && String(v) !== String(value);
             });
             $selD.val(valsD.length ? valsD : null).trigger('change');
         } else if (filter === 'active') {
             $('#filter_active').val('').trigger('change');
         }
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
            title: '{{ config('app.name', 'Invoice Masters') }} - Users',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Users',
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
            title: '{{ config('app.name', 'Invoice Masters') }} - Users',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Users',
            text: '<i class="fa fa-copy"></i> Copy Data',
            className: 'btn btn-primary',
            exportOptions: {
                columns: exportVisibleColumns,
                format: { body: exportFormatter }
            }
        },

        {
            extend: 'excelHtml5',
            title: '{{ config('app.name', 'Invoice Masters') }} - Users',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Users',
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
            title: '{{ config('app.name', 'Invoice Masters') }} - Users',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Users',
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
            targets: '.hide-initial',
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

     $('#filter_department_id, #filter_designation_id, #filter_active').on('change.usersFilter', function () {
         if ($.fn.DataTable.isDataTable('#example2')) {
             $('#example2').DataTable().draw();
         }
         updateUrl();
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
                checkbox.closest('tr').attr('data-active', isActive ? '1' : '0').data('active', isActive ? 1 : 0);

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

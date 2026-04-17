@extends('masterapp.layouts.app')
@push('styles')
<style>
    /* Timesheets: filter row + User filter Select2 */
    #filterWrapper .form-control.filter-input {
        height: 40px !important;
        min-height: 40px !important;
    }
    #filterWrapper .input-group .form-control {
        height: 40px !important;
        min-height: 40px !important;
    }
    .select2-container.select2-user-filter-tall .select2-selection__rendered {
        text-align: center;
        width: 100%;
        display: block;
        padding-left: 8px;
        padding-right: 24px;
        color: #444;
        line-height: 1.25;
    }
    .select2-container.select2-user-filter-tall .select2-selection__arrow {
        height: 40px !important;
    }
    .select2-container.select2-user-filter-tall .select2-selection--single {
        height: calc(2.25rem + 2px);
        border-radius: 4px;
        font-size: 14px;
    }
</style>
@endpush
@section('content')

{{-- HEADER --}}
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Timesheets</h1>
            </div>

            <div class="col-sm-6 d-flex justify-content-end add-new">
                <button type="button" class="btn btn-default ml-2" id="toggleFilterBtn">
                    <i class="fa fa-filter"></i> Filter
                </button>
                @can('create-timesheet')
                @if($canManageTimesheets ?? false)
                <button type="button"
                    class="btn btn-primary add-new ml-2"
                    data-toggle="modal"
                    data-target="#addTimesheetModal">
                <i class="fa fa-plus"></i> Add Timesheet
            </button>
                @endif
                @endcan
            </div>
        </div>
    </div>
</div>

{{-- CONTENT --}}
<section class="content">
    <div class="container-fluid">

        {{-- Filters (Server-Side Logic for DataTable to Read) --}}
        @php
            $hasFilters = request()->hasAny(['user_id', 'date_from', 'date_to', 'type']);
            $displayFilter = $hasFilters ? 'block' : 'none';
        @endphp

        <div class="filter-wrapper" id="filterWrapper" style="display: {{ $displayFilter }};">
            <a href="#" class="close-filter-btn" id="toggleFilterclear" title="Clear Filters & Close">
                &times;
            </a>
            <form id="filterForm">
                <div class="row align-items-start">
                    @if($canManageTimesheets ?? false)
                    <div class="col-md-3">
                        <label class="font-weight-bold d-block mb-1">User</label>
                        <select id="filter_user_id" name="user_id" class="form-control filter-input select2-filter-user" style="height: 40px;">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="{{ ($canManageTimesheets ?? false) ? 'col-md-4' : 'col-md-5' }}">
                        <label class="font-weight-bold d-block mb-1">Date Range (Start Time)</label>
                        <div class="input-group">
                            <input type="text" id="filter_date_from" name="date_from" class="form-control filter-input" value="{{ request('date_from') }}" placeholder="mm-dd-yyyy" autocomplete="off">
                            <div class="input-group-prepend input-group-append">
                                <span class="input-group-text border-left-0 border-right-0 bg-white">to</span>
                            </div>
                            <input type="text" id="filter_date_to" name="date_to" class="form-control filter-input" value="{{ request('date_to') }}" placeholder="mm-dd-yyyy" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="font-weight-bold d-block mb-1">Type</label>
                        <select id="filter_type" name="type" class="form-control filter-input">
                            <option value="">All Types</option>
                            <option value="normal_paid" {{ request('type') == 'normal_paid' ? 'selected' : '' }}>Normal Paid</option>
                            <option value="absent_unpaid" {{ request('type') == 'absent_unpaid' ? 'selected' : '' }}>Absent (Unpaid)</option>
                            <option value="holiday_paid" {{ request('type') == 'holiday_paid' ? 'selected' : '' }}>Holiday Paid</option>
                            <option value="sick_paid" {{ request('type') == 'sick_paid' ? 'selected' : '' }}>Sick Paid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold d-block mb-1 invisible">Apply</label>
                        <button type="button" id="applyFilterBtn" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Apply Filter</button>
                    </div>
                </div>
                <div class="row mt-2">
                     <div class="col-md-12 text-right">
                        <a href="{{ route('masterapp.timesheets.index') }}" class="btn btn-link btn-sm text-secondary">Clear All Filters</a>
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
                    <div class="card-body">

                        <table id="dataTable"
                               class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Hours</th>
                                    <th>Clock In Mode</th>
                                    <th>Type</th>
                                    <th>Notes</th>

                                    <th class="no-export no-vis">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                {{-- Loaded via AJAX --}}
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- Add Timesheet Modal --}}
<div class="modal fade" id="addTimesheetModal" tabindex="-1">
    <div class="modal-dialog">
                <form id="addTimesheetForm" action="{{ route('masterapp.timesheets.store') }}" method="POST">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Timesheet Entry</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    {{-- USER --}}
                    <div class="form-group">
                        <label>User<span class="text-danger">*</span></label>
                        <select name="user_id" id="add_user_id" class="form-control select2-add-user" required>
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- START TIME (12-hour AM/PM) --}}
                    <div class="form-group">
                        <label>Start Time<span class="text-danger">*</span></label>
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <input type="text" name="start_date" id="add_start_date" class="form-control" required placeholder="mm-dd-yyyy" autocomplete="off">
                            </div>
                            <div class="col-auto px-1">
                                <select name="start_hour" id="add_start_hour" class="form-control" required style="min-width: 4rem;">
                                    @for($h = 1; $h <= 12; $h++)
                                        <option value="{{ $h }}">{{ $h }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-auto px-1">
                                <select name="start_minute" id="add_start_minute" class="form-control" style="min-width: 4rem;">
                                    @for($m = 0; $m <= 59; $m++)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="start_ampm" id="add_start_ampm" class="form-control" required style="min-width: 4.5rem;">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="start_time" id="add_start_time" required>
                    </div>

                    {{-- END TIME (12-hour AM/PM) --}}
                    <div class="form-group">
                        <label>End Time<span class="text-danger">*</span></label>
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <input type="text" name="end_date" id="add_end_date" class="form-control" required placeholder="mm-dd-yyyy" autocomplete="off">
                            </div>
                            <div class="col-auto px-1">
                                <select name="end_hour" id="add_end_hour" class="form-control" required style="min-width: 4rem;">
                                    @for($h = 1; $h <= 12; $h++)
                                        <option value="{{ $h }}">{{ $h }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-auto px-1">
                                <select name="end_minute" id="add_end_minute" class="form-control" style="min-width: 4rem;">
                                    @for($m = 0; $m <= 59; $m++)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="end_ampm" id="add_end_ampm" class="form-control" required style="min-width: 4.5rem;">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="end_time" id="add_end_time" required>
                    </div>

                    {{-- CLOCK IN MODE --}}
                    <div class="form-group">
                        <label>Clock In Mode</label>
                        <select name="clock_in_mode" class="form-control" required>
                            <option value="office">Office</option>
                            <option value="remote">Remote</option>
                            <option value="out_of_office">Out of Office</option>
                            <option value="do_not_disturb">Do Not Disturb</option>
                        </select>
                    </div>

                    {{-- TYPE --}}
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-control" required>
                            <option value="normal_paid">Normal Paid</option>
                            <option value="absent_unpaid">Absent (Unpaid)</option>
                            <option value="holiday_paid">Holiday Paid</option>
                            <option value="sick_paid">Sick Paid</option>
                        </select>
                    </div>

                    {{-- NOTES --}}
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes"
                                  class="form-control"
                                  rows="3"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <span class="js-btn-text">Save</span>
                        <span class="js-btn-spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Timesheet Modal --}}
<div class="modal fade" id="editTimesheetModal" tabindex="-1">
  <div class="modal-dialog modal-lg" style="width:500px;">
    <form id="editTimesheetForm" method="POST">
      @csrf
      @method('PUT')

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Timesheet</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">

          <input type="hidden" id="edit_id">

          {{-- USER --}}
          <div class="form-group">
            <label>User<span class="text-danger">*</span></label>
            <select name="user_id" id="edit_user_id" class="form-control select2-edit-user" required>
              @foreach($users as $user)
                <option value="{{ $user->id }}">
                  {{ $user->first_name }} {{ $user->last_name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- START TIME (12-hour AM/PM) --}}
          <div class="form-group">
            <label>Start Time<span class="text-danger">*</span></label>
            <div class="row no-gutters align-items-center">
              <div class="col">
                <input type="text" name="start_date" id="edit_start_date" class="form-control" required placeholder="mm-dd-yyyy" autocomplete="off">
              </div>
              <div class="col-auto px-1">
                <select name="start_hour" id="edit_start_hour" class="form-control" required style="min-width: 4rem;">
                  @for($h = 1; $h <= 12; $h++)
                    <option value="{{ $h }}">{{ $h }}</option>
                  @endfor
                </select>
              </div>
              <div class="col-auto px-1">
                <select name="start_minute" id="edit_start_minute" class="form-control" style="min-width: 4rem;">
                  @for($m = 0; $m <= 59; $m++)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="col-auto">
                <select name="start_ampm" id="edit_start_ampm" class="form-control" required style="min-width: 4.5rem;">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <input type="hidden" name="start_time" id="edit_start_time" required>
          </div>

          {{-- END TIME (12-hour AM/PM) --}}
          <div class="form-group">
            <label>End Time</label>
            <div class="row no-gutters align-items-center">
              <div class="col">
                <input type="text" name="end_date" id="edit_end_date" class="form-control" placeholder="mm-dd-yyyy" autocomplete="off">
              </div>
              <div class="col-auto px-1">
                <select name="end_hour" id="edit_end_hour" class="form-control" style="min-width: 4rem;">
                  @for($h = 1; $h <= 12; $h++)
                    <option value="{{ $h }}">{{ $h }}</option>
                  @endfor
                </select>
              </div>
              <div class="col-auto px-1">
                <select name="end_minute" id="edit_end_minute" class="form-control" style="min-width: 4rem;">
                  @for($m = 0; $m <= 59; $m++)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                  @endfor
                </select>
              </div>
              <div class="col-auto">
                <select name="end_ampm" id="edit_end_ampm" class="form-control" style="min-width: 4.5rem;">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>
            <input type="hidden" name="end_time" id="edit_end_time">
          </div>

          {{-- CLOCK MODE --}}
          <div class="form-group">
            <label>Clock In Mode</label>
            <select name="clock_in_mode" id="edit_clock_in_mode" class="form-control">
              <option value="office">Office</option>
              <option value="remote">Remote</option>
              <option value="out_of_office">Out of Office</option>
              <option value="do_not_disturb">Do Not Disturb</option>
            </select>
          </div>

          {{-- TYPE --}}
          <div class="form-group">
            <label>Type</label>
            <select name="type" id="edit_type" class="form-control">
              <option value="normal_paid">Normal Paid</option>
              <option value="absent_unpaid">Absent (Unpaid)</option>
              <option value="holiday_paid">Holiday Paid</option>
            </select>
          </div>

          {{-- NOTES --}}
          <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" id="edit_notes" class="form-control"></textarea>
          </div>

        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <span class="js-btn-text">Update</span>
            <span class="js-btn-spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>


{{-- GENERIC MODAL --}}
@include('partials.generic-model')

{{-- SCRIPTS --}}
@push('scripts')
<script src="{{ asset('js/datetime-12h-helpers.js') }}"></script>
<script>
$(function () {
     // --- URL Param Handling ---
     var urlParams = new URLSearchParams(window.location.search);

     // Initialize Inputs from URL
     if(urlParams.has('user_id')) $('#filter_user_id').val(urlParams.get('user_id'));
     if(urlParams.has('date_from')) $('#filter_date_from').val(urlParams.get('date_from'));
     if(urlParams.has('date_to')) $('#filter_date_to').val(urlParams.get('date_to'));
     if(urlParams.has('type')) $('#filter_type').val(urlParams.get('type'));

     // Searchable User dropdown in filter (Select2)
     if ($('#filter_user_id').length && $.fn.select2) {
         $('#filter_user_id').select2({
             width: '100%',
             placeholder: 'All Users',
             containerCssClass: 'select2-user-filter-tall'
         });
     }
     // Searchable User dropdown in Add/Edit Timesheet modals
     if ($.fn.select2) {
         $('#add_user_id').select2({ width: '100%', placeholder: 'Select User', dropdownParent: $('#addTimesheetModal') });
         $('#edit_user_id').select2({ width: '100%', placeholder: 'Select User', dropdownParent: $('#editTimesheetModal') });
     }

     // Calculate Initial Start for Pagination
     var initialPage = parseInt(urlParams.get('page')) || 1;
     var pageLength = 10; // Default
     var initialStart = (initialPage - 1) * pageLength;

     // Function to update URL
     function updateUrl() {
         var params = new URLSearchParams();

         var user_id = $('#filter_user_id').val();
         var date_from = $('#filter_date_from').val();
         var date_to = $('#filter_date_to').val();
         var type = $('#filter_type').val();

         if(user_id) params.set('user_id', user_id);
         if(date_from) params.set('date_from', date_from);
         if(date_to) params.set('date_to', date_to);
         if(type) params.set('type', type);

         // Page
         var info = table.page.info();
         var currentPage = info.page + 1;
         if (currentPage > 1) params.set('page', currentPage);

         var newUrl = window.location.pathname + '?' + params.toString();
         history.pushState(null, '', newUrl);

         updateActiveFilterBadges();
     }

     // Function to render Active Filter Badges
     function updateActiveFilterBadges() {
         var container = $('#activeFilters');
         var list = $('#activeFiltersList');
         list.empty();
         var hasFilter = false;

         function addBadge(label, value, inputId) {
             if(value) {
                 hasFilter = true;
                 var badge = $('<span class="badge badge-info ml-2 p-2" style="font-size: 100%;">' + label + ': ' + value + ' <i class="fa fa-times cursor-pointer remove-filter" data-target="' + inputId + '" style="margin-left:5px;"></i></span>');
                 list.append(badge);
             }
         }

         var userVal = $('#filter_user_id').val();
         var userText = $('#filter_user_id option:selected').text();
         if(userVal) {
             hasFilter = true;
             var badge = $('<span class="badge badge-info ml-2 p-2" style="font-size: 100%;">User: ' + userText + ' <i class="fa fa-times cursor-pointer remove-filter" data-target="#filter_user_id" style="margin-left:5px;"></i></span>');
             list.append(badge);
         }

         addBadge('Date From', $('#filter_date_from').val(), '#filter_date_from');
         addBadge('Date To', $('#filter_date_to').val(), '#filter_date_to');

         var typeVal = $('#filter_type').val();
         var typeText = $('#filter_type option:selected').text();
         if(typeVal) {
             hasFilter = true;
             var badge = $('<span class="badge badge-info ml-2 p-2" style="font-size: 100%;">Type: ' + typeText + ' <i class="fa fa-times cursor-pointer remove-filter" data-target="#filter_type" style="margin-left:5px;"></i></span>');
             list.append(badge);
         }

         if(hasFilter) container.show();
         else container.hide();
     }

     // Filter Toggle
     $('#toggleFilterBtn').click(function() {
         $('#filterWrapper').slideToggle();
     });

     // Apply Filter Button
     $('#applyFilterBtn').click(function() {
         table.page(0).draw(false); // Reset to page 1 on filter
         updateUrl();
     });

      $('#toggleFilterclear').click(function() {
      $('#filterWrapper').slideToggle();
    });

     // Remove Filter Logic
     $(document).on('click', '.remove-filter', function() {
         var target = $(this).data('target');
         $(target).val(''); // Clear value
         table.page(0).draw(false);
         updateUrl();
     });

    const table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        displayStart: initialStart, // Key for initial pagination

        ajax: {
            url: "{{ route('masterapp.timesheets.data') }}",
            type: "GET",
            data: function (d) {
                d.user_id = $('#filter_user_id').val();
                d.date_from = $('#filter_date_from').val();
                d.date_to = $('#filter_date_to').val();
                d.type = $('#filter_type').val();
            }
        },

        columns: [
            { data: 'user', name: 'user' },
            { data: 'start_time', name: 'start_time', render: function (data) { return formatTableCellDateTime(data); } },
            { data: 'end_time', name: 'end_time', render: function (data) { return formatTableCellDateTime(data); } },
            { data: 'hours', name: 'hours' },
            { data: 'clock_in_mode', name: 'clock_in_mode' },
            { data: 'type', name: 'type' },
            { data: 'notes', name: 'notes' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],

        dom: '<"top"Biplf>rt<"bottom bottomAlign"ip><"clear">',
        buttons: [],

        language: {
            lengthMenu: 'Show _MENU_',
            paginate: {
                next: '<i class="fa fa-angle-double-right"></i>',
                previous: '<i class="fa fa-angle-double-left"></i>'
            },
            search: ''
        },
        dom: '<"top"Biplf>rt<"bottom bottomAlign"ip><"clear">',
      buttons: [
          {
            extend: 'print',
            title: '{{ config('app.name', 'Invoice Masters') }} - Timesheets',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Timesheets',
            text: '<i class="fa fa-print"></i> Print',
            className: 'btn btn-secondary',
            exportOptions: {
                columns: exportVisibleColumns
            },
            customize: function (win) {

                $(win.document.body).css('font-size', '9px');

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
                            padding: 4px !important;
                        }
                    </style>
                `);
            }
        },
        {
            extend: 'copyHtml5',
            title: '{{ config('app.name', 'Invoice Masters') }} - Timesheets',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Timesheets',
            text: '<i class="fa fa-copy"></i> Copy Data',
            className: 'btn btn-primary',
            exportOptions: {
                columns: exportVisibleColumns
            }
        },
        {
            extend: 'excelHtml5',
            title: '{{ config('app.name', 'Invoice Masters') }} - Timesheets',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Timesheets',
            text: '<i class="fa fa-download"></i> Excel',
            className: 'btn btn-success',
            exportOptions: {
                columns: exportVisibleColumns
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
            title: '{{ config('app.name', 'Invoice Masters') }} - Timesheets',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Timesheets',
            text: '<i class="fa fa-download"></i> PDF',
            className: 'btn btn-danger',
            orientation: 'landscape',
            pageSize: 'A4',
            exportOptions: {
                columns: exportVisibleColumns
            },
            customize: function (doc) {

                const table = doc.content.find(c => c.table).table;
                const colCount = table.body[0].length;

                /* Page + font */
                doc.pageMargins = [15, 12, 15, 12];
                doc.defaultStyle.fontSize = 8;
                doc.styles.tableHeader.fontSize = 9;

                /*  Best fit for small column counts */
                table.widths = Array(colCount).fill('*');

                /* Wrapping & spacing */
                doc.styles.tableBodyEven = {
                    margin: [0, 3, 0, 3]
                };
                doc.styles.tableBodyOdd = {
                    margin: [0, 3, 0, 3]
                };

                /* Header styling */
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
            targets: [], // hidden initially
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

            $('.top .dataTables_length, .top .dataTables_paginate')
                .wrapAll('<div class="length_pagination"></div>');

            $('.top .dataTables_info, .top .length_pagination')
                .wrapAll('<div class="show_page_align"></div>');

            $('.top .dt-buttons, .top .dataTables_filter')
                .wrapAll('<div class="btn_filter_align"></div>');

            const $searchInput = $('.dataTables_filter input');
            $searchInput.attr('placeholder', 'Search..');
            // wrap input
            $searchInput.wrap('<div class="search-input-wrapper"></div>');
            // add class
            $searchInput.addClass('search-input');
            // ADD SEARCH ICON ELEMENT
            $searchInput.before('<i class="fa fa-search"></i>');

            // Initialize Badges based on initial URL params
            updateActiveFilterBadges();
        }
    });

    // Update URL on Page Change
    table.on('page.dt', function () {
        // We need to wait for the redraw to complete slightly or just use the event
        // Actually the event fires before draw. Let's use defer logic or just setTimeout 0
        setTimeout(updateUrl, 0);
    });
});

// OPEN CREATE MODAL
$('#addTimesheetBtn').on('click', function () {
        ModalFormManager.openModal(
            $(this).data('url'),
            $(this).data('title')
        );
});

    // AJAX FORM HANDLING
handleAjaxForm('#form-timesheets', {
        modalToClose: '#genericModal',
        reloadOnSuccess: true
});

  // DELETE HANDLER
handleDelete();

// Toast for timesheet add/update (consistent with other masterapp pages)
var timesheetToast = typeof Swal !== 'undefined' && Swal.mixin({
    toast: true,
    position: 'top-end',
    icon: 'success',
    timer: 5000,
    timerProgressBar: true,
    showConfirmButton: false
});

function showTimesheetToast(title, message) {
    if (timesheetToast) {
        timesheetToast.fire({ title: title || 'Success', text: message || '' });
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: title || 'Success', text: message || '', timer: 5000, timerProgressBar: true, showConfirmButton: false });
    }
}

// Prevent full-page submit so response is always handled by AJAX (toast instead of raw JSON)
$('#addTimesheetForm').on('submit', function (e) {
    e.preventDefault();
});
$('#editTimesheetForm').on('submit', function (e) {
    e.preventDefault();
});

$('#addTimesheetForm').on('change', '#add_start_date, #add_start_hour, #add_start_minute, #add_start_ampm, #add_end_date, #add_end_hour, #add_end_minute, #add_end_ampm', function () { setFormHiddenTimes('add'); });
$('#editTimesheetForm').on('change', '#edit_start_date, #edit_start_hour, #edit_start_minute, #edit_start_ampm, #edit_end_date, #edit_end_hour, #edit_end_minute, #edit_end_ampm', function () { setFormHiddenTimes('edit'); });

// Add form: auto-set end time to start time + 8 hours
function setAddEndTimeFromStartPlus8() {
    var startDateStr = $('#add_start_date').val();
    var startHour = $('#add_start_hour').val();
    var startMinute = $('#add_start_minute').val();
    var startAmpm = $('#add_start_ampm').val();
    if (!startDateStr || !startHour || !startAmpm) return;
    var startDtStr = typeof buildDateTimeFrom12h !== 'undefined' && buildDateTimeFrom12h(startDateStr, startHour, startMinute || '00', startAmpm);
    if (!startDtStr) return;
    var parts = startDtStr.split(/[\s:-]/);
    if (parts.length < 5) return;
    var d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10), parseInt(parts[3], 10), parseInt(parts[4], 10) || 0, 0);
    d.setHours(d.getHours() + 8);
    var y = d.getFullYear(), mo = d.getMonth() + 1, day = d.getDate();
    var ymd = y + '-' + String(mo).padStart(2, '0') + '-' + String(day).padStart(2, '0');
    var endDateStr = typeof toMMDDYYYY !== 'undefined' ? toMMDDYYYY(ymd) : (String(mo).padStart(2, '0') + '-' + String(day).padStart(2, '0') + '-' + y);
    var h24 = d.getHours(), min = d.getMinutes();
    var h12 = h24 === 0 ? 12 : h24 > 12 ? h24 - 12 : h24;
    var ampm = h24 < 12 ? 'AM' : 'PM';
    var minStr = String(min).padStart(2, '0');
    $('#add_end_date').val(endDateStr);
    if ($('#add_end_date')[0]._flatpickr) $('#add_end_date')[0]._flatpickr.setDate(endDateStr, false);
    $('#add_end_hour').val(String(h12));
    $('#add_end_minute').val(minStr);
    $('#add_end_ampm').val(ampm);
    setFormHiddenTimes('add');
}
$('#addTimesheetForm').on('change', '#add_start_date, #add_start_hour, #add_start_minute, #add_start_ampm', function () { setAddEndTimeFromStartPlus8(); });

// Datepicker (flatpickr) – mm-dd-yyyy on date inputs
var timesheetDatepickerOptions = { dateFormat: 'm-d-Y', allowInput: true };
// Filter dates: show mm-dd-yyyy, keep value yyyy-mm-dd for server/URL
var filterDatepickerOptions = { dateFormat: 'Y-m-d', altInput: true, altFormat: 'm-d-Y', allowInput: true };
if (typeof flatpickr !== 'undefined') {
    flatpickr('#add_start_date', timesheetDatepickerOptions);
    flatpickr('#add_end_date', timesheetDatepickerOptions);
    flatpickr('#edit_start_date', timesheetDatepickerOptions);
    flatpickr('#edit_end_date', timesheetDatepickerOptions);
    if ($('#filter_date_from').length) flatpickr('#filter_date_from', filterDatepickerOptions);
    if ($('#filter_date_to').length) flatpickr('#filter_date_to', filterDatepickerOptions);
}

//Edit Timesheet Js
$(document).on('click', '.js-edit-timesheet', function () {
    const btn   = $(this);
    const url   = btn.data('url');
    const modal = $('#editTimesheetModal');

    $.get(url, function (data) {
        modal.find('#edit_user_id').val(data.user_id).trigger('change');
        var start12 = parseDateTimeTo12h(data.start_time || '');
        var startDateStr = toMMDDYYYY(start12.date);
        modal.find('#edit_start_date').val(startDateStr);
        if (modal.find('#edit_start_date')[0]._flatpickr) modal.find('#edit_start_date')[0]._flatpickr.setDate(startDateStr, false);
        modal.find('#edit_start_hour').val(start12.hour);
        modal.find('#edit_start_minute').val(start12.minute);
        modal.find('#edit_start_ampm').val(start12.ampm);
        var end12 = parseDateTimeTo12h(data.end_time || '');
        var endDateStr = toMMDDYYYY(end12.date);
        modal.find('#edit_end_date').val(endDateStr);
        if (modal.find('#edit_end_date')[0]._flatpickr) modal.find('#edit_end_date')[0]._flatpickr.setDate(endDateStr, false);
        modal.find('#edit_end_hour').val(end12.hour);
        modal.find('#edit_end_minute').val(end12.minute);
        modal.find('#edit_end_ampm').val(end12.ampm);
        setFormHiddenTimes('edit');
        modal.find('#edit_clock_in_mode').val(data.clock_in_mode);
        modal.find('#edit_type').val(data.type);
        modal.find('#edit_notes').val(data.notes);

        modal.find('#editTimesheetForm')
            .attr('action', '/master-app/timesheets/' + data.id);

        modal.modal('show');
    });
});

// Add Timesheet modal – inline validation
$.validator.addMethod('greaterThanStart', function(value, element) {
    var form = $(element).closest('form');
    var startVal = form.find('[name="start_time"]').val();
    var endVal = form.find('[name="end_time"]').val();
    if (!endVal || !startVal) return true;
    return new Date(endVal) >= new Date(startVal);
}, "The end time cannot be before start time.");

$('#addTimesheetForm').validate({
    rules: {
        user_id: {
            required: true
        },
        start_time: {
            required: true
        },
        end_time: {
            required: true,
            greaterThanStart: true
        },
        clock_in_mode: { required: true },
        type: { required: true }
    },
    messages: {
        user_id: {
            required: "Please select a user"
        },
        start_time: {
            required: "Please select start time"
        },
        end_time: {
            required: "Please select end time",
            greaterThanStart: "The end time cannot be before start time."
        },
        clock_in_mode: { required: "Please select clock in mode." },
        type: { required: "Please select type." }
    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.form-group').append(error);
    },
    highlight: function (element) {
        $(element).addClass('is-invalid');
    },
    unhighlight: function (element) {
        $(element).removeClass('is-invalid');
    },
    onfocusout: function (element) {
        $(element).valid();
    },
    onkeyup: function (element) {
        $(element).valid();
    },
    submitHandler: function (form) {
        setFormHiddenTimes('add');
        var $form = $(form);
        var $btn = $form.find('button[type="submit"]');
        var $text = $form.find('.js-btn-text');
        var $spinner = $form.find('.js-btn-spinner');
        $btn.prop('disabled', true);
        $text.addClass('d-none');
        $spinner.removeClass('d-none');
        var formData = new FormData(form);
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#addTimesheetModal').modal('hide');
                $form[0].reset();
                $btn.prop('disabled', false);
                $text.removeClass('d-none');
                $spinner.addClass('d-none');
                showTimesheetToast('Timesheet entry created', res.message || 'Timesheet entry created successfully.');
                if ($.fn.DataTable && $('#dataTable').length && $('#dataTable').DataTable()) {
                    $('#dataTable').DataTable().ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false);
                $text.removeClass('d-none');
                $spinner.addClass('d-none');
                var msg = xhr.responseJSON?.message || "An error occurred.";
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Error', text: msg, timer: 3000, showConfirmButton: false });
                }
            }
        });
        return false;
    }
});

$('#addTimesheetModal').on('shown.bs.modal', function () {
    $('#addTimesheetForm').find('.is-invalid').removeClass('is-invalid');
    $('#addTimesheetForm').find('.invalid-feedback').remove();
});
// Reset add timesheet form when modal is closed without saving (Close button, X, or backdrop)
$('#addTimesheetModal').on('hidden.bs.modal', function () {
    $('#addTimesheetForm')[0].reset();
    if ($('#add_user_id').length && $('#add_user_id').data('select2')) $('#add_user_id').val('').trigger('change');
    if ($('#add_start_date')[0]._flatpickr) $('#add_start_date')[0]._flatpickr.clear();
    if ($('#add_end_date')[0]._flatpickr) $('#add_end_date')[0]._flatpickr.clear();
    $('#addTimesheetForm').find('.is-invalid').removeClass('is-invalid');
    $('#addTimesheetForm').find('.invalid-feedback').remove();
});
$('#addTimesheetForm').on('change', 'select, input[type="date"], #add_start_hour, #add_start_minute, #add_start_ampm, #add_end_hour, #add_end_minute, #add_end_ampm', function () {
    setFormHiddenTimes('add');
    $(this).valid();
});

// Edit Timesheet modal – inline validation (end_time not required)
$.validator.addMethod('greaterThanStartEdit', function(value, element) {
    var form = $(element).closest('form');
    var startVal = form.find('[name="start_time"]').val();
    var endVal = form.find('[name="end_time"]').val();
    if (!endVal || !startVal) return true;
    return new Date(endVal) >= new Date(startVal);
}, "The end time cannot be before start time.");

$('#editTimesheetForm').validate({
    rules: {
        user_id: { required: true },
        start_time: { required: true },
        end_time: { greaterThanStartEdit: true },
        clock_in_mode: { required: true },
        type: { required: true }
    },
    messages: {
        user_id: { required: "Please select a user" },
        start_time: { required: "Please select start time" },
        end_time: { greaterThanStartEdit: "The end time cannot be before start time." },
        clock_in_mode: { required: "Please select clock in mode." },
        type: { required: "Please select type." }
    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.form-group').append(error);
    },
    highlight: function (element) {
        $(element).addClass('is-invalid');
    },
    unhighlight: function (element) {
        $(element).removeClass('is-invalid');
    },
    onfocusout: function (element) {
        $(element).valid();
    },
    onkeyup: function (element) {
        $(element).valid();
    },
    submitHandler: function (form) {
        setFormHiddenTimes('edit');
        var $form = $(form);
        var $btn = $form.find('button[type="submit"]');
        var $text = $form.find('.js-btn-text');
        var $spinner = $form.find('.js-btn-spinner');
        $btn.prop('disabled', true);
        $text.addClass('d-none');
        $spinner.removeClass('d-none');
        var formData = new FormData(form);
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#editTimesheetModal').modal('hide');
                $btn.prop('disabled', false);
                $text.removeClass('d-none');
                $spinner.addClass('d-none');
                showTimesheetToast('Timesheet updated', res.message || 'Timesheet updated successfully.');
                if ($.fn.DataTable && $('#dataTable').length && $('#dataTable').DataTable()) {
                    $('#dataTable').DataTable().ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false);
                $text.removeClass('d-none');
                $spinner.addClass('d-none');
                var msg = xhr.responseJSON?.message || "An error occurred.";
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Error', text: msg, timer: 3000, showConfirmButton: false });
                }
            }
        });
        return false;
    }
});

$('#editTimesheetModal').on('shown.bs.modal', function () {
    $('#editTimesheetForm').find('.is-invalid').removeClass('is-invalid');
    $('#editTimesheetForm').find('.invalid-feedback').remove();
});
$('#editTimesheetForm').on('change', 'select, input[type="date"], #edit_start_hour, #edit_start_minute, #edit_start_ampm, #edit_end_hour, #edit_end_minute, #edit_end_ampm', function () {
    setFormHiddenTimes('edit');
    $(this).valid();
});

// Export only visible columns (excluding action column)
function exportVisibleColumns(idx, data, node) {
    const table = $('#dataTable').DataTable();

    // Exclude action column
    if ($(node).hasClass('no-export') || $(node).hasClass('no-vis')) {
        return false;
    }

    // Export only columns enabled via Column Visibility
    return table.column(idx).visible();
}


</script>
@endpush
@endsection

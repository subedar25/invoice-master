@extends('masterapp.layouts.app')
@section('content')
@push('styles')
<style>
  #dataTable_wrapper .search-input-wrapper {
    position: relative;
    display: inline-block;
    max-width: 100%;
  }

  #dataTable_wrapper .search-input-wrapper .fa-search {
    position: absolute;
    left: 17px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
  }

  #dataTable_wrapper .dataTables_filter input.search-input {
    width: min(455px, 100%) !important;
    max-width: 100%;
    padding-left: 34px !important;
    box-sizing: border-box;
  }
</style>
@endpush
{{-- HEADER --}}
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Locations</h1>
            </div>

            <div class="col-sm-6 d-flex justify-content-end add-new">
                <button type="button" class="btn btn-default ml-2" id="toggleFilterBtn">
                    <i class="fa fa-filter"></i> Filter
                </button>

                @can('create-location')
                <button type="button"
                    class="btn btn-primary add-new ml-2"
                    data-toggle="modal"
                    data-target="#addLocationModal">
                <i class="fa fa-plus"></i> Add Location
            </button>
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
            $hasFilters = request()->hasAny(['city', 'state', 'country']);
            $displayFilter = $hasFilters ? 'block' : 'none';
        @endphp

        <div class="filter-wrapper" id="filterWrapper" style="display: {{ $displayFilter }};">
            <a href="#" class="close-filter-btn" id="toggleFilterclear" title="Clear Filters & Close">
                &times;
            </a>
            <form id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="font-weight-bold">City</label>
                        <input type="text" id="filter_city" name="city" class="form-control filter-input" value="{{ request('city') }}" placeholder="Filter by city">
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">State</label>
                        <select id="filter_state" name="state" class="form-control filter-input">
                            <option value="">All States</option>
                            @foreach(config('states') as $state)
                                <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- <div class="col-md-3">
                        <label class="font-weight-bold">Country</label>
                        <input type="text" id="filter_country" name="country" class="form-control filter-input" value="{{ request('country') }}" placeholder="Filter by country">
                    </div> --}}
                {{-- </div> --}}
                {{-- <div class="row mt-2"> --}}
                    <div class="col-md-3">
                        <button type="button" id="applyFilterBtn" class="btn btn-primary"><i class="fa fa-filter"></i> Apply Filter</button>
                    </div>
                    </div>
                    <div class="row mt-2">
                     <div class="col-md-12 text-right">
                        <a href="{{ route('masterapp.locations.index') }}" class="btn btn-link btn-sm text-secondary">Clear All Filters</a>
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
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Country</th>
                                    <th>State</th>
                                    <th>City</th>
                                    <th>PIN Code</th>
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

{{-- Add Location Modal --}}
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
                <form id="addLocationForm" action="{{ route('masterapp.locations.store') }}" method="POST">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Location</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        {{-- NAME --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name<span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>

                        {{-- PHONE --}}
                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" pattern="^\+1\s\(\d{3}\)\s\d{3}-\d{4}$" placeholder="+1 (123) 456 7890" maxlength="17" value="+1 (">
                                  <!-- Hidden clean value -->
                                <input type="hidden" name="phone" id="phone_raw">
                            </div>
                        </div> --}}
                    </div>

                    {{-- ADDRESS --}}
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="row">
                        {{-- COUNTRY --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Country</label>
                                <select name="country" id="add_country" class="form-control">
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option
                                            value="{{ $country->name }}"
                                            data-id="{{ $country->id }}"
                                            {{ (int) $country->id === (int) $defaultCountryId ? 'selected' : '' }}
                                        >
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- STATE --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>State</label>
                                <select name="state" id="add_state" class="form-control">
                                    <option value="">Select State</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        {{-- CITY --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="city" class="form-control">
                            </div>
                        </div>
                        {{-- POSTAL CODE --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>PIN Code</label>
                                <input type="text" name="postal_code" class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- SHOW MAP --}}
                    {{-- <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="show_map" name="show_map" value="1">
                                    <label class="custom-control-label" for="show_map">Show Map</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="show_map_link" name="show_map_link" value="1">
                                    <label class="custom-control-label" for="show_map_link">Show Map Link</label>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <span id="btn-add-text">Save</span>
                        <span id="btn-add-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Location Modal --}}
<div class="modal fade" id="editLocationModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="editLocationForm" method="POST">
      @csrf
      @method('PUT')

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Location</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
            <input type="hidden" id="edit_id">

            <div class="row">
                {{-- NAME --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Name<span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                </div>

                {{-- PHONE --}}
                {{-- <div class="col-md-6">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control" pattern="^\+1\s\(\d{3}\)\s\d{3}-\d{4}$" placeholder="+1 (123) 456-7890" maxlength="17" value="+1 (">
                    </div>
                </div> --}}
            </div>

            {{-- ADDRESS --}}
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
            </div>

            <div class="row">
                  {{-- COUNTRY --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Country</label>
                        <select name="country" id="edit_country" class="form-control">
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}" data-id="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- STATE --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>State</label>
                        <select name="state" id="edit_state" class="form-control">
                            <option value="">Select State</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                {{-- CITY --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" id="edit_city" class="form-control">
                    </div>
                </div>
                {{-- POSTAL CODE --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>PIN Code</label>
                        <input type="text" name="postal_code" id="edit_postal_code" class="form-control">
                    </div>
                </div>
            </div>
            {{-- SHOW MAP --}}
            {{-- <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="hidden" name="show_map" value="0">
                            <input type="checkbox" class="custom-control-input" id="edit_show_map" name="show_map" value="1">
                            <label class="custom-control-label" for="edit_show_map">Show Map</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="hidden" name="show_map_link" value="0">
                            <input type="checkbox" class="custom-control-input" id="edit_show_map_link" name="show_map_link" value="1">
                            <label class="custom-control-label" for="edit_show_map_link">Show Map Link</label>
                        </div>
                    </div>
                </div>
            </div> --}}

        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <span id="btn-edit-text">Update</span>
            <span id="btn-edit-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
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
<script src="{{ asset('js/ajax-form-handler.js') }}"></script>
<script>
$(function () {
    // Custom validation methods
    $.validator.addMethod(
        'lettersOnly',
        function (value, element) {
            return this.optional(element) || /^[A-Za-z\s]+$/.test(value);
        },
        'Only letters and spaces are allowed'
    );

    $.validator.addMethod(
        'alphanumeric',
        function (value, element) {
            return this.optional(element) || /^[A-Za-z0-9\s]+$/.test(value);
        },
        'Only letters, numbers and spaces are allowed'
    );

    $.validator.addMethod(
        'digitsOnly',
        function (value, element) {
            return this.optional(element) || /^[0-9]+$/.test(value);
        },
        'Only numbers are allowed'
    );

    // Toast (GLOBAL)
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        showClass: { popup: 'animate__animated animate__fadeInUp' },
        hideClass: { popup: 'animate__animated animate__fadeOutDown' }
    });

});

$(function () {
     const statesByCountry = @json($statesByCountry ?? []);
     const defaultCountryId = @json((int) ($defaultCountryId ?? 0));

     function getSelectedCountryId($countrySelect) {
         return parseInt($countrySelect.find(':selected').data('id'), 10) || null;
     }

     function populateStateOptions($stateSelect, countryId, selectedStateName = '') {
         $stateSelect.empty().append('<option value="">Select State</option>');

         if (!countryId) {
             return;
         }

         const states = statesByCountry[countryId] || [];
         states.forEach(function (state) {
             const isSelected = selectedStateName && selectedStateName === state.name;
             $stateSelect.append(
                 $('<option>', {
                     value: state.name,
                     text: state.name,
                     selected: isSelected
                 })
             );
         });
     }

     // Initialize Add modal with default country = India and linked states.
     if (defaultCountryId) {
         populateStateOptions($('#add_state'), defaultCountryId);
     }

     $('#add_country').on('change', function () {
         populateStateOptions($('#add_state'), getSelectedCountryId($(this)));
     });

     $('#edit_country').on('change', function () {
         populateStateOptions($('#edit_state'), getSelectedCountryId($(this)));
     });

     // --- URL Param Handling ---
     var urlParams = new URLSearchParams(window.location.search);

     // Initialize Inputs from URL
     if(urlParams.has('city')) $('#filter_city').val(urlParams.get('city'));
     if(urlParams.has('state')) $('#filter_state').val(urlParams.get('state'));
     if(urlParams.has('country')) $('#filter_country').val(urlParams.get('country'));

     // Calculate Initial Start for Pagination
     var initialPage = parseInt(urlParams.get('page')) || 1;
     var pageLength = 10; // Default
     var initialStart = (initialPage - 1) * pageLength;

     // Function to update URL
     function updateUrl() {
         var params = new URLSearchParams();

         var city = $('#filter_city').val();
         var state = $('#filter_state').val();
         var country = $('#filter_country').val();

         if(city) params.set('city', city);
         if(state) params.set('state', state);
         if(country) params.set('country', country);

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

         addBadge('City', $('#filter_city').val(), '#filter_city');
         addBadge('State', $('#filter_state').val(), '#filter_state');
         addBadge('Country', $('#filter_country').val(), '#filter_country');

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
        order: [[0, 'asc']],

        ajax: {
            url: "{{ route('masterapp.locations.data') }}",
            type: "GET",
            data: function (d) {
                d.city = $('#filter_city').val();
                d.state = $('#filter_state').val();
                d.country = $('#filter_country').val();
            }
        },

        columns: [
            { data: 'name', name: 'name' },
            { data: 'address', name: 'address' },
            { data: 'country', name: 'country' },
            { data: 'state', name: 'state' },
            { data: 'city', name: 'city' },
            { data: 'postal_code', name: 'postal_code' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],

        dom: '<"top"Biplf>rt<"bottom bottomAlign"ip><"clear">',

        language: {
            lengthMenu: 'Show _MENU_',
            paginate: {
                next: '<i class="fa fa-angle-double-right"></i>',
                previous: '<i class="fa fa-angle-double-left"></i>'
            },
            search: ''
        },

        buttons: [
          {
            extend: 'print',
            title: '{{ config('app.name', 'Invoice Masters') }} - Locations',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Locations',
            text: '<i class="fa fa-print"></i> Print',
            className: 'btn btn-secondary',
            exportOptions: {
                columns: exportVisibleColumns
            },
            customize: function (win) {
                $(win.document.body).css('font-size', '9px');

                $(win.document.head).append(`
                    <style>
                        @page { size: A4 landscape; margin: 8mm; }
                        table { width: 100% !important; table-layout: fixed; }
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
            title: '{{ config('app.name', 'Invoice Masters') }} - Locations',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Locations',
            text: '<i class="fa fa-copy"></i> Copy Data',
            className: 'btn btn-primary',
            exportOptions: {
                columns: exportVisibleColumns
            }
        },
        {
            extend: 'excelHtml5',
            title: '{{ config('app.name', 'Invoice Masters') }} - Locations',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Locations',
            text: '<i class="fa fa-download"></i> Excel',
            className: 'btn btn-success',
            exportOptions: {
                columns: exportVisibleColumns
            }
        },
        {
            extend: 'pdfHtml5',
            title: '{{ config('app.name', 'Invoice Masters') }} - Locations',
            filename: '{{ config('app.name', 'Invoice Masters') }} - Locations',
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

                doc.pageMargins = [8, 8, 8, 8];
                doc.defaultStyle.fontSize = 7;
                doc.styles.tableHeader.fontSize = 7.5;

                // Locations = medium-wide table → star widths
                table.widths = Array(colCount).fill('*');

                doc.styles.tableBodyEven = { margin: [0, 2, 0, 2] };
                doc.styles.tableBodyOdd  = { margin: [0, 2, 0, 2] };
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
            targets: -1,
            orderable: false,
            searchable: false,
            className: 'no-export no-vis'
        },
        {
            targets: -1,
            orderable: false,
            searchable: true,
            className: 'no-vis'
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

    // OPEN CREATE MODAL
    $('#addLocationBtn').on('click', function () {
        ModalFormManager.openModal(
            $(this).data('url'),
            $(this).data('title')
        );
    });

    // AJAX FORM HANDLING
    handleAjaxForm('#form-locations', {
        modalToClose: '#genericModal',
        reloadOnSuccess: true
    });

    // DELETE HANDLER
    handleDelete();

    // Edit Location Js
    $(document).on('click', '.js-edit-location', function () {
        const btn   = $(this);
        const url   = btn.data('url');
        const modal = $('#editLocationModal');

        $.get(url, function (data) {
            modal.find('#edit_id').val(data.id);
            modal.find('#edit_name').val(data.name);
            modal.find('#edit_address').val(data.address);
            modal.find('#edit_city').val(data.city);
            modal.find('#edit_country').val(data.country);
            const selectedCountryId = getSelectedCountryId(modal.find('#edit_country'));
            populateStateOptions(modal.find('#edit_state'), selectedCountryId, data.state);
            modal.find('#edit_postal_code').val(data.postal_code);
            modal.find('#edit_phone').val(data.phone);
            // modal.find('#edit_show_map').prop('checked', data.show_map);
            // modal.find('#edit_show_map_link').prop('checked', data.show_map_link);

            modal.find('#editLocationForm')
                .attr('action', '/master-app/locations/' + data.id);

            modal.modal('show');
        });
    });
});

$(function () {
    // jQuery Validate + AJAX for Add Location Form
    $('#addLocationForm').validate({
        submitHandler: function (form) {
            const $form = $(form);
            const $btn = $form.find('button[type="submit"]');
            $btn.prop('disabled', true);
            $('#btn-add-text').addClass('d-none');
            $('#btn-add-spinner').removeClass('d-none');

            const formData = new FormData(form);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#btn-add-text').removeClass('d-none');
                    $('#btn-add-spinner').addClass('d-none');
                    $btn.prop('disabled', false);

                    // Reset form
                    $form[0].reset();

                    // Close modal and show success
                    $('#addLocationModal').modal('hide');
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: "success",
                        title: "Location Created!",
                        text: res.message || 'Location created successfully.',
                        timer: 5000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    $btn.prop('disabled', false);
                    $('#btn-add-text').removeClass('d-none');
                    $('#btn-add-spinner').addClass('d-none');

                    let errorText = xhr.responseJSON?.message || "An unexpected error occurred.";
                    if (xhr.responseJSON?.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorText = errors.join(', ');
                    }
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: "error",
                        title: "Error",
                        text: errorText,
                        timer: 5000,
                        showConfirmButton: false
                    });
                }
            });

            return false;
        },

        rules: {
            name: {
                required: true,
                minlength: 2,
                maxlength: 255,
                remote: {
                    url: "{{ route('masterapp.locations.check-unique') }}",
                    type: "post",
                    data: {
                        _token: function() { return $('meta[name="csrf-token"]').attr('content'); }
                    }
                }
            },
            address: {
                minlength: 5,
                maxlength: 1000
            },
            city: {
                lettersOnly: true,
                minlength: 2,
                maxlength: 100
            },
            country: {
                minlength: 2,
                maxlength: 100
            },
            postal_code: {
                alphanumeric: true,
                minlength: 3,
                maxlength: 20
            },
            phone: {
                maxlength: 17
            }
        },

        messages: {
            name: {
                required: "Please enter a location name",
                minlength: "Location name must be at least 2 characters",
                maxlength: "Location name cannot exceed 255 characters",
                remote: "This location name is already in use"
            },
            address: {
                minlength: "Address must be at least 5 characters",
                maxlength: "Address cannot exceed 1000 characters"
            },
            city: {
                lettersOnly: "City name cannot contain numbers or symbols",
                minlength: "City name must be at least 2 characters",
                maxlength: "City name cannot exceed 100 characters"
            },
            country: {
                minlength: "Country name must be at least 2 characters",
                maxlength: "Country name cannot exceed 100 characters"
            },
            postal_code: {
                alphanumeric: "Postal code can only contain letters and numbers",
                minlength: "Postal code must be at least 3 characters",
                maxlength: "Postal code cannot exceed 20 characters"
            },
            phone: {
            maxlength: "Phone number cannot exceed +1 (123) 456-7890"
            }
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
    }
});
});

$(function () {
    // Reset Add Location form when modal is closed (so next open shows empty form)
    $('#addLocationModal').on('hidden.bs.modal', function () {
        var $form = $('#addLocationForm');
        $form[0].reset();
        const $addCountry = $('#add_country');
        const $addState = $('#add_state');
        const defaultCountryId = parseInt(@json((int) ($defaultCountryId ?? 0)), 10) || null;
        if (defaultCountryId) {
            $addCountry.find('option').prop('selected', false);
            $addCountry.find('option[data-id="' + defaultCountryId + '"]').prop('selected', true);

            const statesByCountry = @json($statesByCountry ?? []);
            $addState.empty().append('<option value="">Select State</option>');
            (statesByCountry[defaultCountryId] || []).forEach(function (state) {
                $addState.append($('<option>', { value: state.name, text: state.name }));
            });
        } else {
            $addState.empty().append('<option value="">Select State</option>');
        }
        var validator = $form.data('validator');
        if (validator) {
            validator.resetForm();
        }
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
    });
});

$(function () {
    // jQuery Validate + AJAX for Edit Location Form
    $('#editLocationForm').validate({
    submitHandler: function (form) {
        const $form = $(form);
        const $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true);
        $('#btn-edit-text').addClass('d-none');
        $('#btn-edit-spinner').removeClass('d-none');

        const formData = new FormData(form);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#btn-edit-text').removeClass('d-none');
                $('#btn-edit-spinner').addClass('d-none');
                $btn.prop('disabled', false);

                // Close modal and show success
                $('#editLocationModal').modal('hide');
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: "success",
                    title: "Location Updated!",
                    text: res.message || 'Location updated successfully.',
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function (xhr) {
                $btn.prop('disabled', false);
                $('#btn-edit-text').removeClass('d-none');
                $('#btn-edit-spinner').addClass('d-none');

                let errorText = xhr.responseJSON?.message || "An unexpected error occurred.";
                if (xhr.responseJSON?.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorText = errors.join(', ');
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: "error",
                    title: "Error",
                    text: errorText,
                    timer: 5000,
                    showConfirmButton: false
                });
            }
        });

        return false;
    },

    rules: {
        name: {
            required: true,
            minlength: 2,
            maxlength: 255,
            remote: {
                url: "{{ route('masterapp.locations.check-unique') }}",
                type: "post",
                data: {
                    exclude_id: function() { return $("#edit_id").val(); },
                    _token: function() { return $('meta[name="csrf-token"]').attr('content'); }
                }
            }
        },
        address: {
            minlength: 5,
            maxlength: 1000
        },
        city: {
            lettersOnly: true,
            minlength: 2,
            maxlength: 100
        },
        country: {
            minlength: 2,
            maxlength: 100
        },
        postal_code: {
            alphanumeric: true,
            minlength: 3,
            maxlength: 20
        },
        phone: {
            maxlength: 17
        }
    },

    messages: {
        name: {
            required: "Please enter a location name",
            minlength: "Location name must be at least 2 characters",
            maxlength: "Location name cannot exceed 255 characters",
            remote: "This location name is already in use"
        },
        address: {
            minlength: "Address must be at least 5 characters",
            maxlength: "Address cannot exceed 1000 characters"
        },
        city: {
            lettersOnly: "City name cannot contain numbers or symbols",
            minlength: "City name must be at least 2 characters",
            maxlength: "City name cannot exceed 100 characters"
        },
        country: {
            minlength: "Country name must be at least 2 characters",
            maxlength: "Country name cannot exceed 100 characters"
        },
        postal_code: {
            alphanumeric: "Postal code can only contain letters and numbers",
            minlength: "Postal code must be at least 3 characters",
            maxlength: "Postal code cannot exceed 20 characters"
        },
        phone: {
            maxlength: "Phone number cannot exceed +1 (123) 456-7890"
        }
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
    }
    });
});
</script>
<script>
// US Phone Number Formatter
function formatUSPhone(input) {
    if (!input) return;

    const PREFIX = '+1 (';
    const PREFIX_LEN = PREFIX.length;
    const rawInput = document.getElementById('phone_raw');

    if (!rawInput) {
        console.error('phone_raw hidden input not found');
        return;
    }

    function formatValue(raw) {
        let value = raw.startsWith(PREFIX) ? raw.slice(PREFIX_LEN) : raw;
        let digits = value.replace(/\D/g, '').slice(0, 10);

        //  ALWAYS update hidden input
        rawInput.value = digits;

        if (digits.length === 0) return PREFIX;
        if (digits.length <= 3) return PREFIX + digits;
        if (digits.length <= 6)
            return PREFIX + digits.slice(0, 3) + ') ' + digits.slice(3);
        return (
            PREFIX +
            digits.slice(0, 3) +
            ') ' +
            digits.slice(3, 6) +
            '-' +
            digits.slice(6)
        );
    }

    input.addEventListener('input', function () {
        input.value = formatValue(input.value);
        requestAnimationFrame(() => {
            input.setSelectionRange(input.value.length, input.value.length);
        });
    });

    input.addEventListener('keydown', function (e) {
        if (
            (e.key === 'Backspace' || e.key === 'Delete') &&
            input.selectionStart <= PREFIX_LEN
        ) {
            e.preventDefault();
        }
    });

    if (!input.value.startsWith(PREFIX)) {
        input.value = PREFIX;
        rawInput.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const editPhoneInput = document.getElementById('edit_phone');
    if (editPhoneInput) formatUSPhone(editPhoneInput);
});
// document.getElementById('phone').addEventListener('input', function (e) {
//     let value = e.target.value.replace(/\D/g, '');

//     if (value.length > 10) value = value.slice(0, 10);

//     let formatted = '+1 ';
//     if (value.length > 0) formatted += '(' + value.substring(0, 3);
//     if (value.length >= 4) formatted += ') ' + value.substring(3, 6);
//     if (value.length >= 7) formatted += '-' + value.substring(6, 10);

//     e.target.value = formatted;
// });

function exportVisibleColumns(idx, data, node) {
    const table = $('#dataTable').DataTable();

    // Exclude Actions / non-export columns
    if ($(node).hasClass('no-export') || $(node).hasClass('no-vis')) {
        return false;
    }

    // Export only columns enabled via Column Visibility
    return table.column(idx).visible();
}

</script>
@endpush
@endsection

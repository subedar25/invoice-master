@extends('layouts.custom-admin')
@section('title', 'Timesheets', 'bold')

@section('content')

<div class="container-fluid">
    <div class="card">
        <div class="card-body">

            <table id="dataTable" class="table table-bordered table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>User</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Hours</th>
                        <th>Clock In Mode</th>
                        <th>Type</th>
                        <th>Notes</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
</div>

@include('partials.generic-model')
@endsection


{{-- INLINE EDIT CONFIG --}}
@push('scripts')
<script>
window.inlineConfig = {
    updateUrl: "/timesheets/",
    fields: {
        user_id: "select",
        start_time: "datetime",
        end_time: "datetime",
        clock_in_mode: "select",
        type: "select",
        notes: "textarea"
    },
    options: {
        user_id: @json(
            \App\Models\User::select('id', 'first_name')->get()->map(fn($u) => [
            'id' => $u->id,
            'label' => $u->first_name
            ])
        ),
        clock_in_mode: [
            { value: "office", label: "Office" },
            { value: "remote", label: "Remote" },
            { value: "out_of_office", label: "Out of Office" },
            { value: "do_not_disturb", label: "Do Not Disturb" }
        ],
        type: [
            { value: "normal_paid", label: "Normal Paid" },
            { value: "absent_unpaid", label: "Absent (Unpaid)" },
            { value: "compensated_paid", label: "Compensated Paid" },
            { value: "holiday_paid", label: "Holiday Paid" },
            { value: "sick_paid", label: "Sick Paid" },
            { value: "vacation_paid", label: "Vacation Paid" },
            { value: "vacation_unpaid", label: "Vacation Unpaid" }
        ]
    }
};
</script>

@endpush


@push('scripts')
<script>
/**
 * SINGLE INLINE EDIT AT A TIME
 * Works across all modules
 */
$(document).on('click', '.inline-edit[data-single]', function (e) {
    e.stopPropagation();

    $('.inline-edit.editing').not(this).each(function () {
        const td = $(this);
        if (td.data('oldHtml')) {
            td.removeClass('editing').html(td.data('oldHtml'));
        }
    });
});

/**
 * CLICK OUTSIDE INLINE EDIT → CLOSE
 * (but allow DataTable UI + Flatpickr)
 */
$(document).on('click', function (e) {

    // Ignore clicks inside inline-edit cell or its controls
    if (
        $(e.target).closest(
            '.inline-edit, input, select, textarea, button, .flatpickr-calendar'
        ).length
    ) {
        return;
    }

    // Ignore clicks INSIDE the datatable table itself
    if ($(e.target).closest('table').length) {
        return;
    }

    //  Click is truly outside → close inline edits
    $('.inline-edit.editing').each(function () {
        const td = $(this);
        if (td.data('oldHtml')) {
            td.removeClass('editing').html(td.data('oldHtml'));
        }
    });
});


/**
 * FLATPICKR SAFETY
 * Prevents time clicks from closing editor
 */
$(document).on('mousedown', '.flatpickr-calendar', function (e) {
    e.stopPropagation();
});
</script>

<!-- Generic CRUD + Form Handler -->
<script src="{{ asset('js/generic-datatable.js') }}"></script>
<script src="{{ asset('js/ajax-form-handler.js') }}"></script>

<!-- Page-specific JS -->
<script src="{{ asset('js/timesheets-index.js') }}"></script>
@endpush

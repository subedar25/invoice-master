<form id="timesheetForm" method="POST" action="{{ url('/timesheets') }}">
    @csrf

    {{-- <div class="modal-header">
        <h5 class="modal-title">Create Timesheet Entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div> --}}

    <div class="modal-body">

        {{-- USER --}}
        <div class="mb-3">
            <label class="form-label">User <span class="text-danger">*</span></label>
            <select name="user_id" class="form-control" required>
                <option value="">-- Select User --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->first_name }}</option>
                @endforeach
            </select>
        </div>

        {{-- START TIME --}}
        <div class="mb-3">
            <label class="form-label">Start Time <span class="text-danger">*</span></label>
            <input type="datetime-local"
                   name="start_time"
                   class="form-control"
                   required>
        </div>

        {{-- END TIME --}}
        <div class="mb-3">
            <label class="form-label">End Time</label>
            <input type="datetime-local"
                   name="end_time"
                   class="form-control">
            <small class="text-muted">
                Leave empty to keep shift running
            </small>
        </div>

        {{-- CLOCK IN MODE --}}
        <div class="mb-3">
            <label class="form-label">Clock In Mode <span class="text-danger">*</span></label>
            <select name="clock_in_mode" class="form-control" required>
                <option value="office">Office</option>
                <option value="remote">Remote</option>
                <option value="out_of_office">Out of Office</option>
                <option value="do_not_disturb">Do Not Disturb</option>
            </select>
        </div>

        {{-- TYPE --}}
        <div class="mb-3">
            <label class="form-label">Type <span class="text-danger">*</span></label>
            <select name="type" class="form-control" required>
                <option value="normal_paid">Normal Paid</option>
                <option value="absent_unpaid">Absent (Unpaid)</option>
                <option value="compensated_paid">Compensated Paid</option>
                <option value="holiday_paid">Holiday Paid</option>
                <option value="sick_paid">Sick Paid</option>
                <option value="vacation_paid">Vacation Paid</option>
                <option value="vacation_unpaid">Vacation Unpaid</option>
            </select>
        </div>

        {{-- NOTES --}}
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes"
                      class="form-control"
                      rows="3"
                      placeholder="Optional notes..."></textarea>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button"
                class="btn btn-secondary"
                data-dismiss="modal">
            Cancel
        </button>

        <button type="submit"
                class="btn btn-success">
            <span id="btn-edit-text">Save</span>
            <span id="btn-edit-spinner"
                  class="spinner-border spinner-border-sm d-none"
                  role="status"
                  aria-hidden="true"></span>
        </button>
    </div>

</form>
@push('scripts')
<script>
handleAjaxForm("#timesheetForm", {
    loadingIndicator: 'button',
    buttonTextSelector: '#btn-edit-text',
    buttonSpinnerSelector: '#btn-edit-spinner',
    modalToClose: "#genericModal",
    reloadOnSuccess: true,
    successTitle: "Timesheet Created!"
});
</script>
<!-- Generic CRUD + Form Handler -->
<script src="{{ asset('js/generic-datatable.js') }}"></script>
<script src="{{ asset('js/ajax-form-handler.js') }}"></script>
@endpush

<form id="timesheetFormEdit"
      method="POST"
      action="{{ url('/timesheets/' . $timesheet->id) }}">
    @csrf
    @method('PATCH')

    {{-- <div class="modal-header">
        <h5 class="modal-title">Edit Timesheet Entry</h5>
        <button type="button" class="btn-close" data-dismiss="modal"></button>
    </div> --}}

    <div class="modal-body">

        {{-- USER (ADMIN ONLY – LOCKED IF NEEDED) --}}
        <div class="mb-3">
            <label class="form-label">User</label>
            <select name="user_id" class="form-control" required>
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        @selected($user->id === $timesheet->user_id)>
                        {{ $user->first_name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- START TIME --}}
        <div class="mb-3">
            <label class="form-label">Start Time <span class="text-danger">*</span></label>
            <input type="datetime-local"
                   name="start_time"
                   class="form-control"
                   value="{{ $timesheet->start_time->format('Y-m-d\TH:i') }}"
                   required>
        </div>

        {{-- END TIME --}}
        <div class="mb-3">
            <label class="form-label">End Time</label>
            <input type="datetime-local"
                   name="end_time"
                   class="form-control"
                   value="{{ $timesheet->end_time?->format('Y-m-d\TH:i') }}">
            <small class="text-muted">
                Leave empty to keep shift running
            </small>
        </div>

        {{-- CLOCK IN MODE --}}
        <div class="mb-3">
            <label class="form-label">Clock In Mode <span class="text-danger">*</span></label>
            <select name="clock_in_mode" class="form-control" required>
                @foreach([
                    'office' => 'Office',
                    'remote' => 'Remote',
                    'out_of_office' => 'Out of Office',
                    'do_not_disturb' => 'Do Not Disturb'
                ] as $value => $label)
                    <option value="{{ $value }}"
                        @selected($timesheet->clock_in_mode === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- TYPE --}}
        <div class="mb-3">
            <label class="form-label">Type <span class="text-danger">*</span></label>
            <select name="type" class="form-control" required>
                @foreach([
                    'normal_paid' => 'Normal Paid',
                    'absent_unpaid' => 'Absent (Unpaid)',
                    'compensated_paid' => 'Compensated Paid',
                    'holiday_paid' => 'Holiday Paid',
                    'sick_paid' => 'Sick Paid',
                    'vacation_paid' => 'Vacation Paid',
                    'vacation_unpaid' => 'Vacation Unpaid'
                ] as $value => $label)
                    <option value="{{ $value }}"
                        @selected($timesheet->type === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- NOTES --}}
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes"
                      class="form-control"
                      rows="3">{{ $timesheet->notes }}</textarea>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button"
                class="btn btn-secondary"
                data-dismiss="modal">
            Cancel
        </button>

        <button type="submit"
                class="btn btn-primary">
            <span id="btn-edit-text">Update</span>
            <span id="btn-edit-spinner"
                  class="spinner-border spinner-border-sm d-none"></span>
        </button>
    </div>

</form>
@push('scripts')
<script>
handleAjaxForm("#timesheetFormEdit", {
    loadingIndicator: 'button',
    modalToClose: "#genericModal",
    reloadOnSuccess: true,
    successTitle: "Timesheet Updated!"
});
<script src="{{ asset('js/timesheets-edit.js') }}"></script>
</script>
@endpush

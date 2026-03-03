<form id="timesheetFormDuplicate"
      method="POST"
      action="{{ url('/timesheets/' . $timesheet->id . '/duplicate') }}">
    @csrf

    {{-- <div class="modal-header">
        <h5 class="modal-title">Duplicate Timesheet Entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div> --}}

    <div class="modal-body">

    <div class="mb-3">
    <label class="form-label">User <span class="text-danger">*</span></label>

    {{-- @if(auth()->user()->hasRole(['admin', 'superadmin'])) --}}
        <select name="user_id" class="form-control" required>
            @foreach($users as $user)
                <option value="{{ $user->id }}"
                    @selected($user->id === $timesheet->user_id)>
                    {{ $user->first_name }} 
                    {{-- {{ $user->last_name }} --}}
                </option>
            @endforeach
        </select>
    {{-- @else
        <input type="hidden" name="user_id" value="{{ auth()->id() }}">

        <input type="text"
               class="form-control"
               value="{{ auth()->user()->first_name }}"
               disabled>
    @endif --}}
</div>

        {{-- START TIME --}}
        <div class="mb-3">
            <label class="form-label">Start Time <span class="text-danger">*</span></label>
            <input type="datetime-local"
                   name="start_time"
                   class="form-control"
                   value="{{ now()->format('Y-m-d\TH:i') }}"
                   required>
            <small class="text-muted">
                New entry will start now
            </small>
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
            <small class="text-muted">
                Copied from original entry
            </small>
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
            <span id="btn-dup-text">Duplicate</span>
            <span id="btn-dup-spinner"
                  class="spinner-border spinner-border-sm d-none"></span>
        </button>
    </div>

</form>
@push('scripts')
<script>
handleAjaxForm("#timesheetFormDuplicate", {
    loadingIndicator: 'button',
    modalToClose: "#genericModal",
    reloadOnSuccess: true,
    successTitle: "Timesheet Duplicated!"
});
</script>

<script src="{{ asset('js/timesheets-duplicate.js') }}"></script>
@endpush


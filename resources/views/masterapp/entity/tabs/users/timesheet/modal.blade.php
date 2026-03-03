<form id="timesheetEditForm"
      data-id="{{ $timesheet->id }}"
      action="{{ route('masterapp.timesheets.update', $timesheet->id) }}"
      method="POST">

    @csrf
    @method('PATCH')

    <div class="mb-3">
        <label>User</label>
        <select name="user_id" class="form-select">
            @foreach($users as $user)
                <option value="{{ $user->id }}"
                    @selected($user->id === $timesheet->user_id)>
                    {{ $user->first_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Start Time *</label>
        <input type="text" name="start_time"
               class="form-control"
               value="{{ $timesheet->start_time->format('m/d/Y h:i A') }}">
    </div>

    <div class="mb-3">
        <label>End Time</label>
        <input type="text" name="end_time"
               class="form-control"
               value="{{ optional($timesheet->end_time)?->format('m/d/Y h:i A') }}">
    </div>

    <div class="mb-3">
        <label>Clock In Mode *</label>
        <select name="clock_in_mode" class="form-select">
            <option value="office">Office</option>
            <option value="remote">Remote</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Type *</label>
        <select name="type" class="form-select">
            <option value="normal_paid">Normal Paid</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Notes</label>
        <textarea name="notes" class="form-control">{{ $timesheet->notes }}</textarea>
    </div>

    <div class="text-end">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Cancel
        </button>
        <button type="submit" class="btn btn-primary">
            Update
        </button>
    </div>
</form>

@extends('masterapp.layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Timesheet Details</h4>
                    <a href="{{ route('masterapp.timesheets.index') }}" class="btn btn-secondary">Back</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>User</h5>
                            <p>{{ $timesheet->user->first_name }} {{ $timesheet->user->last_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Start Time</h5>
                            <p>{{ $timesheet->start_time->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>End Time</h5>
                            <p>{{ $timesheet->end_time ? $timesheet->end_time->format('M d, Y g:i A') : 'Still running' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Duration</h5>
                            <p>{{ $timesheet->duration_hours }} hours</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Clock In Mode</h5>
                            <p>{{ $timesheet->clock_in_mode_label }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Type</h5>
                            <p>{{ $timesheet->type_label }}</p>
                        </div>
                    </div>
                    @if($timesheet->notes)
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Notes</h5>
                            <p>{{ $timesheet->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

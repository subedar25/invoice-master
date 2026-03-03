@extends('masterapp.layouts.app')

@section('title', 'Dashboard', 'bold')

@section('content')
{{-- <br/> --}}
    {{-- <div class="card shadow-sm mb-4" style="max-width: 650px; margin-left: 30px auto;"> --}}
    <div class="card-body">

{{--   CURRENT SHIFT CARD --}}
<div class="card shadow-sm mb-4" style="max-width: 600px;">
    <div class="card-body">

        {{-- HEADER: title fixed; status only in badge (right) --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-clock me-2 text-primary"></i>
                Current Shift
            </h5>

            @if ($currentShift)
                @php
                    $mode = $currentShift->clock_in_mode ?? 'office';
                @endphp
                @if (in_array($mode, ['office', 'remote']))
                    <span class="badge bg-success">
                        <i class="fas fa-play-circle me-1"></i>
                        Running
                    </span>
                @elseif ($mode === 'lunch')
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-utensils me-1"></i>
                        Lunch
                    </span>
                @elseif ($mode === 'out_of_office')
                    <span class="badge bg-info">
                        <i class="fas fa-user-clock me-1"></i>
                        Out of Office
                    </span>
                @elseif ($mode === 'do_not_disturb')
                    <span class="badge bg-info">
                        <i class="fas fa-ban me-1"></i>
                        Do Not Disturb
                    </span>
                @else
                    <span class="badge bg-success">
                        <i class="fas fa-play-circle me-1"></i>
                        {{ $currentShift->clock_in_mode_label }}
                    </span>
                @endif
            @else
                <span class="badge bg-secondary">
                    <i class="fas fa-stop-circle me-1"></i>
                    Not Clocked In
                </span>
            @endif
        </div>

        {{-- TIMER --}}
        <div class="mb-4">
            @if (!$currentShift)
                <div class="text-muted fst-italic">
                    No active shift
                </div>
            @else
                <div class="display-6 fw-bold text-success">
                    <span id="running-timer"
                          data-start="{{ $currentShift->start_time->timestamp }}"
                          data-server-time="{{ now()->timestamp }}">
                        00:00
                    </span>
                    <small class="fs-6 text-muted ms-2">
                        hours
                    </small>
                </div>

                <div class="text-muted mt-1">
                    Started at {{ $currentShift->start_time->format('h:i A') }}
                </div>
            @endif
        </div>

        {{-- ACTIONS --}}
        @if (!$currentShift)

            <div class="mb-2 text-muted">
                <i class="fas fa-sign-in-alt me-1"></i>
                Choose how you want to clock in
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-success clock-btn" data-mode="office">
                    <i class="fas fa-building me-1"></i>
                    Office
                </button>

                <button class="btn btn-outline-success clock-btn" data-mode="remote">
                    <i class="fas fa-laptop-house me-1"></i>
                    Remote
                </button>

                <button class="btn btn-outline-success clock-btn" data-mode="out_of_office">
                    <i class="fas fa-car me-1"></i>
                    Out of Office
                </button>

                <button class="btn btn-outline-success clock-btn" data-mode="do_not_disturb">
                    <i class="fas fa-ban me-1"></i>
                    Do Not Disturb
                </button>
            </div>

        @else
            @if (($currentShift->clock_in_mode ?? '') === 'lunch')
                <div class="mb-2 text-muted">
                    <i class="fas fa-utensils me-1"></i>
                    Back from lunch
                </div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button class="btn btn-outline-success resume-lunch-btn" data-mode="office">
                        <i class="fas fa-building me-1"></i>
                        Office
                    </button>
                    <button class="btn btn-outline-success resume-lunch-btn" data-mode="remote">
                        <i class="fas fa-laptop-house me-1"></i>
                        Remote
                    </button>
                    <button class="btn btn-outline-success resume-lunch-btn" data-mode="out_of_office">
                        <i class="fas fa-car me-1"></i>
                        Out of Office
                    </button>
                    <button class="btn btn-outline-success resume-lunch-btn" data-mode="do_not_disturb">
                        <i class="fas fa-ban me-1"></i>
                        Do Not Disturb
                    </button>
                </div>
            @endif
            <div class="mb-2 text-muted">
                <i class="fas fa-sign-out-alt me-1"></i>
                End your shift
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button id="clockOutBtn" class="btn btn-danger">
                    <i class="fas fa-stop-circle me-1"></i>
                    Clock Out
                </button>
                @if (($currentShift->clock_in_mode ?? '') !== 'lunch')
                    <button id="clockOutLunchBtn" class="btn btn-warning">
                        <i class="fas fa-utensils me-1"></i>
                        Lunch
                    </button>
                @endif
            </div>

        @endif

    </div>
</div>

</div>
</div>

@endsection
@push('scripts')
{{-- <script src="{{ asset('js/dashboard.js') }}"></script> --}}
<script>
const CSRF = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
const CLOCK_IN_URL  = '{{ route("masterapp.dashboard.clock-in") }}';
const CLOCK_OUT_URL = '{{ route("masterapp.dashboard.clock-out") }}';
const RESUME_LUNCH_URL = '{{ route("masterapp.dashboard.resume-from-lunch") }}';

//   CLOCK IN
$(document).on('click', '.clock-btn', function () {
    const mode = $(this).data('mode');

    $.post(CLOCK_IN_URL, {
        _token: CSRF,
        clock_in_mode: mode
    })
    .done(() => {
       location.reload();

    })
    .fail(err => {
        console.error('Clock-in failed:', err.responseJSON?.message || err);
    });
});

//   CLOCK OUT
$(document).on('click', '#clockOutBtn', function () {
    $.post(CLOCK_OUT_URL, { _token: CSRF })
    .done(() => {
        location.reload();
    })
    .fail(err => {
        console.error('Clock-out failed:', err.responseJSON?.message || err);
    });
});

//   CLOCK OUT – LUNCH (keeps shift open, sets mode to lunch)
$(document).on('click', '#clockOutLunchBtn', function () {
    $.post(CLOCK_OUT_URL, {
        _token: CSRF,
        reason: 'lunch'
    })
    .done(() => {
        location.reload();
    })
    .fail(err => {
        console.error('Lunch failed:', err.responseJSON?.message || err);
    });
});

//   RESUME FROM LUNCH
$(document).on('click', '.resume-lunch-btn', function () {
    const mode = $(this).data('mode');
    $.post(RESUME_LUNCH_URL, {
        _token: CSRF,
        clock_in_mode: mode
    })
    .done(() => {
        location.reload();
    })
    .fail(err => {
        console.error('Resume from lunch failed:', err.responseJSON?.message || err);
    });
});

//  LIVE RUNNING TIMER
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('running-timer');
    if (!el) return;

    const startTs = parseInt(el.dataset.start, 10) * 1000;

    function updateTimer() {
        const diff = Math.max(0, Date.now() - startTs);
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);

        el.textContent =
            String(h).padStart(2, '0') + ':' +
            String(m).padStart(2, '0') + ':' +
            String(s).padStart(2, '0');
    }

    updateTimer();
    setInterval(updateTimer, 1000);
});
</script>
@endpush

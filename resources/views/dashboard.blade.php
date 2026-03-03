@extends('layouts.custom-admin')

@section('title', 'Dashboard', 'bold')

@section('content')
    
<div class="card" style="max-width: 600px; margin-left: 20px auto;">
    <div class="card-body">

{{-- |  CURRENT SHIFT CARD --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-clock me-2 text-primary"></i>
                Current Shift
            </h5>

            @if ($currentShift)
                <span class="badge bg-success">
                    <i class="fas fa-play-circle me-1"></i>
                    Running
                </span>
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
                          data-start="{{ $currentShift->start_time->timestamp }}">
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
                <button class="btn btn-success clock-btn" data-mode="office">
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

            <div class="mb-2 text-muted">
                <i class="fas fa-sign-out-alt me-1"></i>
                End your shift
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button id="clockOutBtn" class="btn btn-danger">
                    <i class="fas fa-stop-circle me-1"></i>
                    Clock Out
                </button>

                <button id="clockOutLunchBtn" class="btn btn-warning">
                    <i class="fas fa-utensils me-1"></i>
                    Lunch Break
                </button>
            </div>

        @endif

</div> {{-- inner card-body --}}
    </div> {{-- inner card --}}

    </div> {{-- OUTER card-body --}}
</div> {{-- OUTER card --}}




@endsection
@push('scripts')
{{-- <script src="{{ asset('js/dashboard.js') }}"></script> --}}
<script>

// const CSRF = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
// const CLOCK_IN_URL  = '{{ route("masterapp.dashboard.clock-in") }}';
// const CLOCK_OUT_URL = '{{ route("masterapp.dashboard.clock-out") }}';

// // CLOCK IN
// $(document).on('click', '.clock-btn', function () {
//     const mode = $(this).data('mode');

//     $.post(CLOCK_IN_URL, {
//         _token: CSRF,
//         clock_in_mode: mode
//     })
//     .done(res => {
//         Swal.fire({
//             icon: 'success',
//             title: 'Clocked In',
//             text: res.message
//         }).then(() => location.reload());
//     })
//     .fail(err => {
//         Swal.fire(
//             'Error',
//             err.responseJSON?.message || 'Failed to clock in',
//             'error'
//         );
//     });
// });

// // CLOCK OUT
// $(document).on('click', '#clockOutBtn', function () {
//     $.post(CLOCK_OUT_URL, { _token: CSRF })
//     .done(res => {
//         Swal.fire({
//             icon: 'success',
//             title: 'Clocked Out',
//             text: res.message
//         }).then(() => location.reload());
//     })
//     .fail(err => {
//         Swal.fire(
//             'Error',
//             err.responseJSON?.message || 'Failed to clock out',
//             'error'
//         );
//     });
// });

// // CLOCK OUT – LUNCH
// $(document).on('click', '#clockOutLunchBtn', function () {
//     $.post(CLOCK_OUT_URL, {
//         _token: CSRF,
//         reason: 'lunch'
//     })
//     .done(res => {
//         Swal.fire({
//             icon: 'success',
//             title: 'Lunch Break',
//             text: res.message
//         }).then(() => location.reload());
//     })
//     .fail(err => {
//         Swal.fire(
//             'Error',
//             err.responseJSON?.message || 'Failed',
//             'error'
//         );
//     });
// });

// // LIVE TIMER
// document.addEventListener('DOMContentLoaded', function () {
//     const el = document.getElementById('running-timer');
//     if (!el) return;

//     const startTs = parseInt(el.dataset.start, 10) * 1000;

//     function updateTimer() {
//         const diff = Math.max(0, Date.now() - startTs);
//         const h = Math.floor(diff / 3600000);
//         const m = Math.floor((diff % 3600000) / 60000);
//         const s = Math.floor((diff % 60000) / 1000);

//         el.textContent =
//             String(h).padStart(2, '0') + ':' +
//             String(m).padStart(2, '0') + ':' +
//             String(s).padStart(2, '0');
//     }

//     updateTimer();
//     setInterval(updateTimer, 1000);
// });






const CSRF = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
const CLOCK_IN_URL  = '{{ route("masterapp.dashboard.clock-in") }}';
const CLOCK_OUT_URL = '{{ route("masterapp.dashboard.clock-out") }}';

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

//   CLOCK OUT – LUNCH
$(document).on('click', '#clockOutLunchBtn', function () {
    $.post(CLOCK_OUT_URL, {
        _token: CSRF,
        reason: 'lunch'
    })
    .done(() => {
        location.reload();
    })
    .fail(err => {
        console.error('Lunch break failed:', err.responseJSON?.message || err);
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
const CSRF = $('meta[name="csrf-token"]').attr('content');
const CLOCK_IN_URL  = '/master-app/dashboard/clock-in';
const CLOCK_OUT_URL = '/master-app/dashboard/clock-out';

// CLOCK IN
$(document).on('click', '.clock-btn', function () {
    const mode = $(this).data('mode');

    $.post(CLOCK_IN_URL, {
        _token: CSRF,
        clock_in_mode: mode
    })
    .done(res => {
        Swal.fire({
            icon: 'success',
            title: 'Clocked In',
            text: res.message
        }).then(() => {
            // Update UI without page refresh
            updateDashboardAfterClockIn(res.shift);
        });
    })
    .fail(err => {
        Swal.fire(
            'Error',
            err.responseJSON?.message || 'Failed to clock in',
            'error'
        );
    });
});

// CLOCK OUT
$(document).on('click', '#clockOutBtn', function () {
    $.post(CLOCK_OUT_URL, { _token: CSRF })
    .done(res => {
        Swal.fire({
            icon: 'success',
            title: 'Clocked Out',
            text: res.message
        }).then(() => {
            // Update UI without page refresh
            updateDashboardAfterClockOut();
        });
    })
    .fail(err => {
        Swal.fire(
            'Error',
            err.responseJSON?.message || 'Failed to clock out',
            'error'
        );
    });
});

// CLOCK OUT – LUNCH
$(document).on('click', '#clockOutLunchBtn', function () {
    $.post(CLOCK_OUT_URL, {
        _token: CSRF,
        reason: 'lunch'
    })
    .done(res => {
        Swal.fire({
            icon: 'success',
            title: 'Lunch Break',
            text: res.message
        }).then(() => {
            // Update UI without page refresh
            updateDashboardAfterClockOut();
        });
    })
    .fail(err => {
        Swal.fire(
            'Error',
            err.responseJSON?.message || 'Failed',
            'error'
        );
    });
});

// Function to update dashboard after clock in
function updateDashboardAfterClockIn(shift) {
    // Update status badge
    $('.badge').removeClass('bg-secondary').addClass('bg-success').html('<i class="fas fa-play-circle me-1"></i> Running');

    // Hide clock-in buttons and show clock-out buttons
    $('.clock-btn').parent().hide();
    $('#clockOutBtn, #clockOutLunchBtn').parent().show();

    // Update the text
    $('.mb-2.text-muted').html('<i class="fas fa-sign-out-alt me-1"></i> End your shift');

    // Start the timer
    const startTime = new Date(shift.start_time).getTime();
    startLiveTimer(startTime);
}

// Function to update dashboard after clock out
function updateDashboardAfterClockOut() {
    // Update status badge
    $('.badge').removeClass('bg-success').addClass('bg-secondary').html('<i class="fas fa-stop-circle me-1"></i> Not Clocked In');

    // Hide clock-out buttons and show clock-in buttons
    $('#clockOutBtn, #clockOutLunchBtn').parent().hide();
    $('.clock-btn').parent().show();

    // Update the text
    $('.mb-2.text-muted').html('<i class="fas fa-sign-in-alt me-1"></i> Choose how you want to clock in');

    // Stop the timer and show "No active shift"
    $('#running-timer').parent().html('<div class="text-muted fst-italic">No active shift</div>');
}

// Function to start live timer
function startLiveTimer(startTs) {
    const el = document.getElementById('running-timer');
    if (!el) return;

    const serverTimeOffset = Date.now() - (parseInt(el.dataset.serverTime, 10) * 1000);

    function updateTimer() {
        const now = Date.now() - serverTimeOffset;
        const diff = Math.max(0, now - startTs);
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
}

// LIVE TIMER
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('running-timer');
    if (!el) return;

    const startTs = parseInt(el.dataset.start, 10) * 1000;
    const serverTimeOffset = Date.now() - (parseInt(el.dataset.serverTime, 10) * 1000);

    function updateTimer() {
        const now = Date.now() - serverTimeOffset;
        const diff = Math.max(0, now - startTs);
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


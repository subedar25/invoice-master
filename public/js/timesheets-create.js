$(document).ready(function () {

    /* ==========================================================
     * AUTO FILL START TIME
     * ========================================================== */
    const startInput = document.querySelector(
        'input[name="start_time"]'
    );

    if (startInput && !startInput.value) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        startInput.value = now.toISOString().slice(0, 16);
    }

    /* ==========================================================
     * END TIME GUARD
     * ========================================================== */
    $('input[name="end_time"]').on('change', function () {
        const start = $('input[name="start_time"]').val();
        const end = $(this).val();

        if (start && end && end < start) {
            alert('End time cannot be before start time');
            $(this).val('');
        }
    });

});

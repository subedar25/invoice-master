$(document).ready(function () {

    /* ==========================================================
     * AUTO SET START TIME (NOW)
     * ========================================================== */
    const startInput = document.querySelector(
        '#timesheetFormDuplicate input[name="start_time"]'
    );

    if (startInput) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        startInput.value = now.toISOString().slice(0, 16);
    }

    /* ==========================================================
     * TIME VALIDATION
     * ========================================================== */
    $('#timesheetFormDuplicate input[name="end_time"]').on('change', function () {
        const start = $('#timesheetFormDuplicate input[name="start_time"]').val();
        const end = $(this).val();

        if (start && end && end < start) {
            alert('End time cannot be before start time');
            $(this).val('');
        }
    });

    /* ==========================================================
     * OPEN SHIFT WARNING
     * ========================================================== */
    $('#timesheetFormDuplicate').on('submit', function () {
        const end = $('#timesheetFormDuplicate input[name="end_time"]').val();

        if (!end) {
            return confirm(
                'This will create a new running shift. Continue?'
            );
        }
    });

});

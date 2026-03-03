$(document).ready(function () {

    /* ==========================================================
     * TIME VALIDATION
     * ========================================================== */
    $('input[name="end_time"]').on('change', function () {
        const start = $('input[name="start_time"]').val();
        const end = $(this).val();

        if (start && end && end < start) {
            alert('End time cannot be before start time');
            $(this).val('');
        }
    });

    /* ==========================================================
     * CONFIRM OPEN SHIFT
     * ========================================================== */
    $('form#timesheetFormEdit').on('submit', function () {
        const end = $('input[name="end_time"]').val();

        if (!end) {
            return confirm(
                'This will keep the shift open. Continue?'
            );
        }
    });

});

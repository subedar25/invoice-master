// Highlight row when its checkbox is checked
$(document).on('change', '.row-check', function () {
    const row = $(this).closest('tr');

    if (this.checked) {
        row.addClass('row-selected');
    } else {
        row.removeClass('row-selected');
    }
});

// Select All → check/uncheck all rows + highlight
$(document).on('change', '#selectAll', function () {
    const checked = this.checked;

    $('.row-check').prop('checked', checked).trigger('change');
});

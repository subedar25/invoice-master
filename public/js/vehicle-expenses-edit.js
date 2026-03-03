$(document).ready(function () {

    /**
     * EDIT VEHICLE EXPENSE (AJAX)
     * Uses event delegation because form is loaded via modal (AJAX)
     */
    $(document).on('submit', '#vehicleExpenseEditForm', function (e) {
        e.preventDefault();

        const form = this;
        const $form = $(form);
        const url = $form.attr('action');
        const formData = new FormData(form);

        // Disable submit button
        const $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating');

        // Clear previous validation errors
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
        formData.append('_method', 'PATCH');
        $.ajax({
            url: url,
            method: 'POST', 
            headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
            data: formData,
            processData: false,
            contentType: false,

            success: function () {

                // Close modal (Bootstrap 5)
                // const modalEl = document.getElementById('vehicleExpenseModal');
                // const modal = bootstrap.Modal.getInstance(modalEl);
                // modal.hide();
                $('#vehicleExpenseModal').modal('hide');


                // Refresh DataTable only
                if ($.fn.DataTable.isDataTable('#expensesTable')) {
                    $('#expensesTable').DataTable().draw(false);
                }

                // Toast success
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Expense updated successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
                location.reload();
            },

            error: function (xhr) {

                // Validation errors
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;

                    for (const field in errors) {
                        const input = $form.find(`[name="${field}"]`);
                        if (!input.length) continue;

                        input.addClass('is-invalid');
                        input.after(
                            `<div class="invalid-feedback">${errors[field][0]}</div>`
                        );
                    }
                } else {
                    Swal.fire(
                        'Error',
                        xhr.responseJSON?.message || 'Failed to update expense',
                        'error'
                    );
                }
            },

            complete: function () {
                $btn.prop('disabled', false)
                    .html('<i class="fa fa-save"></i> Update Expense');
            }
        });
    });

});

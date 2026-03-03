$(document).ready(function () {

    /**
     * CREATE VEHICLE EXPENSE (AJAX)
     */
    $(document).on('submit', '#vehicleExpenseCreateForm', function (e) {
        e.preventDefault();

        const form = this;
        const $form = $(form);
        const url = $form.attr('action');

        const formData = new FormData(form);

        // Disable submit button
        const $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving');

        // Clear previous errors
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,

           success: function (res) {

    Swal.fire({
        icon: 'success',
        title: 'Created',
        text: res.message,
        timer: 1200,
        showConfirmButton: false
    });

    // Close modal safely
    // const modalEl = document.getElementById('vehicleExpenseModal');
    // const modal = bootstrap.Modal.getInstance(modalEl);
    // if (modal) modal.hide();
$('#vehicleExpenseModal').modal('hide');

    //  FORCE reload the expenses page content
    setTimeout(() => {
        location.reload();
    }, 1300);
},



            error: function (xhr) {

                // Validation errors
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;

                    for (const field in errors) {
                        const input = $form.find(`[name="${field}"]`);

                        if (input.length) {
                            input.addClass('is-invalid');
                            input.after(
                                `<div class="invalid-feedback">${errors[field][0]}</div>`
                            );
                        }
                    }
                } else {
                    Swal.fire('Error', 'Failed to save expense', 'error');
                }
            },

            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Expense');
            }
        });
    });

});

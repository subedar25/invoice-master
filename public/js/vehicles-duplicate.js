$(document).on('submit', '#vehicleDuplicateForm', function (e) {
    e.preventDefault();

    const $form = $(this);

    $.post($form.attr('action'), $form.serialize())
        .done(res => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: res.message || 'Vehicle duplicated',
                timer: 1500,
                showConfirmButton: false
            });

            $('#vehicleModal').modal('hide');
            location.reload();
        })
        .fail(xhr => {
            Swal.fire('Error', xhr.responseJSON?.message || 'Failed', 'error');
        });
});

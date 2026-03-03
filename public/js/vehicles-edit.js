$(document).on('submit', '#vehicleEditForm', function (e) {
    e.preventDefault();

    const $form = $(this);

    $.ajax({
        url: $form.attr('action'),
        method: 'POST', 
        data: $form.serialize(),
        dataType: 'json',

        success(res) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: res.message || 'Vehicle updated successfully',
                timer: 1500,
                showConfirmButton: false
            });

            $('#vehicleModal').modal('hide');

            //  Delay refresh
            setTimeout(() => {
                window.location.reload();
            }, 1600);

        },

        error(xhr) {
            Swal.fire('Error',
                xhr.responseJSON?.message || 'Failed to update vehicle',
                'error'
            );
        }
    });
});

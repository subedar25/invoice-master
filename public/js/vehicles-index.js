$(document).ready(function () {

    //  * DATATABLE INIT

    const table = $("#vehiclesTable").DataTable({
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100, 250, 500, -1],
            [10, 25, 50, 100, 250, 500, "All"]
        ],
        dom: 'lBfrtip',
        columnDefs: [
            { orderable: false, targets: 0 }
        ],
        buttons: [
            {
                text: '<i class="fa fa-plus"></i> New',
                className: 'btn btn-success',
                action: () => openVehicleModal('/vehicles/create', 'New Vehicle')
            },
            {
                text: '<i class="fa fa-edit"></i> Edit',
                className: 'btn btn-primary buttons-edit',
                enabled: false,
                action: () => {
                    const row = getSelectedRows().first();
                    if (row.length) {
                        openVehicleModal(`/vehicles/${row.data('id')}/edit`, 'Edit Vehicle');
                    }
                }
            },
            {
                text: '<i class="fa fa-copy"></i> Duplicate',
                className: 'btn btn-secondary buttons-dup',
                enabled: false,
                action: () => {
                    const row = getSelectedRows().first();
                    if (row.length) {
                        openVehicleModal(`/vehicles/${row.data('id')}/duplicate`, 'Duplicate Vehicle');
                    }
                }
            },
            {
                text: '<i class="fa fa-trash"></i> Delete',
                className: 'btn btn-danger buttons-del',
                enabled: false,
                action: bulkDelete
            },
            { extend: 'colvis', text: '<i class="fa fa-columns"></i> Columns' },
            { extend: 'copy',   text: '<i class="fa fa-copy"></i> Copy' },
            { extend: 'excel',  text: '<i class="fa fa-file-excel"></i> Excel' },
            { extend: 'pdf',    text: '<i class="fa fa-file-pdf"></i> PDF' }
        ],
        drawCallback() {
            // Rebind inline edit after redraw
            if (window.initInlineEdit) {
                window.initInlineEdit();
            }
        }
    });

    //  * HELPERS

    const csrf = () => $('meta[name="csrf-token"]').attr('content');

    function getSelectedRows() {
        return $('#vehiclesTable tbody tr').filter(function () {
            return $(this).find('.row-check').is(':checked');
        });
    }

    function updateButtonStates() {
        const count = getSelectedRows().length;
        table.button('.buttons-edit').enable(count === 1);
        table.button('.buttons-dup').enable(count === 1);
        table.button('.buttons-del').enable(count > 0);
    }

    //  * ROW SELECTION

    $(document).on('change', '.row-check', function () {
        $(this).closest('tr').toggleClass('table-primary', this.checked);
        updateButtonStates();
    });

    $(document).on('change', '#selectAll', function () {
        $('.row-check').prop('checked', this.checked).trigger('change');
    });

    // Clicking row toggles checkbox (except inline-edit)
    $(document).on('click', '#vehiclesTable tbody tr', function (e) {
        if ($(e.target).closest('.inline-edit, a, button, input, select, textarea').length) return;
        const cb = $(this).find('.row-check');
        cb.prop('checked', !cb.prop('checked')).trigger('change');
    });

    updateButtonStates();

    //  * MODAL LOADER

    function openVehicleModal(url, title) {
        const $modal = $('#vehicleModal');

        $('#vehicleModalTitle').text(title);
        $('#vehicleModalBody').html(`
            <div class="text-center p-5">
                <div class="spinner-border text-primary"></div>
            </div>
        `);

        $modal.modal('show');

        $.get(url)
            .done(html => {
                $('#vehicleModalBody').html(html);
            })
            .fail(() => {
                $('#vehicleModalBody')
                    .html('<div class="text-danger p-3">Failed to load.</div>');
            });
    }

    //  * BULK DELETE

    function bulkDelete() {
        const rows = getSelectedRows();

        if (!rows.length) {
            Swal.fire('No selection', 'Select at least one vehicle.', 'info');
            return;
        }

        const ids = rows.map((_, r) => $(r).data('id')).get();

        Swal.fire({
            title: `Delete ${ids.length} vehicle(s)?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then(res => {
            if (!res.isConfirmed) return;

            $.ajax({
                url: '/vehicles/bulk-delete',
                method: 'DELETE',
                data: { ids, _token: csrf() },
                success(res) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    rows.remove();
                    updateButtonStates();
                },
                error(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Delete failed', 'error');
                }
            });
        });
    }

});

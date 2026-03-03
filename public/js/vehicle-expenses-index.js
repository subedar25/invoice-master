//  * GLOBAL DATATABLE INSTANCE
let table;

//  * RECEIPT CELL CLICK (DELEGATED – DATATABLE SAFE)
$(document).on('click', 'td.receipt-cell', function () {

    const td = $(this);

    if (td.data('has-file') == 1) return;

    const expenseId = td.data('expense');
    if (!expenseId) return;

    openReceiptUpload(expenseId);
});


//  * OPEN RECEIPT UPLOAD MODAL
function openReceiptUpload(expenseId) {

    const modal = $('#vehicleExpenseModal');

    modal.find('.modal-title').text('Upload Receipt');
    modal.find('.modal-body')
        .html('<div class="text-center p-4">Loading…</div>');

    modal.modal('show');

    fetch(`/vehicle-expenses/${expenseId}/receipt`)
        .then(r => r.text())
        .then(html => modal.find('.modal-body').html(html))
        .catch(() => {
            modal.find('.modal-body')
                .html('<div class="text-danger">Failed to load</div>');
        });
}



//  * DOCUMENT READY
$(document).ready(function () {

    //  * DATATABLE INIT (SINGLE INITIALISATION)
    if ($.fn.DataTable.isDataTable('#expensesTable')) {
        table = $('#expensesTable').DataTable();
    } else {
        table = $('#expensesTable').DataTable({
            dom: 'lBfrtip',
            pageLength: 10,

            /* MULTI ROW SELECTION */
            // select: {
            //     style: 'multi',
            //     selector: 'td:first-child'
            // },

            columnDefs: [
                { orderable: false, targets: 0 }
            ],

            buttons: [

                /* NEW */
                {
                    text: '<i class="fa fa-plus"></i> New',
                    className: 'btn btn-success',
                    action: function () {
                        openVehicleExpenseModal(
                            `/vehicles/${window.VEHICLE_ID}/expenses/create`,
                            'Add Expense'
                        );
                       
                    }
                },
                    
                /* EDIT (SINGLE ROW ONLY) */
               {
    text: '<i class="fa fa-edit"></i> Edit',
    className: 'btn btn-primary btn-edit',
    enabled: false,
    action: function () {

        const rows = table.rows({ selected: true }).nodes();

        if (rows.length !== 1) return;

        const id = rows[0].dataset.id;

        if (!id) {
            Swal.fire('Error', 'Invalid expense selected', 'error');
            return;
        }

        openVehicleExpenseModal(
            `/vehicles/${window.VEHICLE_ID}/expenses/${id}/edit`,
            'Edit Expense'
        );
    }
},


                /* DELETE (MULTI ROW) */
            {
    text: '<i class="fa fa-trash"></i> Delete',
    className: 'btn btn-danger btn-delete',
    enabled: false,
    action: function () {

        const ids = table
            .rows({ selected: true })
            .nodes()
            .toArray()
            .map(tr => tr.dataset.id)
            .filter(Boolean);

        if (!ids.length) {
            Swal.fire('Error', 'No expenses selected', 'error');
            return;
        }

        Swal.fire({
            title: `Delete ${ids.length} expense(s)?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete'
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '/vehicle-expenses/bulk-delete',
                type: 'DELETE',
                data: {
                    ids: ids,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    table.rows({ selected: true }).remove().draw(false);
                    Swal.fire('Deleted', res.message, 'success');
                },
                error: function (xhr) {
                    Swal.fire(
                        'Error',
                        xhr.responseJSON?.message || 'Delete failed',
                        'error'
                    );
                }
            });
        });
    }
},
                { extend: 'colvis', text: '<i class="fa fa-columns"></i> Columns' },
                { extend: 'copy',   text: '<i class="fa fa-copy"></i> Copy' },
                { extend: 'excel',  text: '<i class="fa fa-file-excel"></i> Excel' },
                { extend: 'pdf',    text: '<i class="fa fa-file-pdf"></i> PDF' }
            ]
        });
    }

    //  * BUTTON ENABLE / DISABLE LOGIC
    const deleteBtn = table.button('.btn-delete');
    const editBtn   = table.button('.btn-edit');

 table.on('select deselect', function () {
    const count = table.rows({ selected: true }).count();

    table.button('.btn-delete').enable(count > 0);
    table.button('.btn-edit').enable(count === 1);
});
});

//  * BULK DELETE (DATATABLE NATIVE)
function bulkDelete(ids) {

    $.ajax({
        url: '/vehicle-expenses/bulk-delete',
        method: 'DELETE',
        data: {
            ids: ids,
            _token: csrf()
        },
        success: function (res) {

            table.rows({ selected: true }).remove().draw(false);

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: res.message || 'Expenses deleted',
                timer: 1500,
                showConfirmButton: false
            });
        },
        error: function (xhr) {
            Swal.fire(
                'Error',
                xhr.responseJSON?.message || 'Failed to delete expenses',
                'error'
            );
        }
    });
}

//  * CSRF HELPER
function csrf() {
    return $('meta[name="csrf-token"]').attr('content');
}

//  * GENERIC MODAL LOADER (BOOTSTRAP 5)
function openVehicleExpenseModal(url, title) {

    $.get(url, function (html) {

        const modalEl = document.getElementById('vehicleExpenseModal');

        modalEl.querySelector('.modal-title').innerText = title;
        modalEl.querySelector('.modal-body').innerHTML = html;

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
}

//  * DEBUG
if (window.inlineConfig) {
    console.log('Vehicle Expenses Inline Config:', window.inlineConfig);
}
$(document).on('change', '.row-check', function () {

    const tr = $(this).closest('tr');
    const row = table.row(tr);

    if (this.checked) {
        row.select();
    } else {
        row.deselect();
    }
});

$('#selectAll').on('change', function () {

    const checked = this.checked;

    $('.row-check').each(function () {
        $(this).prop('checked', checked).trigger('change');
    });
});
// $(document).on('click', '.receipt-preview', function () {
//     $('#previewImage').attr('src', this.dataset.src || this.src);
//     $('#imagePreviewModal').modal('show');
// });
$(document).on('click', '.receipt-preview', function () {

    const src = this.dataset.src;

    $('#receiptPreviewImage').attr('src', src);
    $('#receiptDownloadBtn').attr('href', src);

    $('#receiptPreviewModal')
        .show()
        .addClass('show');
});

$(document).on('click', '.receipt-close, #receiptPreviewModal', function (e) {

    // prevent closing when clicking inside modal content
    if ($(e.target).closest('.modal-content').length) return;

    $('#receiptPreviewModal')
        .removeClass('show')
        .hide();

    $('#receiptPreviewImage').attr('src', '');
});
// Close via button
$(document).on('click', '.receipt-close', function () {
    closeReceiptModal();
});

// Close via backdrop click
$(document).on('click', '#receiptPreviewModal', function (e) {
    if ($(e.target).closest('.modal-content').length) return;
    closeReceiptModal();
});

function closeReceiptModal() {
    $('#receiptPreviewModal').removeClass('show').hide();
    $('#receiptPreviewImage').attr('src', '');
}



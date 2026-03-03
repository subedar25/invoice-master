$(document).ready(function () {
    const table = $('#clientsTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"All"]],
        dom: 'lBfrtip',
        scrollX: true,
        columnDefs: [{ orderable: false, targets: 0 }],
        buttons: [
            {
                text: '<i class="fa fa-plus"></i> New',
                className: 'btn btn-success',
                action: () => openClientModal(`/clients/create`, 'New Client')
            },
            {
                text: '<i class="fa fa-edit"></i> Edit',
                className: 'btn btn-primary buttons-edit',
                enabled: false,
                action: () => {
                    const id = getSelected().first().data('id');
                    if (id) openClientModal(`/clients/${id}/edit`, 'Edit Client');
                }
            },
            {
                text: '<i class="fa fa-copy"></i> Duplicate',
                className: 'btn btn-secondary buttons-dup',
                enabled: false,
                action: () => {
                    const id = getSelected().first().data('id');
                    if (id) openClientModal(`/clients/${id}/duplicate`, 'Duplicate Client');
                }
            },
            {
                text: '<i class="fa fa-trash"></i> Delete',
                className: 'btn btn-danger buttons-del',
                enabled: false,
                action: () => bulkDelete()
            },
            { extend: 'colvis', text: '<i class="fa fa-columns"></i> Columns' },
            { extend: 'copy', text: '<i class="fa fa-copy"></i> Copy' },
            { extend: 'excel', text: '<i class="fa fa-file-excel"></i> Excel' },
            { extend: 'pdf', text: '<i class="fa fa-file-pdf"></i> PDF' }
        ]
    });

    function getSelected() {
        return $('#clientsTable tbody .row-check:checked').closest('tr');
    }

    function updateButtons() {
        const count = getSelected().length;
        table.button('.buttons-edit').enable(count === 1);
        table.button('.buttons-dup').enable(count === 1);
        table.button('.buttons-del').enable(count > 0);
    }

    $('#clientsTable').on('change', '.row-check', function () {
        const row = $(this).closest('tr');
        row.toggleClass('selected-row', $(this).is(':checked'));
        updateButtons();
    });

    $('#selectAll').on('change', function () {
        const isChecked = $(this).prop('checked');
        $('.row-check').prop('checked', isChecked).trigger('change');
    });

    function openClientModal(url, title) {
        $('#clientModalTitle').text(title);
        $('#clientModalBody').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 fw-semibold">Loading...</p>
            </div>
        `);
        let modal = new bootstrap.Modal(document.getElementById('clientModal'));
        modal.show();

        $.get(url, function (html) {
            setTimeout(()=> $('#clientModalBody').html(html), 200);
        });
    }
    function singleDelete(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "This client will be hidden but not permanently deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (!result.isConfirmed) return;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(`/clients/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                credentials: 'same-origin'
            }).then(async res=>{
                if (!res.ok) throw new Error(await res.text());
                return res.json();
            }).then(data=>{
                $(`#client-row-${id}`).fadeOut();
                Swal.fire({icon:'success',title:'Deleted',text:data.message ?? 'Client deleted',timer:1500,showConfirmButton:false});
            }).catch(err=>{
                console.error(err);
                Swal.fire('Error','Could not delete client','error');
            });
        });
    }
    function bulkDelete() {
        const ids = getSelected().map((_,r)=>$(r).data('id')).get();
        if (!ids.length) return;
        Swal.fire({
            title: "Are you sure?",
            text: "Selected clients will be hidden but not permanently deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
        }).then(res=>{
            if (!res.isConfirmed) return;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/clients/bulk-delete', {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': token },
                credentials: 'same-origin',
                body: JSON.stringify({ ids })
            }).then(async r=>{
                if (!r.ok) throw new Error(await r.text());
                return r.json();
            }).then(data=>{
                ids.forEach(id => $(`#client-row-${id}`).fadeOut());
                Swal.fire({ icon:'success', title:'Deleted', text:data.message ?? 'Clients deleted', timer:1500, showConfirmButton:false });
            }).catch(err=>{
                console.error('Bulk delete failed', err);
                Swal.fire('Error','Could not delete clients','error');
            });
        });
    }

    // Delegate single delete button if you add one inside row actions (not present by default)
    $(document).on('click', '.single-delete-btn', function(){
        const id = $(this).closest('tr').data('id');
        singleDelete(id);
    });

    

});

$(document).ready(function () {
    const table = $('#usersTable').DataTable({
        layout: {
        topStart: ['pageLength', 'buttons'], 
        topEnd: null
    },
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, 250, 500, -1], [10, 25, 50, 100, 250, 500, "All"]],
        pageLength:10,
        dom: 'lBfrtip',
        columnDefs: [
            { orderable: false, targets: 0 }
        ],
        buttons: [
            {
                text: '<i class="fa fa-plus"></i> New',
                className: 'btn btn-success',
                action: () => openUserModal(`/users/create`, 'New User')
            },
            {
                text: '<i class="fa fa-edit"></i> Edit',
                className: 'btn btn-primary buttons-edit',
                enabled: false,
                action: () => {
                    const id = getSelected().first().data('id');
                    if (id) openUserModal(`/users/${id}/edit`, 'Edit User');
                }
            },
            {
                text: '<i class="fa fa-copy"></i> Duplicate',
                className: 'btn btn-secondary buttons-dup',
                enabled: false,
                action: () => {
                    const id = getSelected().first().data('id');
                    if (id) openUserModal(`/users/${id}/duplicate`, 'Duplicate User');
                }
            },
            {
                text: '<i class="fa fa-trash"></i> Delete',
                className: 'btn btn-danger buttons-del',
                enabled: false,
                action: () => bulkDelete()
            },
            {
                extend: 'colvis',
                text: '<i class="fa fa-columns"></i> Columns'
            },
            {
                extend: 'copy',
                text: '<i class="fa fa-copy"></i> Copy'
            },
            {
                extend: 'excel',
                text: '<i class="fa fa-file-excel"></i> Excel'
            },
            {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf"></i> PDF'
            }
        ]
    });

    function getSelected() {
        return $('#usersTable tbody .row-check:checked').closest('tr');
    }

    function updateButtons() {
        const count = getSelected().length;
        table.button('.buttons-edit').enable(count === 1);
        table.button('.buttons-dup').enable(count === 1);
        table.button('.buttons-del').enable(count > 0);
    }

    $('#usersTable').on('change', '.row-check', function () {
        const row = $(this).closest('tr');
        row.toggleClass('selected-row', $(this).is(':checked'));
        updateButtons();
    });

    $('#selectAll').on('change', function () {
        const isChecked = $(this).prop('checked');
        $('.row-check').prop('checked', isChecked).trigger('change');
    });

    function openUserModal(url, title) {
        $('#userModalTitle').text(title);
        $('#userModalBody').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 fw-semibold">Loading...</p>
            </div>
        `);

        let modal = new bootstrap.Modal(document.getElementById('userModal'));
        modal.show();

        $.get(url, function (html) {
            setTimeout(() => {
                $('#userModalBody').html(html);
            }, 300);
        });
    }

    function deleteuser(id) {

        Swal.fire({
            title: "Are you sure?",
            text: "This user will be hidden but not permanently deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {

                // SAFE CSRF
                const meta = document.querySelector('meta[name="csrf-token"]');
                const token = meta ? meta.getAttribute("content") : "";
                document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                $.ajax({
                    url: "/users/" + id,
                    type: "DELETE",
                    data: { _token: token },
                    success: function (response) {

                        const row = $("#user-row-" + id);
                        if (row.length) row.fadeOut();

                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function () {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    }
                });
            }
        });
    }

    function bulkDelete() {
        const ids = getSelected().map((_, r) => $(r).data("id")).get();
        if (!ids.length) return;

        Swal.fire({
            title: "Are you sure?",
            text: "Selected users will be hidden but not permanently deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            confirmButtonColor: "#d33",
        }).then((res) => {
            if (!res.isConfirmed) return;

            // SAFE CSRF
            const meta = document.querySelector('meta[name="csrf-token"]');
            const token = meta ? meta.getAttribute("content") : "";
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')


            fetch("/users/bulk-delete", {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token
                },
                // body: JSON.stringify({ ids }),
                body: JSON.stringify({ ids }),
    credentials: "same-origin"  
            })
                .then(async (response) => {
                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(`Status ${response.status}: ${text}`);
                    }
                    return response.json();
                })
                .then((data) => {

                    ids.forEach((id) => {
                        const row = $(`#user-row-${id}`);
                        if (row.length) row.fadeOut();
                    });

                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: data.message ?? "Users deleted.",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                })
                .catch((err) => {
                    console.error("Bulk delete failed:", err);
                    Swal.fire("Error!", "Could not delete users.", "error");
                });
        });
    }

});





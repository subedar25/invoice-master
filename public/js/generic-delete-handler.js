
// --- Helper 1: Notification ---
// const Notificationdel = (function () {
//     return {
//         success: function (message, title = "Success") {
//             Swal.fire({ icon: "success", title, text: message, timer: 1500, showConfirmButton: false });
//         },
//         error: function (message, title = "Error") {
//             Swal.fire({ icon: "error", title, text: message });
//         },
//         confirm: function (message, options = {}) {
//             const defaults = { title: "Are you sure?", icon: "warning", showCancelButton: true, confirmButtonColor: "#d33" };
//             return Swal.fire({ ...defaults, ...options, text: message });
//         },
//     };
// })();


// --- Helper 1: Notification ---
const Notificationdel = (function () {

    // shared toast config
    const toastConfig = {
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    };

    return {
        success: function (message, title = "Success") {
            Swal.fire({
                toast: true,
                ...toastConfig,
                icon: "success",
                title: title,
                text: message
            });
        },

        error: function (message, title = "Error") {
            Swal.fire({
                ...toastConfig,
                icon: "error",
                title: title,
                text: message
            });
        },

        confirm: function (message, options = {}) {
            const defaults = {
                title: "Are you sure?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33"
            };

            return Swal.fire({
                ...defaults,
                ...options,
                text: message
            });
        },
    };
})();


// --- Helper 2: Ajax ---
const Ajaxdel = (function () {
    const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    const request = (method, url, data = null) => {
        const config = {
            method,
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            }
        };
        if (data instanceof FormData) { config.body = data; } else { config.headers["Content-Type"] = "application/json"; config.body = JSON.stringify(data); }
        return fetch(url, config).then(response => {
            if (!response.ok) return response.json().catch(() => { throw new Error(`Server error: ${response.status}`); }).then(body => { const error = new Error(body.message || "Server error"); error.status = response.status; throw error; });
            return response.json();
        });
    };
    return { delete: (url, data) => request("DELETE", url, data) };
})();


// --- Core Delete Logic ---
// options: { reloadTable: '#dataTable' } to reload DataTable on success (e.g. serverSide)
// options: { confirmMessage: '...' } or use data-confirm on the button for custom confirm text
function handleDelete(options) {
    options = options || {};
    $(document).on('click', '.delete-item', function(e) {
        e.preventDefault();
        const button = $(this);
        const url = button.data('url');
        const itemName = button.data('name') || 'this item';
        const row = button.closest('tr');
        const confirmMsg = button.data('confirm') || options.confirmMessage || `Are you sure you want to delete "${itemName}"? This action cannot be undone.`;

        Notificationdel.confirm(confirmMsg, {
            title: "Confirm Delete", confirmButtonText: "Yes, delete it!", confirmButtonColor: "#d33",
        }).then((result) => {
            if (result.isConfirmed) {
                const originalRowContent = row.html();
                const colCount = row.find('td').length;
                row.html(`<td colspan="${colCount}" class="text-center"><div class="spinner-border spinner-border-sm text-danger" role="status"></div><span class="ms-2">Deleting...</span></td>`);
                Ajaxdel.delete(url)
                    .then((res) => {
                        if (options.reloadTable && $.fn.DataTable && $.fn.DataTable.isDataTable(options.reloadTable)) {
                            $(options.reloadTable).DataTable().ajax.reload(null, false);
                        } else {
                            row.fadeOut(400, function() { $(this).remove(); });
                        }
                        Notificationdel.success(res.message || 'Item deleted successfully.');
                    })
                    .catch((error) => {
                        row.html(originalRowContent);
                        console.error("Deletion failed:", error);
                        if (error.status === 403) {
                            Notificationdel.error("You do not have permission to delete this item.");
                        } else {
                            Notificationdel.error(error.message || "An error occurred while deleting.");
                        }
                    });
            }
        });
    });
}

$(document).ready(function () {

    // Load users into any dropdown
    function loadUsers(selectId, selectedId) {
        const sel = $(`#${selectId}`);
        if (!sel.length) return;

        sel.prop("disabled", true).empty().append(`<option value="">Loading...</option>`);

        $.get('/users/ajax-list', function (users) {

            sel.empty().append(`<option value="">Select</option>`);

            users.forEach(u => {
                const s = (parseInt(selectedId) === parseInt(u.id)) ? "selected" : "";
                sel.append(`<option value="${u.id}" ${s}>${u.name}</option>`);
            });

            sel.prop("disabled", false);

        }).fail(() => {
            sel.empty().append(`<option value="">Error loading users</option>`);
            Swal.fire({
                icon: "error",
                title: "Failed to load users",
                text: `Could not load user list for "${selectId}"`,
            });
            console.warn("Could not load users for", selectId);
        });
    }

    // Load all assigned fields from CLIENT_ENTRY
    if (window.CLIENT_ENTRY) {

        loadUsers("primary_contact_id", window.CLIENT_ENTRY.primary_contact_id);
        loadUsers("primary_ad_rep_id", window.CLIENT_ENTRY.primary_ad_rep_id);
        loadUsers("secondary_ad_rep_id", window.CLIENT_ENTRY.secondary_ad_rep_id);
    }


    // SWEETALERT FORM SUBMISSION FEEDBACK
    $("#client-edit-form").on("submit", function (e) {
        // Uncomment if you want AJAX submit:
        // e.preventDefault();

        Swal.fire({
            icon: "info",
            title: "Saving...",
            text: "Please wait while the client details are being updated.",
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });
    });

});

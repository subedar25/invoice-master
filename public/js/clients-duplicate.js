document.addEventListener("DOMContentLoaded", function () {
    
    const passwordField = document.getElementById("passwordField");
    const form = document.getElementById("clientFormEdit");
    const changePasswordSelect = document.getElementById("change_password");
    
    function togglePasswordField() {
        passwordField.style.display = (changePasswordSelect.value == "1") ? "block" : "none";
    }

    if (changePasswordSelect) {
        togglePasswordField();
        changePasswordSelect.addEventListener("change", togglePasswordField);
    }

    $('#clientFormDuplicate').submit(function(e){
        e.preventDefault();
        let form = this;
        let formData = new FormData(form);

        $.ajax({
            url: $(form).attr('action'),
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(res){
                Swal.fire("Done!", "Client duplicated!", "success");
                $('#clientModal').modal('hide');
                $('#clientsTable').DataTable().ajax.reload(null, false);
            },
            error: function(xhr){
                Swal.fire("Failed!", "Could not duplicate!", "error");
            }
        });
    });
});

// $(document).ready(function () {

//     // Load Users into dropdowns
//     function loadUsers(selectId, selectedId) {
//         const sel = $(`#${selectId}`);
//         if (!sel.length) return;

//         sel.prop("disabled", true).empty().append(`<option value="">Loading...</option>`);

//         $.get("/users/ajax-list", function (users) {

//             sel.empty().append(`<option value="">Select</option>`);

//             users.forEach(u => {
//                 const isSelected = parseInt(selectedId) === parseInt(u.id) ? "selected" : "";
//                 sel.append(`<option value="${u.id}" ${isSelected}>${u.name}</option>`);
//             });

//             sel.prop("disabled", false);

//         }).fail(() => {
//             sel.empty().append(`<option value="">Error loading users</option>`);
//             Swal.fire({
//                 icon: "error",
//                 title: "User List Load Failed",
//                 text: `Could not load users for ${selectId}.`,
//             });
//         });
//     }

//     // Load all dropdowns from CLIENT_ENTRY
//     if (window.CLIENT_ENTRY) {
//         loadUsers("primary_contact_id", window.CLIENT_ENTRY.primary_contact_id);
//         loadUsers("primary_ad_rep_id", window.CLIENT_ENTRY.primary_ad_rep_id);
//         loadUsers("secondary_ad_rep_id", window.CLIENT_ENTRY.secondary_ad_rep_id);
//     }

//     // AJAX SUBMIT
//     $("#clientFormDuplicate").on("submit", function (e) {
//         e.preventDefault();

//         let form = $(this);
//         let formData = form.serialize();

//         Swal.fire({
//             icon: "info",
//             title: "Duplicating...",
//             text: "Please wait..",
//             showConfirmButton: false,
//             allowOutsideClick: false,
//             didOpen: () => Swal.showLoading()
//         });

//         $.post(form.attr("action"), formData)
//             .done(function (res) {
//                 Swal.fire({
//                     icon: "success",
//                     title: "Client Duplicated",
//                     text: "The duplicate client was created successfully!",
//                 }).then(() => {
//                     window.location.href = "/clients/";
//                 });
//             })
//             .fail(function (xhr) {
//                 let msg = "Something went wrong.";

//                 if (xhr.responseJSON && xhr.responseJSON.message) {
//                     msg = xhr.responseJSON.message;
//                 }

//                 Swal.fire({
//                     icon: "error",
//                     title: "Duplicate Failed",
//                     html: msg,
//                 });
//             });
//     });

// });

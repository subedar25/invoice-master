// document.addEventListener("DOMContentLoaded", function () {

//     const quill = new Quill("#status_notes_editor", { theme: "snow" });
//     const form = document.getElementById("userFormEdit");
//     const changePasswordSelect = document.getElementById("change_password");
//     const passwordField = document.getElementById("passwordField");

//     // Toggle Password
//     if (changePasswordSelect) {
//         const toggleField = () =>
//             passwordField.style.display = (changePasswordSelect.value === "1") ? "block" : "none";
//         toggleField();
//         changePasswordSelect.addEventListener("change", toggleField);
//     }

//     $(document).on("submit", "#userFormEdit", function (e) {
//         e.preventDefault();

// document.querySelector('#userFormEdit')?.addEventListener('submit', function () {
//     const quillHtml = quill.root.innerHTML;
//     document.getElementById('status_notes_input').value = quillHtml;
// });
//         const formData = new FormData(form);

//         $.ajax({
//             url: form.action,
//             type: "POST",
//             data: formData,
//             processData: false,
//             contentType: false,
//             success: function(res) {

//                 $('#userModal').on('hidden.bs.modal', function () {
//                     $('#addUserButton').focus();
//                 });

//                 $('#userModal').modal('hide');

//                 Swal.fire({
//                     icon: "success",
//                     title: res.message,
//                     timer: 1500,
//                     showConfirmButton: false
//                 }).then(() => location.reload());
//             },
//             error: function(xhr) {
//                 Swal.fire({
//                     icon: "error",
//                     title: "Error",
//                     text: xhr.responseJSON?.message ?? "Something went wrong!"
//                 });
//             }
//         });
//     });
// });


// document.addEventListener("DOMContentLoaded", function () {

//     const quill = new Quill("#status_notes_editor", { theme: "snow" });
//     const changePasswordSelect = document.getElementById("change_password");
//     const passwordField = document.getElementById("passwordField");

//     // Toggle password field
//     if (changePasswordSelect) {
//         const toggleField = () =>
//             passwordField.style.display = (changePasswordSelect.value === "1") ? "block" : "none";
//         toggleField();
//         changePasswordSelect.addEventListener("change", toggleField);
//     }

//     // Submit via AJAX
//     $(document).on("submit", "#userFormEdit", function (e) {
//         e.preventDefault();

//         // Update hidden field with Quill content
//         document.getElementById("status_notes_input").value = quill.root.innerHTML;

//         const formData = new FormData(this);
//         const action = $(this).attr("action");

//         $.ajax({
//             url: action,
//             method: "POST",
//             data: formData,
//             processData: false,
//             contentType: false,
//             success: function(res) {

//                 $('#userModal').modal('hide');

//                 Swal.fire({
//                     icon: "success",
//                     title: res.message,
//                     timer: 1500,
//                     showConfirmButton: false
//                 }).then(() => location.reload());
//             },
//             error: function(xhr) {
//                 Swal.fire({
//                     icon: "error",
//                     title: "Error",
//                     text: xhr.responseJSON?.message ?? "Something went wrong!"
//                 });
//             }
//         });
//     });

// });




// document.addEventListener("DOMContentLoaded", function () {
//     let quill;

//     if (document.querySelector("#status_notes_editor")) {
//         quill = new Quill("#status_notes_editor", { theme: "snow" });
//     }

//     $(document).on("submit", "#userFormEdit", function (e) {
//         e.preventDefault();

//         //  Update hidden input with Quill content
//         if (quill) {
//             document.getElementById("status_notes_input").value = quill.root.innerHTML;
//         }

//         const formData = new FormData(this);
//         formData.append('_method', 'PUT'); // Laravel expects PUT for update

//         $.ajax({
//             url: $(this).attr('action'), // should be /users/{id}
//             method: 'POST', // Laravel accepts POST with _method=PUT
//             data: formData,
//             processData: false,
//             contentType: false,
//             success: function (res) {
//                 $('#userModal').modal('hide');
//                 Swal.fire({
//                     icon: "success",
//                     title: res.message,
//                     timer: 1500,
//                     showConfirmButton: false
//                 }).then(() => location.reload());
//             },
//             error: function (xhr) {
//                 Swal.fire({
//                     icon: "error",
//                     title: "Error",
//                     text: xhr.responseJSON?.message ?? "Something went wrong!"
//                 });
//             }
//         });
//     });
// });

document.addEventListener("DOMContentLoaded", function () {

    // Initialize Quill only if editor exists
    let quill = null;
    const editor = document.querySelector("#status_notes_editor");

    if (editor) {
        quill = new Quill("#status_notes_editor", { theme: "snow" });
    }

    // Password toggle
    const changePasswordSelect = document.getElementById("change_password");
    const passwordField = document.getElementById("passwordField");

    if (changePasswordSelect && passwordField) {
        const toggleField = () => {
            passwordField.style.display = changePasswordSelect.value === "1" ? "block" : "none";
        };

        toggleField(); // show on page load
        changePasswordSelect.addEventListener("change", toggleField);
    }

    // Submit form via AJAX
    $(document).on("submit", "#userFormEdit", function (e) {
        e.preventDefault();

        // Put Quill HTML into hidden input
        if (quill) {
            document.getElementById("status_notes_input").value = quill.root.innerHTML;
        }

        const form = this;
        const formData = new FormData(form);

        $.ajax({
            url: form.action,   // correct update URL
            method: "POST",     // Laravel handles PUT via hidden _method
            data: formData,
            processData: false,
            contentType: false,

            success: function (res) {
                // $('#userModal').modal('hide');
                $('#userModal').on('hidden.bs.modal', function () {
                document.activeElement.blur(); // remove focus from modal contents
});


                Swal.fire({
                    icon: "success",
                    title: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            },

            error: function (xhr) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: xhr.responseJSON?.message ?? "Something went wrong!"
                });
            }
        });
    });

});

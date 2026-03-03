var createQuill = null;
$(document).on("shown.bs.modal", "#userModal", function () {

    // Destroy previous instance if modal was reopened
    if (createQuill && createQuill.root) {
        createQuill = null;
    }

    const container = document.querySelector("#status_notes_editor");

    if (container) {
        createQuill = new Quill(container, {
            theme: "snow"
        });
    }
});

$(document).on("submit", "#userForm", function (e) {
    e.preventDefault();

    if (createQuill && createQuill.root) {
        $("#status_notes_input").val(createQuill.root.innerHTML);
    } else {
        $("#status_notes_input").val('');
    }

    let formData = new FormData(this);

    $.ajax({
        url: this.action,
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            $("#userModal").modal("hide");

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
                text: xhr.responseJSON?.message ?? "Validation failed"
            });
        }
    });
});

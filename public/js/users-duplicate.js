document.addEventListener("DOMContentLoaded", function () {
    const passwordField = document.getElementById("passwordField");
    const form = document.getElementById("userFormEdit");
    const changePasswordSelect = document.getElementById("change_password");
    
    function togglePasswordField() {
        passwordField.style.display = (changePasswordSelect.value == "1") ? "block" : "none";
    }

    if (changePasswordSelect) {
        togglePasswordField();
        changePasswordSelect.addEventListener("change", togglePasswordField);
    }

    $('#userFormDuplicate').submit(function(e){
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
                Swal.fire("Done!", "User duplicated!", "success");
                $('#userModal').modal('hide');
                $('#usersTable').DataTable().ajax.reload(null, false);
            },
            error: function(xhr){
                Swal.fire("Failed!", "Could not duplicate!", "error");
            }
        });
    });
});
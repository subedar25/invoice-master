$(document).ready(function () {

    const form = $("#client-create-form");
    const submitBtn = form.find("button[type='submit']");

    // Helper: Show SweetAlert error list
    function swalErrorList(title, messages) {
        let html = "<ul style='text-align:left'>";
        messages.forEach(m => html += `<li>${m}</li>`);
        html += "</ul>";

        Swal.fire({
            icon: "error",
            title: title,
            html: html
        });
    }

    // Submit Handler
    form.on("submit", function (e) {
        e.preventDefault();

        let formData = form.serialize();

        // Button state
        submitBtn.prop("disabled", true).html("Saving...");

        $.ajax({
            url: form.attr("action"),
            method: "POST",
            data: formData,
            success: function (res) {

                Swal.fire({
                    icon: "success",
                    title: "Client Created!",
                    text: "The client has been successfully added.",
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "/clients";
                });
            },

            error: function (jqXHR) {
                submitBtn.prop("disabled", false).html("Create");

                // Laravel validation errors
                if (jqXHR.status === 422) {
                    let errors = jqXHR.responseJSON.errors;
                    let messages = [];

                    // highlight fields
                    $("input, select").removeClass("is-invalid");

                    $.each(errors, function (field, msgs) {
                        messages.push(msgs[0]);

                        // highlight invalid inputs
                        let input = $(`[name='${field}']`);
                        input.addClass("is-invalid");

                        // scroll to first error
                        $('html,body').animate({
                            scrollTop: input.offset().top - 120
                        }, 400);
                    });

                    swalErrorList("Validation Error", messages);
                    return;
                }

                // Server error
                Swal.fire({
                    icon: "error",
                    title: "Server Error",
                    text: "Something went wrong. Please try again."
                });
            }
        });

    });

    // Optional: live validation highlight
    $("input, select").on("input change", function () {
        $(this).removeClass("is-invalid");
    });

});

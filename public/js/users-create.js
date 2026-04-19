var createQuill = null;

function escapeHtmlUserForm(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function clearUserFormValidation($form) {
    $form.find(".is-invalid").removeClass("is-invalid");
    $form.find(".laravel-validation-error").remove();
    $form
        .find(".select2-container .select2-selection")
        .removeClass("border-danger");
}

/** Map Laravel error keys (e.g. roles, roles.0) to the form field in the modal/full page. */
function findFieldForLaravelKey($form, key) {
    let $field = $form.find('[name="' + key + '"]');
    if ($field.length) {
        return $field.first();
    }
    $field = $form.find('[name="' + key + '[]"]');
    if ($field.length) {
        return $field.first();
    }
    if (key.indexOf(".") !== -1) {
        const base = key.split(".")[0];
        $field = $form.find('[name="' + base + '[]"]');
        if ($field.length) {
            return $field.first();
        }
        $field = $form.find('[name="' + base + '"]');
        if ($field.length) {
            return $field.first();
        }
    }
    return $();
}

/**
 * @returns {string[]} human-readable messages for Swal
 */
function applyLaravelValidationErrors($form, errors) {
    clearUserFormValidation($form);
    const lines = [];
    Object.keys(errors).forEach(function (key) {
        const msgs = errors[key];
        const text = Array.isArray(msgs) ? msgs.join(" ") : String(msgs);
        lines.push(text);

        const $field = findFieldForLaravelKey($form, key);
        if (!$field.length) {
            return;
        }
        $field.addClass("is-invalid");
        const $fg = $field.closest(".form-group");
        const $target = $fg.length ? $fg : $field.parent();
        $target.append(
            '<div class="invalid-feedback d-block laravel-validation-error">' +
                escapeHtmlUserForm(text) +
                "</div>"
        );
        if ($field.hasClass("select2-hidden-accessible")) {
            $field
                .next(".select2-container")
                .find(".select2-selection")
                .addClass("border-danger");
        }
    });
    return lines;
}

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

    const $form = $(this);

    if (createQuill && createQuill.root) {
        $("#status_notes_input").val(createQuill.root.innerHTML);
    } else {
        $("#status_notes_input").val("");
    }

    const formData = new FormData(this);

    $.ajax({
        url: this.action,
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
        },
        success: function (res) {
            if ($("#userModal").length) {
                $("#userModal").modal("hide");
            }

            var redirectUrl = $form.data("redirectAfterCreate");
            Swal.fire({
                icon: "success",
                title: res.message,
                timer: 1500,
                showConfirmButton: false,
            }).then(function () {
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                } else {
                    location.reload();
                }
            });
        },
        error: function (xhr) {
            const json = xhr.responseJSON;
            if (xhr.status === 422 && json && json.errors) {
                const lines = applyLaravelValidationErrors($form, json.errors);
                const html =
                    "<ul class=\"text-start mb-0 ps-3\"><li>" +
                    lines.map(escapeHtmlUserForm).join("</li><li>") +
                    "</li></ul>";
                Swal.fire({
                    icon: "error",
                    title: "Please correct the following",
                    html: html,
                });
                const $first = $form.find(".is-invalid").first();
                if ($first.length) {
                    $first[0].scrollIntoView({ behavior: "smooth", block: "center" });
                }
                return;
            }
            let msg =
                json?.message ||
                (xhr.status ? "Request failed (" + xhr.status + ")" : "Validation failed");
            if (json?.errors) {
                msg = Object.values(json.errors)
                    .flat()
                    .join("\n");
            }
            Swal.fire({
                icon: "error",
                title: "Error",
                text: msg,
            });
        },
    });
});

$(document).ready(function () {

    const csrf = $('meta[name="csrf-token"]').attr("content");

    //  CLICK TO EDIT (Universal) 
  $(document).on("click", "td.editable-text, td.editable-textarea, td.editable-select, td.editable-multi-select", function (e) {

    const td = $(this);  //  must be here

    //  Close all other open inline editors (auto-cancel)
    $("td.editing").not(td).each(function () {
        const otherTd = $(this);
        const originalValue = otherTd.data("oldValue");
        otherTd.removeClass("editing").text(originalValue);
    });

    // If clicking same td again → do nothing
    if (td.hasClass("editing")) return;

    td.addClass("editing");

    const field = td.data("field");
    const userId = td.closest("tr").data("id");
    const oldValue = td.text().trim();


        let input;

        //  TEXT 
        if (td.hasClass("editable-text")) {
            input = $(`<input type="text" class="form-control form-control-sm" value="${oldValue}">`);
        }

        // -------- TEXTAREA --------
        if (td.hasClass("editable-textarea")) {
            input = $(`<textarea class="form-control form-control-sm">${oldValue}</textarea>`);
        }

        //  SELECT (Dropdown) 
        // if (td.hasClass("editable-select")) {
        //     const opts = td.data("options");
        //     input = $('<select class="form-select form-select-sm"></select>');
        //     $.each(opts, function (key, val) {
        //         const option = $("<option>").text(val).val(val);
        //         if (val == oldValue) option.prop("selected", true);
        //         input.append(option);
        //     });
        // }
if (td.hasClass("editable-select")) {
    const opts = td.data("options");
    input = $('<select class="form-select form-select-sm"></select>');

    $.each(opts, function (i, obj) {
        const option = $("<option>").val(obj.id).text(obj.label);
        if (obj.label == oldValue || obj.id == oldValue) {
            option.prop("selected", true);
        }
        input.append(option);
    });
}

        //  MULTI SELECT (tags UI) 
        if (td.hasClass("editable-multi-select")) {
            const opts = td.data("options");
            input = $('<select class="form-select form-select-sm" multiple></select>');
            $.each(opts, function (i, val) {
                input.append(`<option value="${val}">${val}</option>`);
            });

            // pre-select existing items
            oldValue.split(",").forEach(v => {
                input.find(`option[value="${v.trim()}"]`).prop("selected", true);
            });
        }

        // Append OK button
        const okBtn = $(`<button class="btn btn-success btn-sm ms-1">✓</button>`);

        td.data("oldValue", oldValue);
        td.html("").append(input).append(okBtn);
        input.focus();

        //  SAVE FUNCTION 
        function saveInline() {

            let newValue = input.val();
            if (Array.isArray(newValue)) newValue = newValue.join(",");

            $.ajax({
                url: `/users/${userId}`,
                type: "POST",
                data: {
                    _token: csrf,
                    _method: "PUT",
                    [field]: newValue
                },
                success: function () {

    let showValue = newValue; // default

    //  STATUS (ID → LABEL) 
    if (field === "status_id") {
        const opts = td.data("options");
        const found = opts.find(o => o.id == newValue);
        showValue = found ? found.label : newValue;
    }

    //  DRIVER (1/0 → Yes/No) 
    else if (field === "driver") {
        // showValue = (newValue == 1 || newValue === "Yes") ? "Yes" : "No";
        showValue=newValue==1?"Yes":"No";
    }

    //  ACTIVE 
    else if (field === "active") {
        showValue = (newValue == 1 || newValue === "Active") ? "Active" : "Inactive";
    }

    //  CHANGE PASSWORD 
    else if (field === "change_password") {
        showValue = (newValue == 1 || newValue === "Change") ? "Change" : "Current";
    }

    //  ROLES / PERMISSIONS (comma-separated) 
    else if (field === "roles" || field === "permissions") {
        showValue = newValue.replace(/,/g, ", ");
    }

    //  UPDATE CELL DISPLAY 
    td.text(showValue);
    td.removeClass("editing");
},

                
                error: function () {
                    td.removeClass("editing").html(oldValue);
                    alert("Error saving!");
                }
            });
        }

        // click ✓
        okBtn.on("click", function () {
            saveInline();
        });

        // Enter key for save
        input.on("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                saveInline();
            }
            if (e.key === "Escape") {
                td.removeClass("editing").text(oldValue);
            }
        });

        // blur optional: keep open
    });
// Close inline edit when clicking OUTSIDE the table
$(document).on("click", function (e) {

    // If click is inside an editable cell or input, ignore
    if (
        $(e.target).closest("td.editing").length ||
        $(e.target).closest("td.editable-text").length ||
        $(e.target).closest("td.editable-textarea").length ||
        $(e.target).closest("td.editable-select").length ||
        $(e.target).closest("td.editable-multi-select").length ||
        $(e.target).is("input, textarea, select, button")
    ) {
        return;
    }

    // Otherwise close ALL inline editors (auto-cancel)
    $("td.editing").each(function () {
        const td = $(this);
        const originalValue = td.data("oldValue");
        td.removeClass("editing").text(originalValue);
    });
});

});

$(document).ready(function () {

    if (!window.inlineConfig) return;

    const cfg   = window.inlineConfig;
    const csrf = $('meta[name="csrf-token"]').attr("content");

    /* ======================================================
     * HELPERS
     * ====================================================== */

    function canEdit(field) {
        if (!cfg.permissions) return true;
        return cfg.permissions[field] !== false;
    }

    function storeOriginal(td) {
        td.data({
            oldText: td.text().trim(),
            oldHtml: td.html(),
            link: td.find("a").attr("href") || td.data("link") || null
        });
    }

    function restore(td) {
        td.removeClass("editing").html(td.data("oldHtml"));
    }

    /* ======================================================
     * VALUE RENDERER (UI SAFE)
     * ====================================================== */
    function renderValue(td, field, value) {

        const type  = cfg.fields[field];
        let display = value;

        /* ---------- BOOLEAN ---------- */
        if (type === "boolean") {
            display = value == 1 ? "Yes" : "No";
        }

        /* ---------- SELECT ---------- */
        if (type === "select") {
            const opts = cfg.options?.[field];

            if (opts && typeof opts === "object" && !Array.isArray(opts)) {
                display = opts[value] ?? value;
            }

            if (Array.isArray(opts)) {
                const found = opts.find(o =>
                    String(o.id ?? o.value ?? o) === String(value)
                );
                display = found
                    ? (found.label ?? found.name ?? found.text ?? found)
                    : value;
            }
        }

        /* ---------- MULTI ---------- */
        if (type === "multi") {
            const opts = cfg.options?.[field] ?? [];
            display = String(value)
                .split(",")
                .map(v => {
                    if (typeof opts === "object" && !Array.isArray(opts)) {
                        return opts[v] ?? v;
                    }
                    if (Array.isArray(opts)) {
                        const found = opts.find(o =>
                            String(o.id ?? o.value ?? o) === String(v)
                        );
                        return found
                            ? (found.label ?? found.name ?? found.text ?? found)
                            : v;
                    }
                    return v;
                })
                .join(", ");
        }

        /* ---------- RENDER MODE ---------- */
        const renderType = td.data("render");
        const link       = td.data("link") || td.data("url");

        td.removeClass("editing");

        if (renderType === "badge") {
            td.html(`<span class="badge bg-light text-dark">${display}</span>`);
        }
        else if (link) {
            td.html(`<a href="${link}" class="entity-link fw-semibold text-primary">${display}</a>`);
        }
        else {
            td.text(display);
        }
    }

    /* ======================================================
     * DOUBLE CLICK → NAVIGATE
     * ====================================================== */
    $(document).on("dblclick", "td.inline-edit", function () {
        const url = $(this).data("url");
        if (url) window.location.href = url;
    });

    $(document).on("click", "td.inline-edit a", e => e.stopPropagation());

    /* ======================================================
     * SINGLE CELL EDIT
     * ====================================================== */
    $(document).on("click", "td.inline-edit, span.inline-edit", function (e) {

        const td    = $(this);
        const field = td.data("field");
        const id    = td.closest("tr").data("id");
        const type  = cfg.fields?.[field];

        if (!type || !canEdit(field) || td.hasClass("editing")) return;

        $("td.editing").each(function () {
            restore($(this));
        });

        storeOriginal(td);
        td.addClass("editing");

        let input;

        switch (type) {

            case "text":
                input = $(`<input class="form-control form-control-sm" value="${td.data("oldText")}">`);
                break;

            case "textarea":
                input = $(`<textarea class="form-control form-control-sm">${td.data("oldText")}</textarea>`);
                break;

            case "boolean":
                input = $(`
                    <select class="form-select form-select-sm">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                `).val(td.data("oldText") === "Yes" ? 1 : 0);
                break;

            case "select":
                input = $(`<select class="form-select form-select-sm"></select>`);
                buildOptions(input, field);
                break;

            case "multi":
                input = $(`<select multiple class="form-select form-select-sm"></select>`);
                buildOptions(input, field, true);
                break;

                // case "date":
                // const dateValue = td.data("oldText") ? new Date(td.data("oldText")).toISOString().split('T')[0] : "";
                // input = $(`<input type="date" class="form-control form-control-sm inline-input" value="${dateValue}">`);
                // break;
// case "date":
//     const old = td.data("oldText") || td.text().trim();
//     const dateValue = old
//         ? new Date(old).toISOString().split('T')[0]
//         : "";

//     input = $(`
//         <input type="date"
//                class="form-control form-control-sm inline-input"
//                value="${dateValue}">
//     `);
//     break;
case "date": {
    const raw = td.data("oldText") || td.text().trim();

    // Expect: YYYY-MM-DD or YYYY-MM-DD HH:mm
    const dateValue = raw
        ? raw.substring(0, 10)
        : "";

    input = $(`
        <input type="date"
               class="form-control form-control-sm inline-input"
               value="${dateValue}">
    `);
    break;
    
}
case "datetime": {
    input = $('<input type="text" class="form-control form-control-sm">');

    const fp = flatpickr(input[0], {
        enableTime: true,
        allowInput: true,
        time_24hr: false,
        dateFormat: "Y-m-d h:i K",

        onOpen() {
            td.data("datetime-open", true);
        },

        onClose() {
            setTimeout(() => {
                td.removeData("datetime-open");
            }, 100);
        }
    });

    input.data("fp", fp);
    break;
}

                }
        if (!input) return restore(td);

        const ok  = $(`<button class="btn btn-success btn-sm ms-1">✓</button>`);
        const esc = $(`<button class="btn btn-light btn-sm ms-1">✕</button>`);

        td.empty().append(input, ok, esc);
        input.focus();

        esc.on("click", () => restore(td));
        ok.on("click", () => save(td, field, id, input.val()));
    });

    /* ======================================================
     * OPTION BUILDER
     * ====================================================== */
    function buildOptions(select, field, multi = false) {

    const opts = cfg.options?.[field];
    if (!opts) return;

    const raw = select.closest("td").data("oldText") ?? "";
    const selected = String(raw)
        .split(",")
        .map(v => v.trim())
        .filter(Boolean);

    // Object format { key: label }
    if (typeof opts === "object" && !Array.isArray(opts)) {
        Object.entries(opts).forEach(([value, label]) => {
            const sel = multi && selected.includes(label) ? "selected" : "";
            select.append(`<option value="${value}" ${sel}>${label}</option>`);
        });
    }

    // Array format [{id,label}] or ["a","b"]
    if (Array.isArray(opts)) {
        opts.forEach(o => {
            const value = o.id ?? o.value ?? o;
            const label = o.label ?? o.name ?? o.text ?? o;
            const sel   = multi && selected.includes(label) ? "selected" : "";
            select.append(`<option value="${value}" ${sel}>${label}</option>`);
        });
    }
}


    /* ======================================================
     * SAVE HANDLER
     * ====================================================== */
   function save(td, field, id) {

    if (!cfg.url && !cfg.updateUrl) {
        console.error("Inline edit URL missing");
        restore(td);
        return;
    }

    const type = cfg.fields[field];
    let value;

    // DATETIME
    if (type === "datetime") {
        const input = td.find("input");
        const fp = input.data("fp");

        if (!fp || !fp.selectedDates.length) {
            restore(td);
            return;
        }

        value = moment(fp.selectedDates[0]).format("YYYY-MM-DD HH:mm");
    } else {
        value = td.find("input, select, textarea").val();
    }

    const urlTemplate = cfg.url || cfg.updateUrl;
    const url = urlTemplate.endsWith('/')
        ? urlTemplate + id
        : urlTemplate.replace("_ID_", id);

    $.ajax({
        url,
        type: cfg.method || "PATCH",
        data: { _token: csrf, [field]: value },

        success() {
            const display =
                type === "datetime"
                    ? moment(value).format("YYYY-MM-DD hh:mm A")
                    : value;

            renderValue(td, field, display);
            showToast("Updated successfully", "success");
        },

        error(xhr) {
            console.error(xhr.responseText);
            restore(td);
            showToast("Update failed", "error");
        }
    });
}



    /* ======================================================
     * ROW LEVEL EDIT
     * ====================================================== */
    if (cfg.rowEdit) {

        $(document).on("click", ".row-edit", function () {
            $(this).closest("tr").find("td.inline-edit").trigger("click");
        });

        $(document).on("click", ".row-cancel", function () {
            $(this).closest("tr").find("td.editing").each(function () {
                restore($(this));
            });
        });
    }

    /* ======================================================
     * CLICK OUTSIDE → CANCEL
     * ====================================================== */
    $(document).on("click", function (e) {
        //  if ($("td.editing").data("datetime-open")) return;
        if ($(e.target).closest(".inline-edit, input, select, textarea, button").length) return;
        $("td.editing").each(function () { restore($(this)); });
    });

});

/* ======================================================
 * UI HELPERS
 * ====================================================== */
function showLoader() {}
function hideLoader() {}

function showToast(msg, type = "success") {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: type,
        title: msg,
        showConfirmButton: false,
        timer: 1500
    });
}

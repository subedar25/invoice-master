/**
 * Generic CRUD Management System
 * This file provides a reusable CRUD interface for DataTables.
 * It depends on: jQuery, DataTables, Bootstrap 5, SweetAlert2
 * It also requires the ModalFormManager to handle form submissions and modal interactions.
 */

// Generic CRUD Manager
const CRUDManager = (function () {
    let table;
    let config = {};
    let buttonIndices = {};

    const defaultConfig = {
        tableSelector: "#dataTable",
        selectAllSelector: "#selectAll",
        rowCheckSelector: ".row-check",
        selectedRowClass: "selected-row",
        serverSide: null,
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, "All"],
        ],
        language: {
            lengthMenu: 'Show _MENU_',
            paginate: {
                next: '<i class="fa fa-angle-double-right"></i>',
                previous: '<i class="fa fa-angle-double-left"></i>'
            }
        },
        order: null,
        // --- FILTER INPUT INTEGRATION ---
        // This new array will hold the IDs of the inputs to watch.
        filterInputs: [],
        // ---------------------------------
        buttons: [
            {
                text: '<i class="fa fa-plus"></i> New',
                className: "btn btn-success",
                action: "create",
                requireSelection: false,
            },
            {
                text: '<i class="fa fa-edit"></i> Edit',
                className: "btn btn-primary buttons-edit",
                action: "edit",
                requireSingle: true,
            },
            {
                text: '<i class="fa fa-trash"></i> Delete',
                className: "btn btn-danger buttons-del",
                action: "delete",
                requireSelection: true,
            },
            { extend: "colvis", text: '<i class="fa fa-columns"></i> Columns' },
            { extend: "copy", text: '<i class="fa fa-copy"></i> Copy' },
            { extend: "excel", text: '<i class="fa fa-file-excel"></i> Excel' },
            { extend: "pdf", text: '<i class="fa fa-file-pdf"></i> PDF' },
        ],
        messages: {
            confirmDelete: "Are you sure?",
            deleteText: "Selected items will be permanently deleted!",
            successTitle: "Success",
            errorTitle: "Error",
            loadingText: "Loading...",
        },
    };

    const init = function (pageConfig) {
        config = { ...defaultConfig, ...pageConfig };
        initDataTable();
        bindEvents();
        setTimeout(() => {
            updateButtons();
        }, 100);
    };

    const initDataTable = function () {
        const commonConfig = {
            paging: true,
            searching: false, // Keep searching disabled as we use custom filters
            ordering: true,
            pageLength: config.pageLength,
            lengthMenu: config.lengthMenu,
            language: config.language,
            order: config.order || undefined,
            dom: '<"top"Bipl>rt<"bottom bottomAlign"ip><"clear">',
            // --- FILTER INPUT INTEGRATION ---
            // The initComplete is now simpler as we don't build HTML here.
            initComplete: function() {
                // Existing Layout Logic
                $('.dataTables_length').appendTo('.dataTables_wrapper .top');
                $('.dataTables_length').addClass('ml-2 d-flex align-items-center');
                $('.top .dataTables_length, .top .dataTables_paginate').wrapAll('<div class="length_pagination"></div>');
                $('.top .dataTables_info, .top .length_pagination').wrapAll('<div class="show_page_align"></div>');
            },
            // ---------------------------------
            buttons: config.buttons.map((button, index) => {
                if (button.extend) return button;
                buttonIndices[button.action] = index;
                return {
                    text: button.text,
                    className: button.className,
                    enabled: false,
                    action: () => handleAction(button.action),
                };
            }),
        };

        if (config.serverSide) {
            table = $(config.tableSelector).DataTable({
                ...commonConfig,
                processing: true,
                serverSide: true,
                // --- FILTER INPUT INTEGRATION ---
                // Modify the ajax object to include filter data from our custom inputs
                ajax: {
                    url: config.serverSide.url,
                    type: "GET",
                    data: function(d) {
                        // d is the default data DataTables sends
                        // We loop through our filterInputs config to get values
                        config.filterInputs.forEach(filter => {
                            d[filter.name] = $('#' + filter.id).val();
                        });
                    }
                },
                // ---------------------------------
                columns: config.serverSide.columns,
                ...(config.serverSide.columnDefs && { columnDefs: config.serverSide.columnDefs }),
            });
        } else {
            table = $(config.tableSelector).DataTable({
                ...commonConfig,
                columnDefs: [{ orderable: false, targets: 0 }],
            });
        }
    };

    const bindEvents = function () {
        // --- FILTER INPUT INTEGRATION ---
        // Add event listeners for our custom inputs based on the configuration
        config.filterInputs.forEach(filter => {
            if (filter.type === 'text') {
                $(document).on('keyup', `#${filter.id}`, function() {
                    table.draw(); // Redraw the table on keyup
                });
            } else if (filter.type === 'select') {
                $(document).on('change', `#${filter.id}`, function() {
                    table.draw(); // Redraw the table on change
                });
            }
        });
        // ---------------------------------

        $(config.tableSelector).on("change", config.rowCheckSelector, function () {
            toggleRowSelection(this);
        });

        $(config.selectAllSelector).on("change", toggleAllSelection);

        // Event listeners for inline action buttons
        $(document).on('click', '.action-icon.edit-item', function(e) {
            e.preventDefault();
            const url = $(this).data('url');
            const title = $(this).data('title');
            ModalFormManager.openModal(url, title);
        });

        $(document).on('click', '.action-icon.delete-item', function(e) {
            e.preventDefault();
            const url = $(this).data('url');
            const name = $(this).data('name');
            singleDelete(url, name);
        });
    };

    const getSelectedRows = function () {
        return $(
            `${config.tableSelector} tbody ${config.rowCheckSelector}:checked`
        ).closest("tr");
    };

    const getSelectedIds = function () {
        const ids = [];
        $(
            `${config.tableSelector} tbody ${config.rowCheckSelector}:checked`
        ).each(function () {
            // Get the 'value' directly from the checkbox element
            ids.push($(this).val());
        });
        return ids;
    };

    const updateButtons = function () {
        const selectedCount = getSelectedIds().length;

        Object.keys(buttonIndices).forEach((action) => {
            const buttonConfig = config.buttons.find(
                (b) => b.action === action
            );
            if (!buttonConfig) return;

            let enabled = true;

            if (buttonConfig.requireSingle) {
                enabled = selectedCount === 1;
            } else if (buttonConfig.requireSelection) {
                enabled = selectedCount > 0;
            }

            if (table && table.button) {
                table.button(buttonIndices[action]).enable(enabled);
            }
        });
    };

    const toggleRowSelection = function (checkbox) {
        const row = $(checkbox).closest("tr");
        row.toggleClass(config.selectedRowClass, $(checkbox).is(":checked"));
        updateButtons();
    };

    const toggleAllSelection = function () {
        const isChecked = $(config.selectAllSelector).prop("checked");
        $(config.rowCheckSelector)
            .prop("checked", isChecked)
            .each(function () {
                toggleRowSelection(this);
            });
    };

   const getEndpoint = function (action, id = null) {
    // For actions that need a full URL generated on the server (like create, delete)
    if (typeof config.endpoints[action] === 'string' && config.endpoints[action].includes('/')) {
        return config.endpoints[action];
    }

    // For actions that should be handled by the client-side router (like edit)
    if (action === 'edit' && id) {
        // If you have Ziggy installed (recommended for SPAs)
        if (typeof route !== 'undefined') {
            // The first parameter is the route name, the second is the parameter object
            return route(config.endpoints[action], { role: id });
        }

        // Fallback if Ziggy is not installed
        // This is less flexible but works for standard resource routes
        const baseUrl = window.location.origin + "/master-app/roles/";
        return `${baseUrl}${id}/edit`;
    }

    // Return a default or throw an error if the action is not found
    throw new Error(`Endpoint for action "${action}" not configured correctly.`);
};

    const handleAction = function (action) {
        switch (action) {
            case "create":
                // Use ModalFormManager to open the modal
                ModalFormManager.openModal(getEndpoint("create"), `New ${config.resource}`);
                break;

            case "edit":
                const editId = getSelectedIds()[0];
                if (editId) {
                    // Use ModalFormManager to open the modal
                    ModalFormManager.openModal(
                        getEndpoint("edit", editId),
                        `Edit ${config.resource}`
                    );
                }
                break;

            case "duplicate":
                const dupId = getSelectedIds()[0];
                if (dupId) {
                    // Use ModalFormManager to open the modal
                    ModalFormManager.openModal(
                        getEndpoint("duplicate", dupId),
                        `Duplicate ${config.resource}`
                    );
                }
                break;

            case "delete":
                bulkDelete();
                break;
        }
    };

    const showLoadingSpinner = function () {
        return `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 fw-semibold">${config.messages.loadingText}</p>
            </div>
        `;
    };

    const bulkDelete = function () {
        const ids = getSelectedIds();
        if (!ids.length) return;

        Notification.confirm(config.messages.deleteText, {
            title: config.messages.confirmDelete,
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                const selectedRows = getSelectedRows();
                const originalRowContent = [];

                // Show loader on selected rows
                selectedRows.each(function (index) {
                    const row = $(this);
                    originalRowContent[index] = row.html(); // Store original HTML
                    const colCount = row.find("td").length;
                    row.html(
                        `<td colspan="${colCount}">${showLoadingSpinner()}</td>`
                    );
                });

                // Execute AJAX call
                Ajax.delete(getEndpoint("delete"), { ids })
                    .then(() => {
                        // Use the DataTables API to remove the rows and redraw the table
                        table.rows(selectedRows).remove().draw();

                        // Reset the UI state
                        $(config.selectAllSelector).prop("checked", false);
                        updateButtons();

                        // Show a success notification
                        Notification.success(
                            `${ids.length} ${config.resource}(s) deleted successfully.`
                        );
                    })
                    .catch((error) => {
                        // On error, restore original content
                        selectedRows.each(function (index) {
                            $(this).html(originalRowContent[index]);
                        });

                        // Handle specific error types
                        if (error.status === 403) {
                            Notification.error(
                                "You do not have permission to perform this action."
                            );
                        } else {
                            // Generic error for other cases
                            Notification.error(
                                error.message ||
                                    `Failed to delete ${config.resource}(s).`
                            );
                        }
                    });
            }
        });
    };

    const singleDelete = function(url, itemName) {
        const deleteText = `Are you sure you want to delete "${itemName}"? This action cannot be undone.`;

        Notification.confirm(deleteText, {
            title: "Delete Confirmation",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                Ajax.delete(url)
                    .then(() => {
                        Notification.success(`"${itemName}" deleted successfully.`);
                        setTimeout(() => location.reload(), 1500);
                    })
                    .catch((error) => {
                        if (error.status === 403) {
                            Notification.error("You do not have permission to perform this action.");
                        } else {
                            Notification.error(error.message || `Failed to delete "${itemName}".`);
                        }
                    });
            }
        });
    };

    // Public API
    return {
        init: init,
    };
})();

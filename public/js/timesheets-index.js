$.fn.dataTable.ext.errMode = 'none';

$(document).ready(function () {
    //  * GLOBAL AJAX ERROR HANDLER (Timesheets)
    $(document).ajaxError(function (event, xhr, settings) {
        if (!settings?.url) return;

        if (settings.url.includes('/timesheets')) {
            let message = 'Something went wrong while processing timesheets.';
            let alertClass = 'alert-danger';

            if (xhr.status === 403) {
                message = 'You do not have permission to perform this action.';
            } else if (xhr.status === 404) {
                message = 'Requested timesheets endpoint not found.';
                alertClass = 'alert-warning';
            } else if (xhr.status >= 500) {
                message = 'Server error. Please try again later.';
            }

            $('#dataTable_wrapper').prepend(`
                <div class="alert ${alertClass} text-center m-3">
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    <strong>Error</strong> ${message}
                </div>
            `);
        }
    });

    //  * TIMESHEETS CRUD MANAGER
    CRUDManager.init({
        resource: 'Timesheet',

        serverSide: {
            url: "/timesheets/get-data",
            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'user', name: 'user.first_name' },
                { data: 'start_time', name: 'start_time' },
                { data: 'end_time', name: 'end_time' },
                // { data: 'hours', orderable: false, searchable: false },
                { data: 'worked_hours', name: 'worked_hours' },

                { data: 'clock_in_mode', name: 'clock_in_mode' },
                { data: 'type', name: 'type' },
                { data: 'notes', name: 'notes' }
            ]
        },

        endpoints: {
            create: "/timesheets/create",
            edit: "/timesheets/{id}/edit",
            delete: "/timesheets/bulk-delete",
            duplicate: "/timesheets/{id}/duplicate"
        },

        buttons: [
            {
                text: '<i class="fa fa-plus"></i> New',
                className: 'btn btn-success',
                action: 'create',
                requireSelection: false
            },
            {
                text: '<i class="fa fa-edit"></i> Edit',
                className: 'btn btn-primary',
                action: 'edit',
                requireSingle: true
            },
            {
                text: '<i class="fa fa-copy"></i> Duplicate',
                className: 'btn btn-primary',
                action: 'duplicate',
                requireSingle: true
            },
            {
                text: '<i class="fa fa-trash"></i> Delete',
                className: 'btn btn-danger',
                action: 'delete',
                requireSelection: true
            },
            {
                extend: 'copy',
                text: '<i class="fa fa-copy"></i> Copy'
            },
            {
                extend: 'colvis',
                text: '<i class="fa fa-columns"></i> Columns'
            },
            {
                extend: 'excel',
                text: '<i class="fa fa-file-excel"></i> Excel'
            },
            {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf"></i> PDF'
            }
        ]
    });

    //  * AJAX FORMS (Create / Edit)
    
    // handleAjaxForm("#timesheetForm", {
    //     loadingIndicator: 'button',
    //     modalToClose: "#genericModal",
    //     reloadOnSuccess: true
    // });

    // handleAjaxForm("#timesheetFormEdit", {
    //     loadingIndicator: 'button',
    //     successTitle: "Timesheet Updated!",
    //     reloadOnSuccess: true
    // });
});

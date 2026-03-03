document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('user-calendar');
    if (!calendarEl) return;

    const userId = calendarEl.dataset.user;
    if (!userId) {
        console.error('[user-dashboard] data-user attribute missing');
        return;
    }

    //  * INITIALIZE CALENDAR

    const calendar = new FullCalendar.Calendar(calendarEl, {

        /* ---------- BASIC CONFIG ---------- */
        initialView: 'dayGridMonth',

        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },

        height: 'auto',
        nowIndicator: true,
        selectable: false,
        editable: false,

        /* ---------- EVENT SOURCE ---------- */
        events: `/master-app/users/${userId}/timesheets/calendar`,

        /* ---------- EVENT CLICK (edit modal – commented out) ---------- */
        // As client feedback - we don't want to edit timesheets from the calendar
        // eventClick(info) {
        //     info.jsEvent.preventDefault();
        //
        //     const event = info.event;
        //     const timesheetId =
        //         event.extendedProps?.timesheet_id ?? event.id;
        //
        //     if (!timesheetId) {
        //         console.error('[calendar] Timesheet ID missing', event);
        //         return;
        //     }
        //
        //     openTimesheetModal(timesheetId);
        // },

        /* ---------- TIME FORMAT ---------- */
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },
          // As client feedback - we don't want to edit timesheets from the calendar
        /* ---------- TOOLTIP ---------- */
        // eventDidMount(info) {
        //     if (info.event.title) {
        //         info.el.setAttribute('title', info.event.title);
        //         info.el.style.cursor = 'pointer';
        //     }
        // }
    });

    calendar.render();

    /* ---------- EXPOSE FOR REFRESH ---------- */
    window.userCalendar = calendar;
});

  // As client feedback - we don't want to edit timesheets from the calendar
//  * OPEN TIMESHEET MODAL (AJAX)
// function openTimesheetModal(timesheetId) {

//     const modalEl = document.getElementById('genericModal');
//     if (!modalEl) {
//         console.error('[modal] genericModal not found');
//         return;
//     }

//     const modalTitle = modalEl.querySelector('.modal-title');
//     const modalBody  = modalEl.querySelector('.modal-body');

//     modalTitle.textContent = 'Edit Timesheet Entry';
//     modalBody.innerHTML =
//         '<div class="text-center p-4 text-muted">Loading…</div>';

//     const modal = new bootstrap.Modal(modalEl);
//     modal.show();

//     fetch(`/master-app/timesheets/${timesheetId}`)
//         .then(res => res.text())
//         .then(html => {
//             modalBody.innerHTML = html;
//         })
//         .catch(() => {
//             modalBody.innerHTML =
//                 '<div class="text-danger">Failed to load timesheet</div>';
//         });
// }

// //  * AJAX SUBMIT – EDIT TIMESHEET
// $(document).on('submit', '#timesheetEditForm', function (e) {
//     e.preventDefault();

//     const form = $(this);

//     $.ajax({
//         url: form.attr('action'),
//         method: 'POST', // PATCH via _method
//         data: form.serialize(),

//         success(res) {
//             if (!res?.success) return;

//             Swal.fire({
//                 icon: 'success',
//                 title: 'Updated',
//                 text: res.message,
//                 timer: 1500,
//                 showConfirmButton: false
//             });

//             $('#genericModal').modal('hide');

//             if (window.userCalendar) {
//                 window.userCalendar.refetchEvents();
//             }
//         },

//         error(xhr) {
//             Swal.fire({
//                 icon: 'error',
//                 title: 'Update Failed',
//                 text: xhr.responseJSON?.message || 'Something went wrong'
//             });
//         }
//     });
// });

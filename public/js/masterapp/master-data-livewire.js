/**
 * Shared JS for Livewire master data pages (Organization Type, Seasons, etc.)
 * - DataTable init for main and optional child tables
 * - SweetAlert delete confirmation
 * - Livewire toast listeners (deleteResult, statusUpdated, formResult)
 *
 * Usage in Blade:
 * - Main table: add class "js-master-datatable", optional data-order-col="2", data-non-orderable-targets="3,4"
 * - Child table: add class "js-master-child-datatable"
 * - Delete link: add class "master-delete-link", data-master-delete-id="{{ $item->id }}", data-master-delete-title="Delete X?"
 */

(function () {
    'use strict';

    function initMasterTable($table) {
        if (!window.jQuery || !$.fn || !$.fn.DataTable) return;
        if (!$table.length) return;
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().destroy();
        }
        var orderCol = parseInt($table.data('order-col'), 10);
        if (isNaN(orderCol)) orderCol = 1;
        var nonOrderable = $table.data('non-orderable-targets');
        var targets = nonOrderable ? (typeof nonOrderable === 'string' ? nonOrderable.split(',').map(function (n) { return parseInt(n.trim(), 10); }) : nonOrderable) : [];
        if (!targets.length) {
            var colCount = $table.find('thead th').length;
            targets = [colCount - 2, colCount - 1];
        }
        $table.DataTable({
            pageLength: 10,
            responsive: true,
            scrollX: false,
            autoWidth: false,
            lengthMenu: [[-1, 10, 50, 100], ['All', 10, 50, 100]],
            language: {
                lengthMenu: 'Show _MENU_',
                paginate: {
                    next: '<i class="fa fa-angle-double-right"></i>',
                    previous: '<i class="fa fa-angle-double-left"></i>'
                },
                search: ''
            },
            order: [[orderCol, 'desc']],
            columnDefs: [{ orderable: false, targets: targets }],
            fixedColumns: { rightColumns: 1 },
            initComplete: function () {
                var $wrapper = $table.closest('.dataTables_wrapper');
                var $topContainer = $wrapper.find('.top');
                $topContainer.addClass('master-table-top');
                $wrapper.find('.dataTables_length').appendTo($topContainer);
                $wrapper.find('.dataTables_length').addClass('ml-2 master-table-length');
                $wrapper.find('.dataTables_length').parent().addClass('master-table-length-col d-flex');
                $wrapper.find('.top .dataTables_length, .top .dataTables_paginate').wrapAll('<div class="length_pagination"></div>');
                $wrapper.find('.top .dataTables_info, .top .length_pagination').wrapAll('<div class="show_page_align"></div>');
                $wrapper.find('.top .dt-buttons, .top .dataTables_filter').wrapAll('<div class="btn_filter_align"></div>');
                var $searchInput = $wrapper.find('.dataTables_filter input');
                $searchInput.attr('placeholder', 'Search..');
                $searchInput.wrap('<div class="search-input-wrapper"></div>');
                $searchInput.addClass('search-input');
                $searchInput.before('<i class="fa fa-search"></i>');
            }
        });
    }

    function initChildTable($table) {
        if (!window.jQuery || !$.fn || !$.fn.DataTable) return;
        if (!$table.length) return;
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().destroy();
        }
        $table.DataTable({
            pageLength: 10,
            responsive: true,
            scrollX: false,
            autoWidth: false,
            searching: false,
            lengthChange: false,
            dom: 'rtip',
            language: {
                paginate: {
                    next: '<i class="fa fa-angle-double-right"></i>',
                    previous: '<i class="fa fa-angle-double-left"></i>'
                }
            },
            order: [[1, 'desc']],
            columnDefs: [{ orderable: false, targets: [2, 3] }],
            initComplete: function () {
                var $wrapper = $table.closest('.dataTables_wrapper');
                $wrapper.find('.dataTables_info, .dataTables_paginate').wrapAll('<div class="show_page_align d-flex align-items-center"></div>');
            }
        });
    }

    function initAllTables() {
        if (!window.jQuery) return;
        $('.js-master-datatable').each(function () {
            initMasterTable($(this));
        });
        $('.js-master-child-datatable').each(function () {
            initChildTable($(this));
        });
    }

    function scheduleTableInit() {
        setTimeout(initAllTables, 0);
    }

    document.addEventListener('DOMContentLoaded', initAllTables);
    document.addEventListener('livewire:load', initAllTables);
    document.addEventListener('livewire:navigated', scheduleTableInit);
    window.addEventListener('master-table:datatable', scheduleTableInit);

    document.addEventListener('livewire:init', function () {
        initAllTables();
        if (window.Livewire && Livewire.hook) {
            Livewire.hook('commit', function (_ref) {
                var succeed = _ref.succeed;
                succeed(scheduleTableInit);
            });
            Livewire.hook('message.processed', scheduleTableInit);
        }
    });

    // SweetAlert delete confirmation for any master delete link
    document.addEventListener('click', function (e) {
        var link = e.target.closest('.master-delete-link');
        if (!link) return;
        e.preventDefault();
        var id = parseInt(link.getAttribute('data-master-delete-id'), 10);
        if (isNaN(id)) return;
        var root = link.closest('[wire\\:id]');
        if (!root || typeof Livewire === 'undefined') return;
        var wireId = root.getAttribute('wire:id');
        if (!wireId) return;
        var component = Livewire.find(wireId);
        if (!component) return;
        if (typeof Swal === 'undefined') return;
        var title = link.getAttribute('data-master-delete-title') || 'Delete?';
        Swal.fire({
            title: title,
            text: 'This will soft delete the record and remove it from dropdowns.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it'
        }).then(function (result) {
            if (result.isConfirmed) {
                component.call('deleteById', id);
            }
        });
    });

    document.addEventListener('livewire:initialized', function () {
        if (typeof Swal === 'undefined') return;
        var toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        Livewire.on('deleteResult', function (e) {
            var success = e && e.success;
            var message = (e && e.message) || '';
            if (success) {
                toast.fire({ icon: 'success', title: 'Deleted', text: message });
            } else {
                toast.fire({ icon: 'warning', title: 'Cannot delete', text: message });
            }
        });
        Livewire.on('statusUpdated', function (e) {
            var message = (e && e.message) || 'Status updated';
            var title = (e && e.active) ? 'Active' : 'Inactive';
            toast.fire({ icon: 'success', title: title, text: message });
        });
        Livewire.on('formResult', function (e) {
            var type = (e && e.type) || 'success';
            var message = (e && e.message) || '';
            var title = type === 'success' ? 'Success' : (type === 'warning' ? 'Warning' : 'Error');
            var icon = type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'error');
            toast.fire({ icon: icon, title: title, text: message });
        });
    });
})();

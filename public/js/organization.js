/**
 * Common JavaScript for organization create and edit forms.
 * Config can be passed either by:
 * 1. OrganizationForm.init({ ... }) from your script, or
 * 2. A <script type="application/json" id="organization-form-config">...</script> in the page
 *    (organization.js will auto-init on DOM ready when that element exists).
 */
(function () {
    'use strict';

    function getToast() {
        if (typeof Swal === 'undefined' || !Swal.mixin) return null;
        return Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            showClass: { popup: 'animate__animated animate__fadeInUp' },
            hideClass: { popup: 'animate__animated animate__fadeOutDown' }
        });
    }

    function showToast(icon, title, text) {
        var toast = getToast();
        if (toast) toast.fire({ icon: icon, title: title || '', text: text || '' });
    }

    function initDescriptionEditor() {
        var $el = $('#description_editor');
        if (!$el.length || typeof $().summernote !== 'function') return null;
        $el.summernote({
            height: 220,
            placeholder: 'Write organization description...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['codeview', 'fullscreen']]
            ]
        });
        return $el;
    }

    function initLogoHandlers(mode) {
        var $logo = $('#logo');
        var $wrap = $('#logo-preview-wrap');
        if (!$logo.length || !$wrap.length) return;

        var isEdit = mode === 'edit';
        var currentLogoUrl = isEdit ? ($wrap.data('current-src') || '') : '';

        function renderCurrentLogo() {
            if (!currentLogoUrl) return;
            $wrap.html(
                '<div class="position-relative d-inline-block">' +
                '<img src="' + currentLogoUrl.replace(/"/g, '&quot;') + '" alt="Logo" style="max-height: 120px; max-width: 200px; object-fit: contain; border: 1px solid #ced4da; border-radius: 0.375rem;">' +
                '<button type="button" class="btn btn-secondary position-absolute rounded-circle p-0 border-0 logo-remove-btn" style="width: 22px; height: 22px; line-height: 1; font-size: 12px; z-index: 2; cursor: pointer; top: 4px; right: 4px; opacity: 0.9;" title="Remove logo" aria-label="Remove logo"><i class="fa fa-times" aria-hidden="true"></i></button>' +
                '</div>'
            );
        }

        if (isEdit && currentLogoUrl && $('#logo_remove').val() !== '1') {
            renderCurrentLogo();
        }

        function fireError(title) {
            var t = getToast();
            if (t) t.fire({ icon: 'error', title: title });
        }

        $logo.on('change', function () {
            var input = this;
            var $label = $(this).next('.custom-file-label');
            if (!input.files || !input.files[0]) {
                $label.html('Choose file');
                if (isEdit && currentLogoUrl && $('#logo_remove').val() !== '1') renderCurrentLogo();
                else $wrap.empty();
                return;
            }
            var file = input.files[0];
            var validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (validTypes.indexOf(file.type) === -1) {
                fireError('Please select an image (JPEG, PNG, GIF or WebP).');
                input.value = '';
                $label.html('Choose file');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                fireError('Image must be 2MB or smaller.');
                input.value = '';
                $label.html('Choose file');
                return;
            }
            $label.html(file.name);
            var reader = new FileReader();
            reader.onload = function (e) {
                var src = e.target.result;
                var previewHtml = '<div class="position-relative d-inline-block">' +
                    '<img alt="Logo preview" style="max-height: 120px; max-width: 200px; object-fit: contain; border: 1px solid #ced4da; border-radius: 0.375rem;">' +
                    '<button type="button" class="btn btn-secondary position-absolute rounded-circle p-0 border-0 logo-preview-remove" style="width: 22px; height: 22px; line-height: 1; font-size: 12px; z-index: 2; cursor: pointer; top: 4px; right: 4px; opacity: 0.9;" title="Remove" aria-label="Remove"><i class="fa fa-times" aria-hidden="true"></i></button>' +
                    '</div>';
                $wrap.html(previewHtml);
                $wrap.find('img').attr('src', src);
            };
            reader.readAsDataURL(file);
        });

        $(document).on('click', '.logo-preview-remove', function () {
            $('#logo').val('');
            $('#logo').next('.custom-file-label').html('Choose file');
            if (isEdit && currentLogoUrl && $('#logo_remove').val() !== '1') renderCurrentLogo();
            else $wrap.empty();
        });

        if (isEdit) {
            $(document).on('click', '.logo-remove-btn', function (e) {
                e.preventDefault();
                e.stopPropagation();
                function doRemove() {
                    $('#logo_remove').val('1');
                    $('#logo').val('');
                    $wrap.empty();
                }
                if (typeof Swal === 'undefined') {
                    if (confirm('Remove current logo? The logo will be removed when you save.')) doRemove();
                    return;
                }
                Swal.fire({
                    title: 'Remove logo?',
                    text: 'The current logo will be removed when you save.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it'
                }).then(function (result) {
                    if (result && result.isConfirmed) doRemove();
                });
            });
        }
    }

    function initFormSubmit(config) {
        var mode = config.mode || 'edit';
        if (mode === 'create') {
            $('#organizationForm').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-create-organization');
                var $desc = $('#description_editor');
                if ($desc.length && $desc.data('summernote')) $desc.val($desc.summernote('code'));
                $btn.prop('disabled', true);
                $('#btn-create-text').addClass('d-none');
                $('#btn-create-spinner').removeClass('d-none');
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function (res) {
                        window.location.href = (config.redirectUrl || '') + '?created=1&message=' + encodeURIComponent(res.message || 'Organization created successfully.');
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        $('#btn-create-text').removeClass('d-none');
                        $('#btn-create-spinner').addClass('d-none');
                        var toast = getToast();
                        if (!toast) return;
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            var msg = '';
                            $.each(xhr.responseJSON.errors, function (field, arr) {
                                msg += arr.join('<br>') + '<br>';
                            });
                            toast.fire({ icon: 'error', title: 'Validation Error', html: msg });
                        } else {
                            toast.fire({ icon: 'error', title: (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong' });
                        }
                    }
                });
            });
            return;
        }
        $('form').on('submit', function () {
            var $desc = $('#description_editor');
            if ($desc.length && $desc.data('summernote')) $desc.val($desc.summernote('code'));
            $('#btn-update-organization').prop('disabled', true);
            $('#btn-update-text').addClass('d-none');
            $('#btn-update-spinner').removeClass('d-none');
        });
    }

    function formatUSPhone(val) {
        var digits = (val || '').replace(/\D/g, '').slice(0, 10);
        if (digits.length <= 3) return digits;
        if (digits.length <= 6) return digits.slice(0, 3) + '-' + digits.slice(3);
        return digits.slice(0, 3) + '-' + digits.slice(3, 6) + '-' + digits.slice(6);
    }

    function bindLocationSearch(suggestUrl, config) {
        var request = null;

        function hideSuggestions() {
            $(config.suggestionsId).addClass('d-none').empty();
        }

        function setLoading(isLoading) {
            $(config.loadingId).toggleClass('d-none', !isLoading);
        }

        function showSelectedLocationDetail(item) {
            var fullAddress = [item.address, item.city, item.state, item.postal_code, item.country]
                .filter(Boolean)
                .join(', ');
            $(config.detailNameId).text(item.name || '-');
            $(config.detailAddressId).text(fullAddress || '-');
            $(config.detailWrapId).removeClass('d-none');
        }

        function hideSelectedLocationDetail() {
            $(config.detailNameId).text('');
            $(config.detailAddressId).text('');
            $(config.detailWrapId).addClass('d-none');
        }

        $(config.inputId).on('input', function () {
            var query = $(this).val().trim();
            $(config.hiddenId).val('');
            $(config.inputId).removeData('selectedLocation');
            hideSelectedLocationDetail();
            if (typeof config.onInput === 'function') {
                config.onInput();
            }
            if (query.length < 2) {
                hideSuggestions();
                setLoading(false);
                return;
            }
            if (request) {
                request.abort();
            }
            setLoading(true);
            request = $.ajax({
                url: suggestUrl,
                type: 'GET',
                dataType: 'json',
                data: { q: query },
                success: function (res) {
                    var items = res.data || [];
                    var $box = $(config.suggestionsId);
                    $box.empty();
                    if (!items.length) {
                        hideSuggestions();
                        return;
                    }
                    items.forEach(function (item) {
                        var $btn = $('<button type="button" class="list-group-item list-group-item-action"></button>');
                        $btn.text(item.display || item.name || '');
                        $btn.data('item', item);
                        $btn.addClass(config.optionClass);
                        $box.append($btn);
                    });
                    $box.removeClass('d-none');
                },
                error: function () {
                    hideSuggestions();
                },
                complete: function () {
                    setLoading(false);
                }
            });
        });

        $(document).on('click', '.' + config.optionClass, function () {
            var item = $(this).data('item');
            if (!item) return;
            $(config.inputId).val(item.address || item.display || item.name || '');
            $(config.hiddenId).val(item.id || '');
            $(config.inputId).data('selectedLocation', item);
            showSelectedLocationDetail(item);
            hideSuggestions();
            if (typeof config.onSelected === 'function') {
                config.onSelected(item);
            }
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest(config.inputId + ', ' + config.suggestionsId).length) {
                hideSuggestions();
            }
        });
    }

    function bindContactSearch(contactSuggestUrl) {
        var request = null;

        function hideSuggestions() {
            $('#contact_search_suggestions').addClass('d-none').empty();
        }

        function setLoading(isLoading) {
            $('#contact_search_loading').toggleClass('d-none', !isLoading);
        }

        function getSelectedContactIds() {
            return $('#selected_contacts_list input[name="contact_ids[]"]').map(function () {
                return $(this).val();
            }).get();
        }

        function addContact(item) {
            var ids = getSelectedContactIds();
            if (ids.indexOf(String(item.contact_id)) !== -1) return;
            var parts = [];
            if (item.items && item.items.length) {
                item.items.forEach(function (i) {
                    var typeLabel = (i.type || '').charAt(0).toUpperCase() + (i.type || '').slice(1);
                    if (i.value) parts.push(typeLabel + ': ' + i.value);
                });
            }
            var $row = $('<div class="selected-contact-item alert alert-light border py-2 px-3 mb-2 position-relative d-flex align-items-start" data-contact-id="' + item.contact_id + '">' +
                '<div class="flex-grow-1">' +
                '<div class="selected-contact-name font-weight-bold">' + (item.contact_name || '') + '</div>' +
                '<div class="selected-contact-details small text-muted">' + (parts.join(' · ') || '') + '</div>' +
                '</div>' +
                '<button type="button" class="btn btn-link p-0 ml-2 text-secondary js-remove-contact" style="font-size: 1.25rem; line-height: 1;" title="Remove" aria-label="Remove">&times;</button>' +
                '<input type="hidden" name="contact_ids[]" value="' + item.contact_id + '">' +
                '</div>');
            $('#selected_contacts_list').append($row);
        }

        $('#contact_search').on('input', function () {
            var query = $(this).val().trim();
            if (query.length < 2) {
                hideSuggestions();
                setLoading(false);
                return;
            }
            if (request) request.abort();
            setLoading(true);
            request = $.ajax({
                url: contactSuggestUrl,
                type: 'GET',
                dataType: 'json',
                data: { q: query },
                success: function (res) {
                    var items = res.data || [];
                    var $box = $('#contact_search_suggestions');
                    $box.empty();
                    if (!items.length) {
                        hideSuggestions();
                        return;
                    }
                    items.forEach(function (item) {
                        var $btn = $('<button type="button" class="list-group-item list-group-item-action js-contact-suggest-option"></button>');
                        $btn.text(item.display || item.contact_name || '');
                        $btn.data('item', item);
                        $box.append($btn);
                    });
                    $box.removeClass('d-none');
                },
                error: function () {
                    hideSuggestions();
                },
                complete: function () {
                    setLoading(false);
                }
            });
        });

        $(document).on('click', '.js-contact-suggest-option', function () {
            var item = $(this).data('item');
            if (item) {
                addContact(item);
                $('#contact_search').val('');
                hideSuggestions();
            }
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#contact_search, #contact_search_suggestions').length) {
                hideSuggestions();
            }
        });
    }

    function applyMailingSameAsPhysical(isChecked) {
        var $mailingAddress = $('#mailing_address_search');
        var $mailingLocationId = $('#mailing_location_id');
        var $mailingDetail = $('#selected_mailing_location_detail');
        var $mailingName = $('#selected_mailing_location_name');
        var $mailingFullAddress = $('#selected_mailing_location_full_address');

        if (isChecked) {
            var physicalAddress = $('#physical_address_search').val() || '';
            var physicalLocationId = $('#physical_location_id').val() || '';
            var physicalItem = $('#physical_address_search').data('selectedLocation');

            $mailingAddress.val(physicalAddress).prop('readonly', true);
            $mailingLocationId.val(physicalLocationId);
            $('#mailing_location_suggestions').addClass('d-none').empty();
            $('#mailing_location_search_loading').addClass('d-none');

            if (physicalItem) {
                var fullAddress = [physicalItem.address, physicalItem.city, physicalItem.state, physicalItem.postal_code, physicalItem.country]
                    .filter(Boolean)
                    .join(', ');
                $mailingName.text(physicalItem.name || '-');
                $mailingFullAddress.text(fullAddress || '-');
                $mailingDetail.removeClass('d-none');
            } else if (physicalAddress) {
                $mailingName.text('Physical Address');
                $mailingFullAddress.text(physicalAddress);
                $mailingDetail.removeClass('d-none');
            } else {
                $mailingName.text('');
                $mailingFullAddress.text('');
                $mailingDetail.addClass('d-none');
            }
            return;
        }
        $mailingAddress.prop('readonly', false);
    }

    window.OrganizationForm = {
        init: function (config) {
            var suggestUrl = config.suggestUrl;
            var contactSuggestUrl = config.contactSuggestUrl;
            var mode = config.mode || 'edit';

            if (config.errorToast && config.errorToast.show) {
                showToast('error', 'Please fix the errors below.', config.errorToast.message || '');
            }

            initDescriptionEditor();
            initLogoHandlers(mode);
            initFormSubmit(config);

            $(document).on('click', '.org-section-btn', function () {
                var targetId = $(this).data('target');
                $('.org-section-btn').removeClass('active');
                $(this).addClass('active');
                $('.org-section-panel').addClass('d-none');
                $('#' + targetId).removeClass('d-none');
            });

            $(document).on('input', '.js-us-phone', function () {
                var $el = $(this);
                var pos = $el[0].selectionStart;
                var oldVal = $el.val();
                var formatted = formatUSPhone(oldVal);
                $el.val(formatted);
                var newLen = formatted.length;
                if (pos !== undefined && pos <= newLen) {
                    $el[0].setSelectionRange(Math.min(pos, newLen), Math.min(pos, newLen));
                }
            });
            $('.js-us-phone').each(function () {
                var v = $(this).val();
                if (v) $(this).val(formatUSPhone(v));
            });

            bindLocationSearch(suggestUrl, {
                inputId: '#physical_address_search',
                hiddenId: '#physical_location_id',
                loadingId: '#physical_location_search_loading',
                suggestionsId: '#physical_location_suggestions',
                detailWrapId: '#selected_physical_location_detail',
                detailNameId: '#selected_physical_location_name',
                detailAddressId: '#selected_physical_location_full_address',
                optionClass: 'js-physical-location-option',
                onInput: function () {
                    if ($('#mailing_same_as_physical').is(':checked')) {
                        applyMailingSameAsPhysical(true);
                    }
                },
                onSelected: function () {
                    if ($('#mailing_same_as_physical').is(':checked')) {
                        applyMailingSameAsPhysical(true);
                    }
                }
            });

            $(document).on('click', '.js-clear-physical-location', function () {
                $('#physical_location_id').val('');
                $('#physical_address_search').val('').removeData('selectedLocation');
                $('#selected_physical_location_detail').addClass('d-none');
                $('#selected_physical_location_name').text('');
                $('#selected_physical_location_full_address').text('');
            });

            bindLocationSearch(suggestUrl, {
                inputId: '#mailing_address_search',
                hiddenId: '#mailing_location_id',
                loadingId: '#mailing_location_search_loading',
                suggestionsId: '#mailing_location_suggestions',
                detailWrapId: '#selected_mailing_location_detail',
                detailNameId: '#selected_mailing_location_name',
                detailAddressId: '#selected_mailing_location_full_address',
                optionClass: 'js-mailing-location-option'
            });

            $(document).on('click', '.js-clear-mailing-location', function () {
                $('#mailing_location_id').val('');
                $('#mailing_address_search').val('').removeData('selectedLocation');
                $('#selected_mailing_location_detail').addClass('d-none');
                $('#selected_mailing_location_name').text('');
                $('#selected_mailing_location_full_address').text('');
            });

            bindContactSearch(contactSuggestUrl);

            $(document).on('click', '.js-remove-contact', function () {
                $(this).closest('.selected-contact-item').remove();
            });

            $('#mailing_same_as_physical').on('change', function () {
                applyMailingSameAsPhysical($(this).is(':checked'));
            });

            if ($('#mailing_same_as_physical').is(':checked')) {
                applyMailingSameAsPhysical(true);
            }

            $('.types-parent-btn').on('click', function () {
                $('.types-parent-btn').removeClass('active');
                $(this).addClass('active');
                var id = parseInt($(this).data('id'), 10);
                $('.types-panel').addClass('d-none');
                $('#types_panel_' + id).removeClass('d-none');
            });
            var $firstBtn = $('.types-parent-btn').first();
            if ($firstBtn.length) {
                $firstBtn.trigger('click');
            }

            $(document).on('change', '.js-enable-parent-type', function () {
                var parentId = $(this).data('parent-id');
                var isChecked = $(this).prop('checked');
                var $btn = $('.types-parent-btn[data-id="' + parentId + '"]');
                var $tick = $btn.find('.types-parent-tick');
                $tick.toggleClass('text-success', isChecked).toggleClass('text-muted', !isChecked);
            });
        }
    };

    /**
     * Organization listing (index) page.
     * Call after CRUDManager.init(). Usage: OrganizationIndex.init({ successMessage: null });
     */
    window.OrganizationIndex = {
        init: function (config) {
            config = config || {};
            var Toast = typeof Swal !== 'undefined' && Swal.mixin ? Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                showClass: { popup: 'animate__animated animate__fadeInUp' },
                hideClass: { popup: 'animate__animated animate__fadeOutDown' }
            }) : null;

            if (Toast && config.successMessage) {
                Toast.fire({ icon: 'success', title: config.successMessage });
            }

            var params = new URLSearchParams(window.location.search);
            if (params.get('created') === '1') {
                if (Toast) {
                    Toast.fire({
                        icon: 'success',
                        title: params.get('message') || 'Organization created successfully.'
                    });
                }
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            if (params.has('active')) {
                var activeValue = params.get('active');
                $('#filter_active_ui').val(activeValue);
                $('#filter_active').val(activeValue);
            }

            function updateUrlWithFilters() {
                var query = new URLSearchParams(window.location.search);
                var active = $('#filter_active').val();
                query.delete('active');
                if (active !== '') query.set('active', active);
                var newUrl = window.location.pathname + (query.toString() ? '?' + query.toString() : '');
                history.replaceState(null, '', newUrl);
            }

            $('#toggleFilterBtn').on('click', function () {
                $('#filterWrapper').slideToggle();
            });
            $('#toggleFilterclear').on('click', function (e) {
                e.preventDefault();
                $('#filterWrapper').slideToggle();
            });
            $('#applyFilterBtn').on('click', function () {
                $('#filter_active').val($('#filter_active_ui').val());
                if ($.fn.DataTable.isDataTable('#dataTable')) {
                    $('#dataTable').DataTable().page(0).draw(false);
                }
                updateUrlWithFilters();
            });
            $('#clearFilterBtn').on('click', function () {
                $('#filter_active_ui').val('');
                $('#filter_active').val('');
                if ($.fn.DataTable.isDataTable('#dataTable')) {
                    $('#dataTable').DataTable().page(0).draw(false);
                }
                updateUrlWithFilters();
            });

            setTimeout(function () {
                var $top = $('#dataTable_wrapper .top');
                var $buttons = $top.find('.dt-buttons');
                var $searchWrap = $('#organization-search-wrap');
                if ($buttons.length && $searchWrap.length) {
                    var $row = $('<div class="btn_filter_align d-flex align-items-center flex-wrap gap-2 w-100"></div>');
                    $row.append($buttons);
                    $row.append($searchWrap);
                    $searchWrap.removeClass('mb-3').addClass('mb-0 ml-auto');
                    $top.prepend($row);
                }
            }, 0);

            $('#customSearchInput').on('search', function () {
                if ($.fn.DataTable.isDataTable('#dataTable')) {
                    $('#dataTable').DataTable().draw();
                }
            });

            function showToast(icon, title) {
                if (Toast) Toast.fire({ icon: icon, title: title || '' });
            }

            $(document).off('change', '.org-active-toggle');
            $(document).on('change', '.org-active-toggle', function () {
                var $input = $(this);
                var url = $input.data('url');
                var active = $input.prop('checked') ? 1 : 0;
                var originalChecked = $input.prop('checked');
                $input.prop('disabled', true);
                $.ajax({
                    url: url,
                    type: 'PATCH',
                    dataType: 'json',
                    data: { _token: $('meta[name="csrf-token"]').attr('content'), active: active },
                    success: function (res) {
                        showToast('success', (res && res.message) ? res.message : 'Active status updated.');
                    },
                    error: function (xhr) {
                        $input.prop('checked', !originalChecked);
                        showToast('error', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update active status.');
                    },
                    complete: function () { $input.prop('disabled', false); }
                });
            });

            if (typeof handleDelete === 'function') {
                handleDelete({ reloadTable: '#dataTable' });
            }
        }
    };

    /**
     * Auto-initialize organization form when #organization-form-config is present (Blade passes config as JSON).
     */
    function tryInitOrganizationForm() {
        var $configEl = $('#organization-form-config');
        if (!$configEl.length) return;
        try {
            var raw = $configEl.html();
            var config = (typeof raw === 'string' && raw) ? JSON.parse(raw) : {};
            if (config.suggestUrl && config.contactSuggestUrl && typeof OrganizationForm !== 'undefined') {
                OrganizationForm.init(config);
                return true;
            }
        } catch (e) {
            console.error('Organization form config invalid:', e);
        }
        return false;
    }

    $(function () {
        if (tryInitOrganizationForm()) return;
        if (typeof $().summernote !== 'function') {
            setTimeout(function () { tryInitOrganizationForm(); }, 150);
        }
    });
})();

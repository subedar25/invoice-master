/**
 * Handles AJAX form submission with generic success/error handling and loading indicators.
 *
 * @param {string} formSelector The jQuery selector for the form (e.g., "#form-modules").
 * @param {object} options Configuration options for the form submission.
 * @param {string} [options.loadingIndicator='button'] The type of loading indicator. Can be 'button' or 'content'.
 * @param {string} [options.buttonTextSelector=null] Selector for the button's text element (e.g., "#btn-text"). Required for 'button' loader.
 * @param {string} [options.buttonSpinnerSelector=null] Selector for the button's spinner element (e.g., "#btn-spinner"). Required for 'button' loader.
 * @param {string} [options.formContentSelector=null] Selector for the container to be replaced by the loader (e.g., ".card-body"). Required for 'content' loader.
 * @param {string} [options.loadingText='Loading...'] The text to display with the spinner.
 * @param {function} [options.onSuccess=null] A callback function to run on a successful request.
 * @param {function} [options.onError=null] A callback function to run on a failed request.
 * @param {string} [options.modalToClose=null] A jQuery selector for a modal to close on success.
 * @param {boolean} [options.closeModalOnSuccess=true] If false, keep the modal open on success.
 * @param {boolean} [options.reloadOnSuccess=false] If true, the page will reload on success.
 * @param {string} [options.successTitle="Success"] The title for the success Swal.
 * @param {string} [options.errorTitle="Error"] The title for the error Swal.
 */
function handleAjaxForm(formSelector, options = {}) {
    const settings = {
        loadingText: 'Loading...',
        loadingIndicator: 'button',
        buttonTextSelector: null,
        buttonSpinnerSelector: null,
        formContentSelector: null,
        onSuccess: null,
        onError: null,
        modalToClose: null,
        closeModalOnSuccess: true,
        reloadOnSuccess: false,
        successTitle: "Success",
        errorTitle: "Error",
        ...options
    };

    const showLoader = () => {
        const form = $(formSelector);
        const submitButton = form.find('button[type="submit"]');

        if (settings.loadingIndicator === 'button' && settings.buttonTextSelector && settings.buttonSpinnerSelector) {
            submitButton.prop('disabled', true);
            $(settings.buttonTextSelector).addClass('d-none');
            $(settings.buttonSpinnerSelector).removeClass('d-none');
        } else if (settings.loadingIndicator === 'content' && settings.formContentSelector) {
            const contentContainer = form.find(settings.formContentSelector);
            // Store original content to restore it on error
            contentContainer.data('original-content', contentContainer.html());
            contentContainer.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 fw-semibold">${settings.loadingText}</p>
                </div>
            `);
        }
    };

    const hideLoader = () => {
        const form = $(formSelector);
        const submitButton = form.find('button[type="submit"]');

        if (settings.loadingIndicator === 'button' && settings.buttonTextSelector && settings.buttonSpinnerSelector) {
            submitButton.prop('disabled', false);
            $(settings.buttonTextSelector).removeClass('d-none');
            $(settings.buttonSpinnerSelector).addClass('d-none');
        } else if (settings.loadingIndicator === 'content' && settings.formContentSelector) {
            const contentContainer = form.find(settings.formContentSelector);
            const originalContent = contentContainer.data('original-content');
            if (originalContent) {
                contentContainer.html(originalContent);
            }
        }
    };

    $(document).on("submit", formSelector, function (e) {
        e.preventDefault();
        showLoader();

        const form = $(this);
        const formData = new FormData(this);

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method') || 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                hideLoader();
                if (typeof settings.onSuccess === 'function') {
                    settings.onSuccess(res);
                }
                const closeModal = () => {
                    if (!settings.modalToClose || !settings.closeModalOnSuccess) return;
                    const modalEl = document.querySelector(settings.modalToClose);
                    if (!modalEl) return;

                    const forceClose = () => {
                        modalEl.classList.remove('show');
                        modalEl.setAttribute('aria-hidden', 'true');
                        modalEl.style.display = 'none';
                        modalEl.removeAttribute('aria-modal');
                        document.body.classList.remove('modal-open');
                        document.body.style.paddingRight = '';
                        document.querySelectorAll('.modal-backdrop').forEach((bd) => bd.remove());
                    };

                    // 1) Bootstrap 4 jQuery plugin path
                    try {
                        if (typeof $ !== 'undefined' && $.fn && typeof $.fn.modal === 'function') {
                            $(modalEl).modal('hide');
                        }
                    } catch (_) {}

                    // 2) Bootstrap 5 API fallback
                    try {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            let modal = null;
                            if (typeof bootstrap.Modal.getOrCreateInstance === 'function') {
                                modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            } else if (typeof bootstrap.Modal.getInstance === 'function') {
                                modal = bootstrap.Modal.getInstance(modalEl);
                            }
                            if (modal && typeof modal.hide === 'function') {
                                modal.hide();
                            }
                        }
                    } catch (_) {}

                    // 3) If still open after transition, hard close
                    setTimeout(() => {
                        const isStillVisible = modalEl.classList.contains('show') || modalEl.style.display !== 'none';
                        if (isStillVisible) {
                            forceClose();
                        }
                    }, 250);
                };

                const showToast = () => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: "success",
                        title: settings.successTitle,
                        text: res.message || 'Operation completed successfully.',
                        timer: 5000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                        }
                    }).then(() => {
                        if (settings.reloadOnSuccess) {
                            location.reload();
                        }
                    });
                };
                closeModal();
                showToast();
            },
            error: function (xhr) {
                hideLoader();
                if (typeof settings.onError === 'function') {
                    settings.onError(xhr);
                }
                let errorText = xhr.responseJSON?.message || "An unexpected error occurred.";
                if (xhr.responseJSON?.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorText = errors.join(', ');
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: "error",
                    title: settings.errorTitle,
                    text: errorText,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
    });
});
}

// --- Core Modal Form Manager ---
const ModalFormManager = (function () {
    let modal;
    let config = {};

    const defaultConfig = {
        modalSelector: "#genericModal",
        modalTitleSelector: "#modalTitle",
        modalBodySelector: "#modalBody",
        formSelector: "form[data-modal-form]",
        messages: {
            loadingText: "Loading...",
        },
    };

    const init = function (pageConfig) {
        config = { ...defaultConfig, ...pageConfig };
        bindEvents();
    };

    const bindEvents = function () {
        // Handle form submission within the modal
        $(document).on("submit", config.formSelector, handleFormSubmit);

        // Handle modal close button
        $(document).on("click", `${config.modalSelector} [data-bs-dismiss="modal"]`, function (e) {
            e.preventDefault();
            hideModal();
        });
    };

    const showLoadingSpinner = function () {
    // Use the optional chaining (?.) operator and the nullish coalescing (??) operator
    // to provide a fallback if config.messages or config.messages.loadingText is undefined.
    const loadingText = config?.messages?.loadingText ?? "Loading...";
    
    return `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 fw-semibold">${loadingText}</p>
        </div>
    `;
};

    const showModal = function (title, content) {
        $(config.modalTitleSelector).text(title);
        $(config.modalBodySelector).html(content);
        const modalElement = document.querySelector(config.modalSelector);
        if (!modalElement) return;

        // Bootstrap 4 path (jQuery plugin)
        if (typeof $ !== "undefined" && $.fn && typeof $.fn.modal === "function") {
            $(modalElement).modal("show");
            return;
        }

        // Bootstrap 5 fallback
        if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
            modal = typeof bootstrap.Modal.getOrCreateInstance === "function"
                ? bootstrap.Modal.getOrCreateInstance(modalElement)
                : (typeof bootstrap.Modal.getInstance === "function"
                    ? (bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement))
                    : new bootstrap.Modal(modalElement));
            if (modal && typeof modal.show === "function") {
                modal.show();
            }
        }
    };

    const hideModal = function () {
        const modalElement = document.querySelector(config.modalSelector);
        if (!modalElement) return;

        // Bootstrap 4 path (jQuery plugin)
        if (typeof $ !== "undefined" && $.fn && typeof $.fn.modal === "function") {
            $(modalElement).modal("hide");
            return;
        }

        // Bootstrap 5 fallback
        if (modal && typeof modal.hide === "function") {
            modal.hide();
        }
    };

    // Public function to open the modal with content from a URL
    const openModal = function (url, title) {
        showModal(title, showLoadingSpinner());
        Ajax.get(url, { responseType: "html" })
            .then((html) => {
                // Use a small timeout to ensure the loading spinner is rendered
                setTimeout(() => {
                    $(config.modalBodySelector).html(html);
                   
                     if (typeof checkAllBox === 'function') {
                            checkAllBox();
                        }
                }, 300);
            })
            .catch((error) => {
                console.error("Error loading modal content:", error);
                hideModal(); // Close modal on error
                Notification.error(error.message || "Error loading content. Please try again.");
            });
    };

    const handleFormSubmit = function (e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        // Show loading state on the submit button
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

        Ajax.post(form.action, formData)
            .then((res) => {
                hideModal();
                Notification.success(res.message || "Item saved successfully.");
                // Optional: Reload the page or update a part of it
                setTimeout(() => location.reload(), 1500);
            })
            .catch((error) => {
                // If validation errors are returned, display them in the form
                if (error.status === 422 && error.responseBody && error.responseBody.errors) {
                    // Reload the form with validation errors displayed by Laravel
                    $(config.modalBodySelector).html(error.responseBody.html);
                } else {
                    Notification.error(error.message || "An unexpected error occurred.");
                }
            })
            .finally(() => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
    };

    // Public API
    return {
        init: init,
        openModal: openModal,
    };
})();

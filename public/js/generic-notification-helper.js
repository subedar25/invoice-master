
// --- Notification Helper ---
window.Notification = (function () {
    return {
        success: function (message, title = "Success") {
            Swal.fire({
                icon: "success",
                title,
                text: message,
                timer: 1500,
                showConfirmButton: false,
            });
        },

        error: function (message, title = "Error") {
            Swal.fire({
                icon: "error",
                title,
                text: message,
            });
        },

        confirm: function (message, options = {}) {
            const defaults = {
                title: "Are you sure?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
            };
            return Swal.fire({ ...defaults, ...options, text: message });
        },
    };
})();

// --- AJAX Helper Module ---
window.Ajax = (function () {
    const getCsrfToken = function () {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    };

    const request = function (method, url, data = null, options = {}) {
        const { responseType = "json", ...fetchOptions } = options;
        const config = {
            method,
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                Accept: responseType === "html" ? "text/html" : "application/json",
                ...fetchOptions.headers,
            },
            ...fetchOptions,
        };
        if (data instanceof FormData) {
            config.body = data;
        } else if (data) {
            config.headers["Content-Type"] = "application/json";
            config.body = JSON.stringify(data);
        }
        return fetch(url, config).then((response) => {
            if (!response.ok) {
                return response.json().catch(() => response.text()).then((body) => {
                    const error = new Error(body.message || `Server responded with status: ${response.status}`);
                    error.status = response.status;
                    error.responseBody = body;
                    throw error;
                });
            }
            return responseType === "html" ? response.text() : response.json();
        });
    };

    return {
        get: (url, options) => request("GET", url, null, options),
        post: (url, data, options) => request("POST", url, data, options),
        put: (url, data, options) => request("PUT", url, data, options),
        delete: (url, data, options) =>
            request("DELETE", url, data, options).catch((error) => {
                // Fallback for environments that reject DELETE requests.
                if (error.status !== 405) {
                    throw error;
                }

                const formData = new FormData();
                formData.append("_method", "DELETE");

                if (data && typeof data === "object") {
                    Object.entries(data).forEach(([key, value]) => {
                        if (Array.isArray(value)) {
                            value.forEach((item) => formData.append(`${key}[]`, item));
                        } else if (value !== undefined && value !== null) {
                            formData.append(key, value);
                        }
                    });
                }

                return request("POST", url, formData, options);
            }),
    };
})();

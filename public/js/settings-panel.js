document.addEventListener("DOMContentLoaded", () => {
    const settingsBtn = document.getElementById("settings-btn");
    const settingsPanel = document.getElementById("right-settings-panel");
    const overlay = document.getElementById("panel-overlay");
    const closePanel = document.getElementById("close-settings-panel");

    if (settingsBtn && settingsPanel && overlay && closePanel) {

        settingsBtn.addEventListener("click", function (e) {
            e.preventDefault();
            settingsPanel.classList.add("active");
            overlay.classList.add("active");
        });

        closePanel.addEventListener("click", function () {
            settingsPanel.classList.remove("active");
            overlay.classList.remove("active");
        });

        overlay.addEventListener("click", function () {
            settingsPanel.classList.remove("active");
            overlay.classList.remove("active");
        });
    }
});

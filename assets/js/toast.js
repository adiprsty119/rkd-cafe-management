document.addEventListener("DOMContentLoaded", () => {

    if (window.toastData) {

        window.dispatchEvent(
            new CustomEvent("toast", {
                detail: window.toastData
            })
        );

    }

});
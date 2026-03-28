document.addEventListener("DOMContentLoaded", () => {

    if (!window.toastData) return;

    const fireToast = () => {
        window.dispatchEvent(
            new CustomEvent("toast", {
                detail: window.toastData
            })
        );
    };

    // 🔥 retry sampai Alpine siap
    let attempts = 0;
    const maxAttempts = 10;

    const interval = setInterval(() => {
        if (document.querySelector("[x-data]")) {
            fireToast();
            clearInterval(interval);
        }

        attempts++;
        if (attempts >= maxAttempts) {
            clearInterval(interval);
        }

    }, 50); // cek tiap 50ms
});
document.addEventListener("DOMContentLoaded", () => {

    const tooltip = document.getElementById("global-tooltip");

    if (!tooltip) return;

    document.addEventListener("mouseover", (e) => {

        const el = e.target.closest("[data-tooltip]");
        if (!el) return;

        if (window.innerWidth < 768) return;

        const sidebar = document.querySelector("aside");
        if (!sidebar || !sidebar.classList.contains("w-20")) return;

        tooltip.textContent = el.dataset.tooltip;
        tooltip.classList.remove("hidden");

    });

    document.addEventListener("mousemove", (e) => {

        if (tooltip.classList.contains("hidden")) return;

        tooltip.style.left = (e.pageX + 12) + "px";
        tooltip.style.top = (e.pageY - 10) + "px";

    });

    document.addEventListener("mouseout", (e) => {

        if (!e.target.closest("[data-tooltip]")) return;

        tooltip.classList.add("hidden");

    });

});
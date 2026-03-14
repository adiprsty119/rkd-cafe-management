document.addEventListener("DOMContentLoaded", () => {

    /* =========================
       ELEMENT REFERENCES
    ========================= */
    const navbar = document.getElementById("dashboardNavbar");
    const headerStack = document.getElementById("headerStack");
    const breadcrumb = document.getElementById("breadcrumbContainer");
    const scrollContainer = document.getElementById("dashboardScroll");
    const indicator = document.getElementById("breadcrumbIndicator");

    const header = navbar ? navbar.querySelector("header") : null;

    if (
        !navbar ||
        !header ||
        !headerStack ||
        !breadcrumb ||
        !scrollContainer ||
        !indicator
    ) return;


    /* =========================
       CONFIGURATION
    ========================= */
    const CONFIG = {
        maxMove: 90,
        glassThreshold: 20,
        indicatorThreshold: 50
    };

    let ticking = false;


    /* =========================
       UTILITIES
    ========================= */
    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }


    /* =========================
       SCROLL UPDATE HANDLER
    ========================= */
    function update() {

        const scrollY = scrollContainer.scrollTop;

        /* Breadcrumb Motion */
        const move = clamp(scrollY, 0, CONFIG.maxMove);

        /* Breadcrumb naik */
        breadcrumb.style.transform = `translate3d(0,-${move}px,0)`;

        /* Content ikut naik */
        scrollContainer.style.marginTop = `-${move}px`;

        /* =========================
           BREADCRUMB FADE OUT
        ========================= */
        const progress = move / CONFIG.maxMove;

        if (progress >= 0.85) {
            breadcrumb.style.opacity = "0";
        } else {
            breadcrumb.style.opacity = "1";
        }

        /* Navbar Blur Effect */
        if (scrollY > CONFIG.glassThreshold) {

            navbar.classList.add(
                "backdrop-blur-xl",
                "backdrop-saturate-150"
            );

        } else {

            navbar.classList.remove(
                "backdrop-blur-xl",
                "backdrop-saturate-150"
            );
        }

        /* Header Transparency */
        if (scrollY > CONFIG.glassThreshold) {

            header.classList.remove(
                "bg-white/80",
                "dark:bg-gray-800"
            );

            header.classList.add(
                "bg-white/40",
                "dark:bg-gray-900"
            );

        } else {

            header.classList.remove(
                "bg-white/40",
                "dark:bg-gray-900"
            );

            header.classList.add(
                "bg-white/80",
                "dark:bg-gray-800"
            );
        }

        /* Breadcrumb Indicator */
        if (move >= CONFIG.indicatorThreshold) {

            indicator.classList.remove(
                "opacity-0",
                "translate-y-2",
                "pointer-events-none"
            );

            indicator.classList.add(
                "opacity-100",
                "translate-y-0"
            );

        } else {

            indicator.classList.remove(
                "opacity-100",
                "translate-y-0"
            );

            indicator.classList.add(
                "opacity-0",
                "translate-y-2",
                "pointer-events-none"
            );
        }

        ticking = false;
    }


    /* =========================
       SCROLL LISTENER
    ========================= */
    scrollContainer.addEventListener("scroll", () => {

        if (!ticking) {
            requestAnimationFrame(update);
            ticking = true;
        }

    });


    /* =========================
       INDICATOR CLICK ACTION
    ========================= */
    indicator.addEventListener("click", () => {

        scrollContainer.scrollTo({
            top: 0,
            behavior: "smooth"
        });

    });

});
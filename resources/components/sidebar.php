<?php

require_once __DIR__ . '/../../app/helpers/menu_helper.php';
require_once __DIR__ . '/../../app/helpers/childmenu_helper.php';
require_once __DIR__ . '/../../app/helpers/icon_helper.php';
require_once __DIR__ . '/../../app/helpers/menu_engine.php';

$allowedRoles = ['admin', 'kasir', 'owner'];
$role = $_SESSION['role'] ?? 'guest';

if (!in_array($role, $allowedRoles)) {
    $role = 'guest';
}

$lang = $_SESSION['lang'] ?? 'en';
$menuConfig = require __DIR__ . '/../../config/sidebar_menu.php';
$menus = $menuConfig[$role] ?? [];
$currentMenu = findMenuByRoute($menus);

?>

<!-- GLOBAL LOADER (CINEMATIC UX) -->

<div id="topLoader"
    class="fixed top-0 left-0 h-[3px] w-0 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 z-[99999] transition-all duration-300 pointer-events-none">
</div>

<template x-teleport="body">
    <div
        id="pageLoader"
        x-show="$store.loader.open"
        x-transition.opacity
        x-cloak
        class="fixed inset-0 z-[999999] flex items-center justify-center
               bg-white/50 dark:bg-gray-900/50 backdrop-blur-2xl">

        <div class="relative flex flex-col items-center gap-8">

            <div class="absolute w-40 h-40 rounded-full
                        bg-gradient-to-tr from-pink-500 via-purple-500 to-indigo-500
                        opacity-20 blur-3xl animate-pulse">
            </div>

            <div class="w-16 h-16 rounded-full border-[3px]
                        border-transparent border-t-pink-500 border-r-purple-500
                        animate-spin">
            </div>

            <div class="absolute w-6 h-6 rounded-full bg-white dark:bg-gray-200 animate-ping"></div>

            <div class="flex flex-col items-center gap-1 z-10">
                <span class="text-sm tracking-widest uppercase text-gray-500 dark:text-gray-400">
                    RKD System
                </span>
                <span class="text-xs text-gray-400 animate-pulse"
                    x-text="$store.loader.text">
                </span>
            </div>

        </div>

    </div>
</template>

<div
    x-show="sidebarOpen"
    @click="sidebarOpen=false"
    class="fixed inset-0 bg-black/40 z-30 md:hidden"
    x-transition.opacity>
</div>

<!-- SIDEBAR HEADER -->
<div class="flex items-center justify-between px-4 py-4 border-b border-gray-200 dark:border-gray-700 dark:text-white">

    <span x-show="sidebarOpen" class="text-2xl font-bold">
        ☕ <?= $t['app_name'] ?>
    </span>

    <button
        @click="sidebarOpen = !sidebarOpen;

        if(window.innerWidth >= 768){
            fetch('/rkd-cafe/api/sidebar/state.php',{
                method:'POST',
                headers:{'Content-Type':'application/json', 'X-CSRF-TOKEN': window.csrfToken},
                body: JSON.stringify({collapsed: !sidebarOpen})
            }).then(res => res.json()).catch(err => console.error('Sidebar state error', err));
        }"

        class="mr-4 text-xl text-gray-600 dark:text-gray-300 cursor-pointer"
        :class="!sidebarOpen ? 'ml-4' : ''">

        <i class="fa-solid fa-bars"></i>

    </button>

</div>


<!-- SIDEBAR MENU -->

<nav class="flex-1 p-2 md:p-4 space-y-2 dark:text-white overflow-y-auto">

    <?php foreach ($menus as $menu): ?>

        <?php if (isset($menu['children'])): ?>

            <div
                x-data="{open: <?= isActivePrefix($menu['prefix'] ?? '') ? 'true' : 'false' ?>, hover: false, timeout: null}"
                @mouseenter="clearTimeout(timeout); timeout = null; hover = true;"
                @mouseleave="timeout = setTimeout(() => hover = false, 150);"
                class="relative">

                <button
                    @click="open=!open"
                    data-tooltip="<?= htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8') ?>"
                    class="flex items-center w-full px-3 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer <?= activeParent($menu['prefix'] ?? '') ?>">

                    <i class="fa-solid <?= $menu['icon'] ?> w-5 text-center <?= iconSpacing($menu) ?>"></i>

                    <span
                        class="ml-3 flex-1 text-left transition-opacity duration-200"
                        :class="sidebarOpen ? 'opacity-100' : 'hidden'">
                        <?= htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8') ?>
                    </span>

                    <!-- EXPANDED MODE -->
                    <i x-show="sidebarOpen"
                        class="fa-solid fa-chevron-down text-xs transition-transform"
                        :class="{'rotate-180':open}">
                    </i>

                    <!-- COLLAPSED MODE -->
                    <i x-show="!sidebarOpen"
                        class="fa-solid fa-chevron-right text-[8px] mt-1 ml-2">
                    </i>

                </button>

                <!-- EXPANDED SUBMENU -->
                <div
                    x-show="open && sidebarOpen"
                    x-transition
                    class="ml-8 mt-1 space-y-1">

                    <?php foreach ($menu['children'] as $child): ?>

                        <a
                            href="<?= $child['url'] ?>"
                            class="nav-link flex items-center w-full px-3 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 <?= activeMenu($child['url']) ?>">

                            <i class="fa-solid <?= $child['icon'] ?? 'fa-circle' ?> w-5 text-center"></i>
                            <span class="ml-2"><?= $child['title'] ?></span>

                        </a>

                    <?php endforeach; ?>

                </div>

                <!-- COLLAPSED (HOVER FLOATING) -->
                <div
                    x-show="!sidebarOpen && hover"
                    @mouseenter="clearTimeout(timeout); timeout = null; hover = true;"
                    @mouseleave="timeout = setTimeout(() => hover = false, 150)"
                    x-transition
                    x-ref="submenu"
                    x-init="
                        $watch('hover', value => {
                            if(value){
                                const rect = $el.parentElement.getBoundingClientRect();
                                
                                $refs.submenu.style.top = (rect.top + window.scrollY) + 'px';
                                $refs.submenu.style.left = (rect.right + 8) + 'px';
                            }
                        })
                    "
                    class="fixed w-56 bg-white dark:bg-gray-800 shadow-xl rounded-lg p-2 z-[9999] transition-all duration-200 ease-out">

                    <?php foreach ($menu['children'] as $child): ?>

                        <a
                            href="<?= $child['url'] ?>"
                            class="nav-link block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">

                            <i class="fa-solid <?= $child['icon'] ?? 'fa-circle' ?> mr-2"></i>
                            <?= $child['title'] ?>

                        </a>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php else: ?>

            <a
                href="<?= $menu['url'] ?>"
                data-tooltip="<?= htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8') ?>"
                class="nav-link flex items-center w-full px-3 py-3 rounded-lg border-l-4 border-transparent transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 <?= activeMenu($menu['url']) ?>">

                <i class="fa-solid <?= $menu['icon'] ?> w-5 text-center"></i>

                <span
                    class="ml-3 flex-1 transition-opacity duration-200"
                    x-show="sidebarOpen">

                    <?= htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8') ?>

                </span>

            </a>

        <?php endif; ?>

    <?php endforeach; ?>


    <!-- ============================= -->
    <!-- GLOBAL MENU -->
    <!-- ============================= -->
    <?php
    $settingsUrl = "/rkd-cafe/pages/$role/settings.php";
    ?>

    <a
        href="<?= $settingsUrl ?>"
        data-tooltip="Settings"
        class="nav-link flex items-center w-full px-3 py-3 rounded-lg border-l-4 border-transparent hover:bg-gray-100 dark:hover:bg-gray-700 <?= activeMenu($settingsUrl) ?>">

        <i class="fa-solid fa-gear w-5 text-center"></i>

        <span
            class="ml-3 flex-1 transition-opacity duration-200"
            x-show="sidebarOpen">
            Settings
        </span>

    </a>

</nav>


<!-- LOGOUT -->
<div class="p-4 border-t border-gray-200 dark:border-gray-700">

    <form
        id="logoutForm"
        method="POST"
        action="/rkd-cafe/resources/views/auth/logout.php">

        <input type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <button
            type="submit"
            data-tooltip="Logout"
            class="flex items-center w-full px-3 py-3 rounded-lg hover:bg-red-600 dark:text-white cursor-pointer">

            <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>

            <span
                class="-ml-24 flex-1 transition-opacity duration-200"
                x-show="sidebarOpen">

                <?= htmlspecialchars($t['logout'] ?? 'Logout', ENT_QUOTES, 'UTF-8') ?>

            </span>

        </button>

    </form>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('loader', {
            open: false,
            text: "Preparing your experience...",

            show(message = null) {
                this.open = true;
                this.text = message || "Preparing your experience...";
            },

            hide() {
                this.open = false;
                this.text = "Preparing your experience...";
            }
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        const topLoader = document.getElementById("topLoader");

        const Loader = {
            isNavigating: false,
            MIN_DURATION: 1200,

            startProgress() {
                if (!topLoader) return;

                topLoader.style.transition = "none";
                topLoader.style.width = "0%";

                requestAnimationFrame(() => {
                    topLoader.style.transition = "width 0.3s ease";
                    topLoader.style.width = "20%";
                });

                setTimeout(() => topLoader.style.width = "45%", 100);
                setTimeout(() => topLoader.style.width = "70%", 200);
                setTimeout(() => topLoader.style.width = "90%", 300);
            },

            finishProgress() {
                if (!topLoader) return;

                topLoader.style.width = "100%";

                setTimeout(() => {
                    topLoader.style.width = "0%";
                }, 300);
            },

            lockUI() {
                document.body.classList.add("page-loading");

                requestAnimationFrame(() => {
                    document.body.style.pointerEvents = "none";
                });
            },

            unlockUI() {
                document.body.style.pointerEvents = "";
                document.body.classList.remove("page-loading");
            },

            start(message = null) {
                this.isNavigating = true;

                this.startProgress();
                Alpine.store('loader').show(message);
                this.lockUI();
            },

            reset() {
                this.isNavigating = false;
                Alpine.store('loader').hide();
                this.finishProgress();
                this.unlockUI();
            }
        };

        // =========================
        // BACK / FORWARD FIX
        // =========================
        window.addEventListener("pageshow", (event) => {
            if (
                event.persisted ||
                performance.getEntriesByType("navigation")[0]?.type === "back_forward"
            ) {
                Loader.reset();
            }
        });

        // =========================
        // NAVIGATION HANDLER
        // =========================
        function handleNavigation(url, element) {

            if (!url) return;

            const parsed = new URL(url, window.location.origin);

            if (
                url.startsWith("#") ||
                url.startsWith("javascript:") ||
                parsed.origin !== window.location.origin
            ) return;

            const current = window.location.pathname;
            const target = parsed.pathname;

            if (current === target) return;

            if (Loader.isNavigating) return;

            // UI feedback
            element?.classList.add("scale-95", "opacity-70");

            setTimeout(() => {
                element?.classList.remove("scale-95", "opacity-70");
            }, 150);

            // start loader
            Loader.start();

            // delay navigation
            setTimeout(() => {
                window.location.href = url;
            }, Loader.MIN_DURATION);
        }

        document.querySelectorAll("nav a[href]").forEach(link => {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                handleNavigation(this.getAttribute("href"), this);
            });
        });

        // =========================
        // LOGOUT HANDLER
        // =========================
        const logoutForm = document.getElementById("logoutForm");

        if (logoutForm) {
            logoutForm.addEventListener("submit", function(e) {

                if (Loader.isNavigating) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();

                const messages = [
                    "Signing you out...",
                    "Securing your session...",
                    "Goodbye 👋"
                ];

                const message = messages[Math.floor(Math.random() * messages.length)];

                Loader.start(message);

                setTimeout(() => {
                    logoutForm.submit();
                }, Loader.MIN_DURATION);
            });
        }

        // =========================
        // PAGE ENTER ANIMATION
        // =========================
        document.body.classList.add("page-enter");

        requestAnimationFrame(() => {
            document.body.classList.add("page-enter-active");
        });

    });
</script>
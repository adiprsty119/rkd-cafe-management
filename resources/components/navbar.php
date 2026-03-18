<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/auth_helper.php';
require_once __DIR__ . '/../../app/helpers/user_helper.php';
require_once __DIR__ . '/../../app/helpers/avatar_helper.php';
require_once __DIR__ . '/../../app/helpers/role_helper.php';
require_once __DIR__ . '/../../app/helpers/notification_helper.php';

$pdo = getPDO();

/* ==========================
   AUTH VALIDATION
========================== */

$userId = requireLogin();

/* ==========================
   DATA FETCH
========================== */

$currentUser = getUserById($pdo, $userId);
$displayName = getDisplayName($currentUser);
$foto = getUserAvatar();
$settingsUrl = getSettingsUrl();
$notificationCount = getUnreadNotificationCount($pdo, $userId);

?>

<header class="bg-white dark:bg-gray-800 p-4 flex items-center justify-between shadow-2xl">

    <div class="flex items-center gap-3">

        <button
            @click="sidebarOpen = !sidebarOpen"
            class="md:hidden mr-3 text-xl text-gray-600 dark:text-gray-300">

            <i class="fa-solid fa-bars"></i>

        </button>

        <!-- TITLE -->
        <h1 class="text-xl font-semibold cursor-pointer">
            <?= htmlspecialchars($t['dashboard'] ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?>
        </h1>

    </div>

    <!-- RIGHT MENU -->
    <div class="flex items-center space-x-3 md:space-x-6">

        <!-- SEARCH -->
        <div class="relative">

            <input
                type="text"
                id="searchInput"
                placeholder="<?= htmlspecialchars($t['search'] ?? 'Search', ENT_QUOTES, 'UTF-8') ?>..."
                class="w-32 sm:w-48 lg:w-96 px-4 py-2 pr-10 border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 dark:bg-gray-700 dark:text-white">

            <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-amber-300 hover:dark:text-amber-400 cursor-pointer"></i>

            <!-- SEARCH RESULT -->
            <div
                id="searchResults"
                class="absolute left-0 right-0 mt-2 bg-white dark:bg-gray-800 border rounded-lg shadow-lg hidden z-50">
            </div>
        </div>

        <!-- LANGUAGE BUTTON -->
        <div x-data="{open:false, currentLang: new URLSearchParams(window.location.search).get('lang') || '<?= $lang ?>'}" class="relative">

            <button
                @click="open=!open"
                class="flex items-center gap-3 px-4 py-1 bg-yellow-400 border-2 border-yellow-500 rounded-full cursor-pointer">

                <i class="fa-solid fa-globe"></i>

                <span x-text="currentLang.toUpperCase()"></span>

                <i
                    class="fa-solid fa-caret-down transition-transform duration-200"
                    :style="`transform: rotate(${open ? 180 : 0}deg)`">
                </i>

            </button>

            <div
                x-show="open"
                x-cloak
                x-transition.scale.origin.top
                @click.outside="open=false"
                class="absolute right-0 mt-2 w-24 bg-white shadow rounded-lg dark:bg-gray-700">

                <a href="?lang=id"
                    @click.prevent="
                        if(window.LoaderEngine?.isNavigating) return;

                        open = false;
                        currentLang = 'id';

                        const url = new URL(window.location.href);
                        url.searchParams.set('lang', 'id');

                        if(window.LoaderEngine){
                            window.dispatchEvent(new CustomEvent('app:navigate', {
                                detail: {
                                    url: url.toString(),
                                    message: 'Switching language...'
                                }
                            }));
                        } else {
                            window.location.href = url.toString();
                        }"
                    class="block px-3 py-2 hover:bg-gray-100 hover:dark:bg-gray-500"
                    :class="currentLang === 'id' ? 'font-bold' : ''">
                    ID
                </a>

                <a href="?lang=en"
                    @click.prevent="
                        if(window.LoaderEngine?.isNavigating) return;

                        open = false;
                        currentLang = 'en';

                        const url = new URL(window.location.href);
                        url.searchParams.set('lang', 'en');

                        if(window.LoaderEngine){
                            window.dispatchEvent(new CustomEvent('app:navigate', {
                                detail: {
                                    url: url.toString(),
                                    message: 'Switching language...'
                                }
                            }));
                        } else {
                            window.location.href = url.toString();
                        }"
                    class="block px-3 py-2 hover:bg-gray-100 hover:dark:bg-gray-500"
                    :class="currentLang === 'en' ? 'font-bold' : ''">
                    EN
                </a>

            </div>

        </div>

        <!-- DARK MODE BUTTON -->
        <button
            @click="
                if(window.LoaderEngine && !window.LoaderEngine.isNavigating){

                    window.LoaderEngine.start('Applying theme...');

                    const html = document.documentElement;
                    const initial = html.classList.contains('dark');

                    // 🔥 OBSERVER
                    const observer = new MutationObserver(() => {
                        const current = html.classList.contains('dark');

                        if (current !== initial) {
                            observer.disconnect();

                            // tunggu render settle
                            requestAnimationFrame(() => {
                                requestAnimationFrame(() => {
                                    window.LoaderEngine.reset();
                                });
                            });
                        }
                    });

                    observer.observe(html, { attributes: true, attributeFilter: ['class'] });

                    // 🔥 TRIGGER THEME
                    $dispatch('toggle-theme');

                } else {
                    $dispatch('toggle-theme');
                }"
            class="flex items-center gap-3 px-4 py-1 bg-yellow-400 border-2 border-yellow-500 rounded-full font-semibold hover:bg-yellow-300 transition cursor-pointer">

            <span class="w-6 flex justify-center">
                <i class="fa-fw transition-transform duration-300" :class="dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
            </span>

            <span class="w-14 text-center" x-text="dark ? '<?= $t['light'] ?>' : '<?= $t['dark'] ?>'"></span>

        </button>

        <!-- NOTIFICATION -->
        <div x-data="notificationSystem()" x-init="init()" class="relative">

            <!-- ICON -->
            <button
                @click="toggle()"
                class="relative p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">

                <i
                    class="fa-solid fa-bell text-lg text-yellow-400 hover:text-yellow-300"
                    :class="count > 0 ? 'bell-alert' : ''">
                </i>

                <span
                    x-show="count>0"
                    x-text="count"
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] px-1.5 py-[1px] rounded-full shadow">
                </span>

            </button>


            <!-- DROPDOWN -->
            <div
                x-show="open"
                x-transition.origin.top.right
                @click.outside="open=false"
                class="absolute right-0 mt-3 w-96 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50">

                <!-- HEADER -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">

                    <h3 class="font-semibold text-gray-800 dark:text-white">
                        Notifications
                    </h3>

                    <button
                        @click="markAllRead()"
                        class="text-xs text-blue-500 hover:text-blue-600 font-medium cursor-pointer">

                        Mark all read

                    </button>

                </div>


                <!-- LIST -->
                <div class="max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">

                    <template x-for="n in notifications" :key="n.id">

                        <div
                            @click="markRead(n.id)"
                            class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">

                            <!-- ICON -->
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center">

                                <i
                                    :class="'fa-solid ' + icon(n.type)"
                                    class="fa-solid fa-bell text-blue-500 text-sm">
                                </i>

                            </div>

                            <!-- CONTENT -->
                            <div class="flex-1 min-w-0">

                                <div class="flex items-center justify-between">

                                    <div class="flex items-center gap-2">

                                        <!-- UNREAD DOT -->
                                        <div
                                            x-show="!n.is_read"
                                            class="w-2 h-2 bg-blue-500 rounded-full">
                                        </div>

                                        <p
                                            class="text-sm font-semibold text-gray-800 dark:text-white"
                                            x-text="n.title">
                                        </p>

                                    </div>

                                    <span
                                        class="text-xs text-gray-400"
                                        x-text="n.time">
                                    </span>

                                </div>

                                <p
                                    class="text-xs text-gray-500 mt-1"
                                    x-text="n.message">
                                </p>

                            </div>

                        </div>

                    </template>

                </div>


                <!-- FOOTER -->
                <div class="text-center py-3 border-t border-gray-100 dark:border-gray-700">

                    <a
                        href="/rkd-cafe/public/notifications.php"
                        class="text-sm font-medium text-blue-500 hover:text-blue-600">

                        View all notifications

                    </a>

                </div>

            </div>

        </div>

        <!-- USER PROFILE -->
        <div x-data="{ open:false }"
            @mouseenter="open=true"
            @mouseleave="open=false"
            class="relative flex items-center space-x-2 cursor-pointer">

            <!-- AVATAR -->
            <img src="<?= htmlspecialchars($foto, ENT_QUOTES, 'UTF-8') ?>"
                loading="lazy"
                alt="User Avatar"
                referrerpolicy="no-referrer"
                class="w-8 h-8 rounded-full object-cover">

            <!-- USERNAME -->
            <span class="text-sm font-medium hidden sm:block">
                <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
            </span>

            <!-- DROPDOWN -->
            <div x-show="open"
                x-transition
                x-cloak
                class="absolute -right-4 top-9 w-32 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">

                <!-- PROFILE -->
                <a href="<?= htmlspecialchars('/rkd-cafe/public/profile.php', ENT_QUOTES, 'UTF-8') ?>"
                    @click.prevent="window.dispatchEvent(new CustomEvent('app:navigate', { detail: { url: '/rkd-cafe/public/profile.php' } }))"
                    class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">

                    <i class="fa-solid fa-user text-gray-500"></i>
                    <?= htmlspecialchars($t['profile'] ?? 'Profile', ENT_QUOTES, 'UTF-8') ?>

                </a>

                <!-- SETTINGS -->
                <a href="<?= htmlspecialchars($settingsUrl, ENT_QUOTES, 'UTF-8') ?>"
                    @click.prevent="window.dispatchEvent(new CustomEvent('app:navigate', { detail: { url: '<?= $settingsUrl ?>' } }))"
                    class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">

                    <i class="fa-solid fa-gear text-gray-500"></i>
                    <?= htmlspecialchars($t['settings'] ?? 'Settings', ENT_QUOTES, 'UTF-8') ?>

                </a>

                <!-- DIVIDER -->
                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>

                <!-- LOGOUT -->
                <form
                    id="navbarLogoutForm"
                    method="POST"
                    action="/rkd-cafe/resources/views/auth/logout.php">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <button
                        type="submit"
                        class="flex items-center gap-2 px-4 py-2 text-sm text-red-500 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left cursor-pointer">

                        <i class="fa-solid fa-right-from-bracket"></i>
                        <?= htmlspecialchars($t['logout'] ?? 'Logout', ENT_QUOTES, 'UTF-8') ?>

                    </button>

                </form>

            </div>

        </div>

    </div>

</header>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        // =========================
        // GLOBAL NAVIGATION LISTENER
        // =========================
        window.addEventListener("app:navigate", function(e) {

            const {
                url,
                message
            } = e.detail || {};
            if (!url) return;

            if (window.LoaderEngine && !window.LoaderEngine.isNavigating) {

                window.LoaderEngine.start(message || null);
                window.LoaderEngine.navigate(url);

            } else {
                window.location.href = url;
            }

        });

        // =========================
        // NAVBAR LOGOUT
        // =========================
        const logoutForm = document.getElementById("navbarLogoutForm");

        if (logoutForm) {
            logoutForm.addEventListener("submit", function(e) {

                if (window.LoaderEngine?.isNavigating) {
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

                if (window.LoaderEngine) {
                    window.LoaderEngine.start(message);
                    window.LoaderEngine.submit(logoutForm);
                } else {
                    logoutForm.submit();
                }

            });
        }

    });
</script>
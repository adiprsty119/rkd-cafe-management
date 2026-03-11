<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/auth_helper.php';
require_once __DIR__ . '/../../app/helpers/user_helper.php';
require_once __DIR__ . '/../../app/helpers/avatar_helper.php';
require_once __DIR__ . '/../../app/helpers/role_helper.php';
require_once __DIR__ . '/../../app/helpers/notification_helper.php';

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
                placeholder="<?= htmlspecialchars($t['search'] ?? 'Search', ENT_QUOTES, 'UTF-8') ?>..."
                class="w-32 sm:w-48 lg:w-96 px-4 py-2 pr-10 border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 dark:bg-gray-700 dark:text-white">

            <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-amber-300 hover:dark:text-amber-400 cursor-pointer"></i>

        </div>

        <!-- LANGUAGE BUTTON -->
        <div x-data="{open:false, loadingLang:false}" class="relative">

            <button
                @click="open=!open"
                class="flex items-center gap-3 px-4 py-1 bg-yellow-400 border-2 border-yellow-500 rounded-full cursor-pointer">

                <i class="fa-solid fa-globe"></i>

                <span><?= strtoupper($lang) ?></span>

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
                    @click.prevent="loadingLang=true; setTimeout(()=>window.location='?lang=id',400)"
                    class="block px-3 py-2 hover:bg-gray-100 hover:dark:bg-gray-500 <?= $lang == 'id' ? 'font-bold' : '' ?>">
                    ID
                </a>

                <a href="?lang=en"
                    @click.prevent="loadingLang=true; setTimeout(()=>window.location='?lang=en',400)"
                    class="block px-3 py-2 hover:bg-gray-100 hover:dark:bg-gray-500 <?= $lang == 'en' ? 'font-bold' : '' ?>">
                    EN
                </a>

            </div>

        </div>

        <!-- DARK MODE BUTTON -->
        <button
            @click="$dispatch('toggle-theme')"
            class="flex items-center gap-3 px-4 py-1 bg-yellow-400 border-2 border-yellow-500 rounded-full font-semibold hover:bg-yellow-300 transition cursor-pointer">

            <span class="w-6 flex justify-center">
                <i class="fa-fw transition-transform duration-300" :class="dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
            </span>

            <span class="w-14 text-center" x-text="dark ? '<?= $t['light'] ?>' : '<?= $t['dark'] ?>'"></span>

        </button>

        <!-- NOTIFICATION -->
        <div x-data="notificationSystem()" x-init="init()" class="relative">

            <button @click="toggle()" class="relative text-amber-300 hover:text-amber-400 cursor-pointer">

                <i class="fa-solid fa-bell text-lg"></i>

                <span
                    x-show="count>0"
                    x-text="count"
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 rounded-full">
                </span>

            </button>

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
                    class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">

                    <i class="fa-solid fa-user text-gray-500"></i>
                    <?= htmlspecialchars($t['profile'] ?? 'Profile', ENT_QUOTES, 'UTF-8') ?>

                </a>

                <!-- SETTINGS -->
                <a href="<?= htmlspecialchars($settingsUrl, ENT_QUOTES, 'UTF-8') ?>"
                    class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">

                    <i class="fa-solid fa-gear text-gray-500"></i>
                    <?= htmlspecialchars($t['settings'] ?? 'Settings', ENT_QUOTES, 'UTF-8') ?>

                </a>

                <!-- DIVIDER -->
                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>

                <!-- LOGOUT -->
                <form method="POST" action="/rkd-cafe/resources/views/auth/logout.php">

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
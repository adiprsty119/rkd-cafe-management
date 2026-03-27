<?php
define('APP_INIT', true);
define('BYPASS_MENU_VALIDATION', true);

require $_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/middleware/AuthMiddleware.php';

/*
|--------------------------------------------------------------------------
| LANGUAGE SYSTEM
|--------------------------------------------------------------------------
*/

$lang = $_GET['lang'] ?? 'id';

if (!in_array($lang, ['id', 'en'])) {
    $lang = 'id';
}

$t = require __DIR__ . '/../../resources/lang/' . $lang . '.php';
require_once __DIR__ . '/../../app/helpers/menu_helper.php';
require_once __DIR__ . '/../../app/helpers/menu_engine.php';

$role = $_SESSION['role'] ?? 'guest';
$sidebarCollapsed = $_SESSION['sidebar_collapsed'] ?? 0;

/* ==========================
   MENU ENGINE
========================== */

$menus = getMenusByRole($role);
$currentMenu = findMenuByRoute($menus);

// Deteksi settings page
$isSettingsPage = str_contains($_SERVER['REQUEST_URI'], 'settings.php');

// Tentukan page title
if ($isSettingsPage) {
    $pageTitle = 'Settings';
} else {
    $pageTitle = $currentMenu['menu']['title'] ?? 'Dashboard';
}

// FALLBACK BREADCRUMB
if (!$currentMenu) {

    $breadcrumb = [
        [
            'title' => $t['dashboard'] ?? 'Dashboard',
            'url'   => '/rkd-cafe/resources/views/dashboard/' . ($role ?? 'admin') . '.php'
        ],
        [
            'title' => $pageTitle,
            'url'   => null
        ]
    ];
} else {

    $breadcrumb = generateBreadcrumb($currentMenu);
}

/*
|--------------------------------------------------------------------------
| SETTINGS TAB CONFIG
|--------------------------------------------------------------------------
*/

$settingsTabs = [

    [
        'id' => 'profile',
        'title' => 'Profile',
        'icon' => 'fa-user'
    ],

    [
        'id' => 'security',
        'title' => 'Security',
        'icon' => 'fa-lock'
    ],

    [
        'id' => 'application',
        'title' => 'Application',
        'icon' => 'fa-gear'
    ],

    [
        'id' => 'theme',
        'title' => 'Theme',
        'icon' => 'fa-palette'
    ]

];

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $pageTitle ?></title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- Vanilla CSS -->
    <link href="/rkd-cafe/public/assets/css/utilities.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Global Seacrh [global-search.js] -->
    <script defer src="/rkd-cafe/public/assets/js/global-search.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>


<body
    x-data="{ dark: localStorage.theme === 'dark', loading:false, loadingTheme:false, sidebarOpen:<?= isset($sidebarCollapsed) && $sidebarCollapsed ? 'false' : 'true' ?>, tab:'profile' }"
    @toggle-theme.window="loadingTheme = true; setTimeout(() => {let newTheme = !dark; localStorage.theme = newTheme ? 'dark' : 'light'; location.reload();}, 800)"
    x-init="document.documentElement.classList.toggle('dark', dark)"
    :class="{ 'dark': dark }"
    class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-300">


    <div class="flex min-h-screen bg-gradient-to-br from-gray-200 via-gray-100 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition">


        <!-- SIDEBAR -->
        <aside
            :class="sidebarOpen ? 'w-64' : 'w-20'"
            class="hidden md:flex flex-col h-screen bg-white dark:bg-gray-800 transition-all duration-300 overflow-x-hidden">

            <?php require __DIR__ . '/../../resources/components/sidebar.php'; ?>

        </aside>


        <!-- MOBILE SIDEBAR -->
        <aside
            x-show="sidebarOpen"
            x-transition
            class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 z-40 md:hidden">

            <?php require __DIR__ . '/../../resources/components/sidebar.php'; ?>

        </aside>


        <div
            x-show="sidebarOpen"
            @click="sidebarOpen=false"
            class="fixed inset-0 bg-black/40 z-30 md:hidden">
        </div>

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

            <!-- =========================
                HEADER STACK
            ========================= -->
            <div id="headerStack" class="sticky top-0 z-50 relative">

                <!-- =========================
                    NAVBAR
                ========================= -->
                <div
                    id="dashboardNavbar"
                    class="relative z-50 transition-all duration-300">

                    <div class="w-full px-4 mt-3 transition-all duration-300 bg-transparent">
                        <?php require __DIR__ . '/../../resources/components/navbar.php'; ?>
                    </div>


                    <!-- =========================
                        BREADCRUMB INDICATOR
                    ========================= -->
                    <div
                        id="breadcrumbIndicator"
                        class="absolute left-auto -translate-x-1/2 top-full z-50 opacity-0 translate-y-2 pointer-events-none transition-all ease-out delay-75 duration-300">

                        <button
                            class="flex items-center ml-16 px-2 py-1 text-sm rounded-sm backdrop-blur-md bg-gray-100 dark:bg-gray-800 shadow-md hover:bg-white/60 active:scale-95 transition cursor-pointer">

                            <i class="fa-solid fa-angle-down animate-bounce"></i>

                        </button>

                    </div>

                </div>

            </div>


            <!-- =========================
                BREADCRUMB CONTAINER
            ========================= -->
            <div
                id="breadcrumbContainer"
                class="relative will-change-transform transition-opacity duration-200">

                <div
                    id="breadcrumbMask"
                    class="px-4 py-2 overflow-hidden">

                    <?php require __DIR__ . '/../../resources/components/breadcrumb.php'; ?>

                </div>

            </div>


            <main id="dashboardScroll"
                class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6 scrollbar-hide">

                <!-- HEADER -->
                <div class="flex flex-col mb-12 md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm px-6 py-5">

                    <!-- LEFT -->
                    <div class="flex items-center gap-4">

                        <!-- PAGE ICON -->
                        <div class="w-11 h-11 flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700">

                            <i class="fa-solid fa-gear text-gray-700 dark:text-gray-300"></i>

                        </div>

                        <!-- TITLE -->
                        <div>

                            <h1 class="text-xl md:text-2xl font-semibold tracking-tight">
                                <?= htmlspecialchars($pageTitle) ?>
                            </h1>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <?= $t['manage_settings'] ?? 'Manage application settings' ?>
                            </p>

                        </div>

                    </div>

                </div>

                <div x-data="settingsPage()" class="grid grid-cols-1 md:grid-cols-4 gap-6">


                    <!-- SETTINGS SIDEBAR -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-2">

                        <?php foreach ($settingsTabs as $tabItem): ?>

                            <button
                                @click="tab='<?= $tabItem['id'] ?>'"
                                :class="tab==='<?= $tabItem['id'] ?>' ? 'bg-gray-100 dark:bg-gray-700' : ''"
                                class="w-full flex items-center px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition">

                                <i class="fa-solid <?= $tabItem['icon'] ?> mr-3"></i>

                                <span><?= $tabItem['title'] ?></span>

                            </button>

                        <?php endforeach; ?>

                    </div>



                    <!-- SETTINGS PANEL -->
                    <div class="md:col-span-3 space-y-6">


                        <?php foreach ($settingsTabs as $tabItem): ?>

                            <div
                                x-show="tab==='<?= $tabItem['id'] ?>'"
                                x-transition
                                class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                                <?php

                                switch ($tabItem['id']) {


                                    case 'profile':
                                ?>

                                        <h2 class="font-semibold text-lg mb-4">Profile Settings</h2>

                                        <label class="block text-sm mb-1">Name</label>

                                        <input
                                            type="text"
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">


                                        <label class="block text-sm mb-1">Email</label>

                                        <input
                                            type="email"
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">


                                        <button
                                            class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded">

                                            Save Changes

                                        </button>

                                    <?php
                                        break;


                                    case 'security':
                                    ?>

                                        <h2 class="font-semibold text-lg mb-4">Security Settings</h2>

                                        <input
                                            type="password"
                                            placeholder="New Password"
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">


                                        <input
                                            type="password"
                                            placeholder="Confirm Password"
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">


                                        <button
                                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">

                                            Update Password

                                        </button>

                                    <?php
                                        break;


                                    case 'application':
                                    ?>

                                        <h2 class="font-semibold text-lg mb-4">Application Settings</h2>

                                        <label class="block text-sm mb-1">Cafe Name</label>

                                        <input
                                            type="text"
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">


                                        <label class="block text-sm mb-1">Currency</label>

                                        <select
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">

                                            <option>IDR</option>
                                            <option>USD</option>

                                        </select>


                                        <button
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">

                                            Save Settings

                                        </button>

                                    <?php
                                        break;


                                    case 'theme':
                                    ?>

                                        <h2 class="font-semibold text-lg mb-4">Theme Settings</h2>

                                        <p class="text-sm text-gray-500 mb-3">
                                            Switch between dark and light mode
                                        </p>

                                        <button
                                            @click="$dispatch('toggle-theme')"
                                            class="bg-gray-800 text-white px-4 py-2 rounded">

                                            Toggle Dark Mode

                                        </button>

                                <?php
                                        break;
                                }

                                ?>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </main>

        </div>

    </div>

    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>
    <script src="/rkd-cafe/public/assets/js/header.js?v=<?= time() ?>"></script>
    <script src="/rkd-cafe/public/assets/js/sidebar-tooltip.js"></script>

    <div
        id="global-tooltip"
        class="fixed hidden px-2 py-1 text-xs text-white bg-black rounded shadow-lg whitespace-nowrap z-[9999] pointer-events-none">
    </div>

    <?php require __DIR__ . '/../../resources/components/toast.php'; ?>
    <?php if (isset($_SESSION['toast'])): ?>

        <script>
            window.toastData = {
                type: "<?= $_SESSION['toast']['type'] ?>",
                message: "<?= $_SESSION['toast']['message'] ?>"
            }
        </script>

    <?php unset($_SESSION['toast']);
    endif; ?>

    <script>
        function settingsPage() {

            return {

                tab: 'profile'

            }

        }
    </script>
</body>

</html>
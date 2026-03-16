<?php

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

/* ==========================
   MENU ENGINE
========================== */

$menus = getMenusByRole($role);
$currentMenu = findMenuByRoute($menus);
$pageTitle = $currentMenu['menu']['title'] ?? 'Dashboard';
$breadcrumb = generateBreadcrumb($currentMenu);

/*
|--------------------------------------------------------------------------
| OWNER SETTINGS CONFIG
|--------------------------------------------------------------------------
*/

$settingsTabs = [

    [
        'id' => 'profile',
        'title' => 'Profile',
        'icon' => 'fa-user'
    ],

    [
        'id' => 'business',
        'title' => 'Business',
        'icon' => 'fa-store'
    ],

    [
        'id' => 'finance',
        'title' => 'Finance',
        'icon' => 'fa-money-bill'
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

    <!-- Font Awesome -->
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
    x-data="{ dark: localStorage.theme === 'dark', loading:false, loadingTheme:false, sidebarOpen:true }"
    @toggle-theme.window="loadingTheme = true; setTimeout(() => {let newTheme = !dark; localStorage.theme = newTheme ? 'dark' : 'light'; location.reload();}, 800)"
    x-init="document.documentElement.classList.toggle('dark', dark)"
    :class="{ 'dark': dark }"
    class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-300">


    <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900 relative">


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


            <!-- NAVBAR -->
            <div class="p-4 border-t border-gray-700">

                <?php require __DIR__ . '/../../resources/components/navbar.php'; ?>

            </div>

            <!-- BREADCRUMB NAVIGATION -->
            <?php require __DIR__ . '/../../resources/components/breadcrumb.php'; ?>

            <!-- SETTINGS CONTENT -->
            <main class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6">


                <!-- HEADER -->
                <div>

                    <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>

                    <p class="text-sm text-gray-500">
                        Owner business configuration
                    </p>

                </div>



                <div x-data="settingsPage()" class="grid grid-cols-1 md:grid-cols-4 gap-6">


                    <!-- SETTINGS SIDEBAR -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-2">

                        <?php foreach ($settingsTabs as $tab): ?>

                            <button
                                @click="tab='<?= $tab['id'] ?>'"
                                :class="tab==='<?= $tab['id'] ?>' ? 'bg-gray-100 dark:bg-gray-700 border-l-4 border-yellow-500' : ''"
                                class="w-full flex items-center px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition">

                                <i class="fa-solid <?= $tab['icon'] ?> mr-3"></i>

                                <span><?= $tab['title'] ?></span>

                            </button>

                        <?php endforeach; ?>

                    </div>



                    <!-- SETTINGS PANEL -->
                    <div class="md:col-span-3 space-y-6">


                        <?php foreach ($settingsTabs as $tab): ?>

                            <div
                                x-show="tab==='<?= $tab['id'] ?>'"
                                x-transition
                                class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                                <?php

                                switch ($tab['id']) {


                                    case 'profile':
                                ?>

                                        <h2 class="font-semibold text-lg mb-4">Owner Profile</h2>

                                        <input
                                            type="text"
                                            placeholder="Owner Name"
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">

                                        <input
                                            type="email"
                                            placeholder="Email"
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">

                                        <button
                                            class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded">

                                            Save Profile

                                        </button>

                                    <?php
                                        break;



                                    case 'business':
                                    ?>

                                        <h2 class="font-semibold text-lg mb-4">Business Information</h2>

                                        <input
                                            type="text"
                                            placeholder="Cafe Name"
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">

                                        <input
                                            type="text"
                                            placeholder="Cafe Address"
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">

                                        <input
                                            type="text"
                                            placeholder="Phone Number"
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">

                                        <button
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">

                                            Save Business Info

                                        </button>

                                    <?php
                                        break;



                                    case 'finance':
                                    ?>

                                        <h2 class="font-semibold text-lg mb-4">Finance Settings</h2>

                                        <label class="block text-sm mb-1">Currency</label>

                                        <select
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">

                                            <option>IDR</option>
                                            <option>USD</option>

                                        </select>


                                        <label class="block text-sm mb-1">Tax (%)</label>

                                        <input
                                            type="number"
                                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">


                                        <button
                                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">

                                            Save Finance Settings

                                        </button>

                                    <?php
                                        break;



                                    case 'theme':
                                    ?>

                                        <h2 class="font-semibold text-lg mb-4">Theme</h2>

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



    <div
        id="global-tooltip"
        class="fixed hidden px-2 py-1 text-xs text-white bg-black rounded shadow-lg whitespace-nowrap z-[9999] pointer-events-none">
    </div>



    <?php require __DIR__ . '/../../resources/components/toast.php'; ?>

    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>

    <script>
        function settingsPage() {

            return {

                tab: 'profile'

            }

        }
    </script>


</body>

</html>
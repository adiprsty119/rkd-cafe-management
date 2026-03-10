<?php

require $_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/middleware/AuthMiddleware.php';

/* ==========================
   LANGUAGE SYSTEM
========================== */

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

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $pageTitle ?></title>

    <!-- Tailwind -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

        <!-- OVERLAY -->
        <div
            x-show="sidebarOpen"
            @click="sidebarOpen=false"
            class="fixed inset-0 bg-black/40 z-30 md:hidden">
        </div>

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col min-w-0 md:ml-0 transition-all duration-300">


            <!-- NAVBAR -->
            <div class="p-4 border-t border-gray-700">

                <?php require __DIR__ . '/../../resources/components/navbar.php'; ?>

            </div>

            <!-- BREADCRUMB NAVIGATION -->
            <?php require __DIR__ . '/../../resources/components/breadcrumb.php'; ?>

            <!-- PAGE CONTENT -->
            <main class="flex-1 p-4 md:p-6 space-y-6 overflow-y-auto">

                <!-- HEADER -->
                <div class="flex justify-between items-center">

                    <div>
                        <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>
                        <p class="text-sm text-gray-500"><?= $t['manage_menu'] ?? 'Manage cafe menu items' ?></p>
                    </div>

                    <button
                        class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg shadow">

                        <i class="fa-solid fa-plus mr-2"></i>
                        <?= $t['add_menu'] ?? 'Add Menu' ?>

                    </button>

                </div>



                <!-- SEARCH + FILTER -->
                <div class="flex flex-wrap gap-4">

                    <input
                        type="text"
                        placeholder="<?= $t['search_menu'] ?? 'Search menu...' ?>"
                        class="border rounded-lg px-4 py-2 w-64 focus:ring-2 focus:ring-amber-400 dark:bg-gray-800">

                    <select class="border rounded-lg px-4 py-2 dark:bg-gray-800">

                        <option><?= $t['all_categories'] ?? 'All Categories' ?></option>
                        <option>Coffee</option>
                        <option>Bakery</option>

                    </select>

                    <select class="border rounded-lg px-4 py-2 dark:bg-gray-800">

                        <option><?= $t['status'] ?? 'Status' ?></option>
                        <option>Available</option>
                        <option>Unavailable</option>

                    </select>

                </div>



                <!-- MENU TABLE -->
                <div class="bg-white rounded-xl shadow-xl dark:bg-gray-800 overflow-x-auto">

                    <div class="p-4 border-b font-semibold dark:bg-gray-700">
                        <?= $t['menu_list'] ?? 'Menu List' ?>
                    </div>

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="p-3 text-left"><?= $t['image'] ?? 'Image' ?></th>
                                <th class="p-3 text-left"><?= $t['menu'] ?? 'Menu' ?></th>
                                <th class="p-3 text-left"><?= $t['category'] ?? 'Category' ?></th>
                                <th class="p-3 text-left"><?= $t['price'] ?? 'Price' ?></th>
                                <th class="p-3 text-left"><?= $t['status'] ?? 'Status' ?></th>
                                <th class="p-3 text-left"><?= $t['action'] ?? 'Action' ?></th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr class="border-t">

                                <td class="p-3">

                                    <img
                                        src="/rkd-cafe/public/assets/images/latte.png"
                                        class="w-10 h-10 rounded object-cover">

                                </td>

                                <td class="p-3 font-semibold">
                                    Latte
                                </td>

                                <td class="p-3">
                                    Coffee
                                </td>

                                <td class="p-3">
                                    Rp 30.000
                                </td>

                                <td class="p-3">

                                    <span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">
                                        Available
                                    </span>

                                </td>

                                <td class="p-3 space-x-2">

                                    <button class="text-blue-600 hover:underline">
                                        Edit
                                    </button>

                                    <button class="text-red-600 hover:underline">
                                        Delete
                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

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

</body>

</html>
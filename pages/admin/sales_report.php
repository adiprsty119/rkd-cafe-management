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
    x-data="{ dark: localStorage.theme === 'dark', loading:false, loadingTheme:false, sidebarOpen:true }"
    @toggle-theme.window="loadingTheme = true; setTimeout(() => {let newTheme = !dark; localStorage.theme = newTheme ? 'dark' : 'light'; location.reload();}, 800)"
    x-init="document.documentElement.classList.toggle('dark', dark)"
    :class="{ 'dark': dark }"
    class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-300">


    <div class="flex min-h-screen">


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

                    <div class="w-full px-4 mt-3 transition-all duration-300 bg-gray-100 dark:bg-gray-800">
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
                <div class="flex justify-between items-center">

                    <div>
                        <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>
                        <p class="text-sm text-gray-500"><?= $t['sales_summary'] ?? 'View cafe sales performance' ?></p>
                    </div>

                    <button
                        class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg shadow">

                        <i class="fa-solid fa-download mr-2"></i>
                        <?= $t['export'] ?? 'Export Report' ?>

                    </button>

                </div>



                <!-- FILTER -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex flex-wrap gap-4">

                    <input
                        type="date"
                        class="border rounded-lg px-4 py-2 dark:bg-gray-700">

                    <input
                        type="date"
                        class="border rounded-lg px-4 py-2 dark:bg-gray-700">

                    <button
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">

                        <?= $t['filter'] ?? 'Filter' ?>

                    </button>

                </div>



                <!-- SUMMARY CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm"><?= $t['total_sales'] ?? 'Total Sales' ?></p>

                        <h2 class="text-2xl font-bold mt-2">
                            Rp 8.200.000
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm"><?= $t['total_orders'] ?? 'Total Orders' ?></p>

                        <h2 class="text-2xl font-bold mt-2">
                            124
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm"><?= $t['best_seller'] ?? 'Best Seller' ?></p>

                        <h2 class="text-2xl font-bold mt-2">
                            Latte
                        </h2>

                    </div>

                </div>



                <!-- SALES TABLE -->
                <div class="bg-white rounded-xl shadow-xl dark:bg-gray-800 overflow-x-auto">

                    <div class="p-4 border-b font-semibold dark:bg-gray-700">

                        <?= $t['sales_details'] ?? 'Sales Details' ?>

                    </div>


                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="p-3 text-left"><?= $t['order_id'] ?? 'Order ID' ?></th>
                                <th class="p-3 text-left"><?= $t['menu'] ?? 'Menu' ?></th>
                                <th class="p-3 text-left"><?= $t['quantity'] ?? 'Qty' ?></th>
                                <th class="p-3 text-left"><?= $t['total'] ?? 'Total' ?></th>
                                <th class="p-3 text-left"><?= $t['date'] ?? 'Date' ?></th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700">

                                <td class="p-3 font-semibold">
                                    #ORD001
                                </td>

                                <td class="p-3">
                                    Latte
                                </td>

                                <td class="p-3">
                                    2
                                </td>

                                <td class="p-3">
                                    Rp 60.000
                                </td>

                                <td class="p-3">
                                    2026-03-10
                                </td>

                            </tr>


                            <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700">

                                <td class="p-3 font-semibold">
                                    #ORD002
                                </td>

                                <td class="p-3">
                                    Croissant
                                </td>

                                <td class="p-3">
                                    1
                                </td>

                                <td class="p-3">
                                    Rp 25.000
                                </td>

                                <td class="p-3">
                                    2026-03-10
                                </td>

                            </tr>


                        </tbody>

                    </table>

                </div>


            </main>

        </div>

    </div>


    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>
    <script src="/rkd-cafe/public/assets/js/header.js"></script>

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
</body>

</html>
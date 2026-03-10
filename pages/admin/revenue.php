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

    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">


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
                        <p class="text-sm text-gray-500"><?= $t['revenue_overview'] ?? 'Cafe revenue overview' ?></p>
                    </div>

                </div>



                <!-- SUMMARY CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm"><?= $t['today_revenue'] ?? 'Today Revenue' ?></p>

                        <h2 class="text-2xl font-bold mt-2 text-green-600">
                            Rp 1.250.000
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm"><?= $t['weekly_revenue'] ?? 'Weekly Revenue' ?></p>

                        <h2 class="text-2xl font-bold mt-2 text-blue-600">
                            Rp 7.800.000
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm"><?= $t['monthly_revenue'] ?? 'Monthly Revenue' ?></p>

                        <h2 class="text-2xl font-bold mt-2 text-purple-600">
                            Rp 28.600.000
                        </h2>

                    </div>


                </div>



                <!-- REVENUE CHART PLACEHOLDER -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                    <h2 class="font-semibold mb-4">
                        <?= $t['revenue_trend'] ?? 'Revenue Trend' ?>
                    </h2>

                    <div class="h-64 flex items-center justify-center text-gray-400">

                        <i class="fa-solid fa-chart-line text-4xl"></i>

                    </div>

                    <p class="text-center text-sm mt-3 text-gray-400">
                        Chart will be displayed here
                    </p>

                </div>



                <!-- TOP SELLING MENU -->
                <div class="bg-white rounded-xl shadow-xl dark:bg-gray-800 overflow-x-auto">

                    <div class="p-4 border-b font-semibold dark:bg-gray-700">

                        <?= $t['top_selling_menu'] ?? 'Top Selling Menu' ?>

                    </div>


                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="p-3 text-left"><?= $t['menu'] ?? 'Menu' ?></th>
                                <th class="p-3 text-left"><?= $t['orders'] ?? 'Orders' ?></th>
                                <th class="p-3 text-left"><?= $t['revenue'] ?? 'Revenue' ?></th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700">

                                <td class="p-3 font-semibold">
                                    Latte
                                </td>

                                <td class="p-3">
                                    56
                                </td>

                                <td class="p-3 text-green-600 font-semibold">
                                    Rp 1.680.000
                                </td>

                            </tr>


                            <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700">

                                <td class="p-3 font-semibold">
                                    Croissant
                                </td>

                                <td class="p-3">
                                    41
                                </td>

                                <td class="p-3 text-green-600 font-semibold">
                                    Rp 1.025.000
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
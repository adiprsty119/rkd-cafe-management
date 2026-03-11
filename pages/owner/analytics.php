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
require_once __DIR__ . '/../../app/services/analytics_service.php';

$role = $_SESSION['role'] ?? 'guest';

/* ==========================
   MENU ENGINE
========================== */

$menus = getMenusByRole($role);
$currentMenu = findMenuByRoute($menus);
$pageTitle = $currentMenu['menu']['title'] ?? 'Dashboard';
$breadcrumb = generateBreadcrumb($currentMenu);


/* ==========================
   ANALYTICS ENGINE
========================== */

$analytics = getDashboardAnalytics();

$totalRevenue = $analytics['total_revenue'] ?? 0;
$totalOrders = $analytics['total_orders'] ?? 0;
$activeCustomers = $analytics['active_customers'] ?? 0;
$avgOrder = $analytics['avg_order'] ?? 0;

$topMenu = getTopMenu();

$salesTrend = getSalesTrend();

$customerInsight = getCustomerInsight();

$productProfit = getProductProfit();

$salesPrediction = getSalesPrediction();
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

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Alpine JS -->
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


    <div class="flex min-h-screen relative">


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

            <!-- PAGE CONTENT -->
            <main class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6">


                <!-- HEADER -->
                <div>

                    <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>

                    <p class="text-sm text-gray-500">
                        Business performance insights
                    </p>

                </div>



                <!-- KEY METRICS -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm">Total Revenue</p>

                        <h2 class="text-2xl font-bold mt-2">
                            Rp. <?= number_format($totalRevenue, 0, ',', '.') ?>
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm">Total Orders</p>

                        <h2 class="text-2xl font-bold mt-2">
                            <?= $totalOrders ?>
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm">Active Customers</p>

                        <h2 class="text-2xl font-bold mt-2">
                            <?= $activeCustomers ?>
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm">Average Order</p>

                        <h2 class="text-2xl font-bold mt-2">
                            Rp. <?= number_format($avgOrder, 0, ',', '.') ?>
                        </h2>

                    </div>

                </div>



                <!-- ANALYTICS WIDGETS -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- SALES TREND -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                        <h2 class="font-semibold mb-4">
                            Sales Trend
                        </h2>

                        <div class="h-64">
                            <canvas id="salesChart"></canvas>
                        </div>

                    </div>



                    <!-- CUSTOMER INSIGHT -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                        <h2 class="font-semibold mb-4">
                            Customer Insight
                        </h2>

                        <div class="h-64">
                            <canvas id="customerChart"></canvas>
                        </div>

                    </div>



                    <!-- PRODUCT PROFIT ANALYSIS -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                        <h2 class="font-semibold mb-4">
                            Product Profit Analysis
                        </h2>

                        <div class="h-64">
                            <canvas id="profitChart"></canvas>
                        </div>

                    </div>



                    <!-- SALES PREDICTION -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                        <h2 class="font-semibold mb-4">
                            Sales Prediction (AI)
                        </h2>

                        <div class="h-64">
                            <canvas id="predictionChart"></canvas>
                        </div>

                    </div>

                </div>


                <!-- TOP SELLING MENU -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow">

                    <!-- HEADER -->
                    <div class="flex items-center justify-between p-5 border-b dark:border-gray-700">

                        <div>
                            <h2 class="font-semibold text-lg flex items-center gap-2">
                                <i class="fa-solid fa-fire text-orange-500"></i>
                                Top Selling Menu
                            </h2>

                            <p class="text-xs text-gray-500 mt-1">
                                Best performing products based on orders
                            </p>
                        </div>

                    </div>


                    <!-- TABLE -->
                    <div class="overflow-x-auto">

                        <table class="w-full text-sm">

                            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">

                                <tr>

                                    <th class="p-4 text-left font-semibold">
                                        Menu
                                    </th>

                                    <th class="p-4 text-left font-semibold">
                                        Orders
                                    </th>

                                    <th class="p-4 text-left font-semibold">
                                        Revenue
                                    </th>

                                </tr>

                            </thead>

                            <tbody class="divide-y dark:divide-gray-700">

                                <?php $rank = 1; ?>

                                <?php foreach ($topMenu as $menu): ?>

                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                                        <!-- MENU NAME -->
                                        <td class="p-4 font-semibold flex items-center gap-3">

                                            <!-- RANK BADGE -->
                                            <span class="w-7 h-7 flex items-center justify-center rounded-full bg-orange-100 text-orange-600 text-xs font-bold">
                                                <?= $rank ?>
                                            </span>

                                            <?= $menu['name'] ?>

                                        </td>


                                        <!-- ORDERS -->
                                        <td class="p-4">

                                            <span class="px-2 py-1 rounded-md bg-blue-50 text-blue-600 text-xs font-medium">
                                                <?= $menu['orders'] ?> orders
                                            </span>

                                        </td>


                                        <!-- REVENUE -->
                                        <td class="p-4">

                                            <span class="px-2 py-1 rounded-md bg-green-50 text-green-600 text-xs font-semibold">
                                                Rp <?= number_format($menu['revenue'], 0, ',', '.') ?>
                                            </span>

                                        </td>

                                    </tr>

                                    <?php $rank++; ?>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

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
        window.analyticsData = {

            salesTrend: <?= json_encode($salesTrend ?? []) ?>,
            customerInsight: <?= json_encode($customerInsight ?? []) ?>,
            productProfit: <?= json_encode($productProfit ?? []) ?>,
            salesPrediction: <?= json_encode($salesPrediction ?? []) ?>

        };
    </script>

    <script src="/rkd-cafe/public/assets/js/analytics_menu.js"></script>

</body>

</html>
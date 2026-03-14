<?php

require $_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/middleware/AuthMiddleware.php';

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
$pageTitle = $currentMenu['menu']['title'] ?? 'Analytics Dashboard';
$breadcrumb = generateBreadcrumb($currentMenu);


/* ==========================
   ANALYTICS DATA
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

$paymentDistribution = getPaymentDistribution();
$customerGrowth = getCustomerGrowth();

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $pageTitle ?></title>

    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <link href="/rkd-cafe/public/assets/css/utilities.css" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>

<body
    x-data="{dark:localStorage.theme==='dark',sidebarOpen:true}"
    x-init="document.documentElement.classList.toggle('dark',dark)"
    :class="{ 'dark': dark }"
    class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white transition">

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside :class="sidebarOpen ? 'w-64':'w-20'"
            class="hidden md:flex flex-col bg-white dark:bg-gray-800 transition-all">

            <?php require __DIR__ . '/../../resources/components/sidebar.php'; ?>

        </aside>

        <!-- MAIN -->
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

                <div class="flex flex-col md:flex-row md:items-center md:justify-between">

                    <div>
                        <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>
                        <p class="text-sm text-gray-500">Business Intelligence Dashboard</p>
                    </div>

                    <div class="flex gap-2 mt-3 md:mt-0">

                        <button class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded">Today</button>
                        <button class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded">7 Days</button>
                        <button class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded">30 Days</button>

                    </div>

                </div>


                <!-- KPI SECTION -->

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex items-center justify-between">

                            <p class="text-gray-500 text-sm">Total Revenue</p>

                            <i class="fa-solid fa-coins text-yellow-500"></i>

                        </div>

                        <h2 class="text-2xl font-bold mt-2">
                            Rp <?= number_format($totalRevenue, 0, ',', '.') ?>
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex justify-between">

                            <p class="text-gray-500 text-sm">Orders</p>

                            <i class="fa-solid fa-receipt text-blue-500"></i>

                        </div>

                        <h2 class="text-2xl font-bold mt-2"><?= $totalOrders ?></h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex justify-between">

                            <p class="text-gray-500 text-sm">Active Customers</p>

                            <i class="fa-solid fa-users text-purple-500"></i>

                        </div>

                        <h2 class="text-2xl font-bold mt-2"><?= $activeCustomers ?></h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex justify-between">

                            <p class="text-gray-500 text-sm">Average Order</p>

                            <i class="fa-solid fa-chart-line text-green-500"></i>

                        </div>

                        <h2 class="text-2xl font-bold mt-2">
                            Rp <?= number_format($avgOrder, 0, ',', '.') ?>
                        </h2>

                    </div>

                </div>



                <!-- CHARTS -->

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <h2 class="font-semibold mb-4">Sales Trend</h2>

                        <div class="h-64">
                            <canvas id="salesChart"></canvas>
                        </div>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <h2 class="font-semibold mb-4">Customer Insight</h2>

                        <div class="h-64">
                            <canvas id="customerChart"></canvas>
                        </div>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <h2 class="font-semibold mb-4">Sales Prediction (AI)</h2>

                        <div class="h-64">
                            <canvas id="predictionChart"></canvas>
                        </div>

                    </div>

                </div>



                <!-- SECOND ROW -->

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <h2 class="font-semibold mb-4">Product Profit Analysis</h2>

                        <div class="h-64">
                            <canvas id="profitChart"></canvas>
                        </div>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <h2 class="font-semibold mb-4">Customer Growth</h2>

                        <div class="h-64">
                            <canvas id="customerGrowthChart"></canvas>
                        </div>

                    </div>

                </div>


                <!-- PAYMENT + TOP MENU ROW -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- PAYMENT ANALYTICS -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <h2 class="font-semibold mb-4">
                            Payment Distribution
                        </h2>

                        <div class="h-64">
                            <canvas id="paymentChart"></canvas>
                        </div>

                    </div>



                    <!-- TOP SELLING MENU -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow">

                        <div class="flex items-center justify-between p-5 border-b dark:border-gray-700">

                            <h2 class="font-semibold text-lg flex gap-2 items-center">

                                <i class="fa-solid fa-fire text-orange-500"></i>

                                Top Selling Menu

                            </h2>

                        </div>

                        <div class="overflow-x-auto">

                            <table class="w-full text-sm">

                                <thead class="bg-gray-50 dark:bg-gray-700">

                                    <tr>

                                        <th class="p-4 text-left">Menu</th>
                                        <th class="p-4 text-left">Orders</th>
                                        <th class="p-4 text-left">Revenue</th>

                                    </tr>

                                </thead>

                                <tbody class="divide-y dark:divide-gray-700">

                                    <?php $rank = 1; ?>

                                    <?php foreach ($topMenu as $menu): ?>

                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">

                                            <td class="p-4 flex items-center gap-3">

                                                <span class="w-7 h-7 flex items-center justify-center rounded-full bg-orange-100 text-orange-600 text-xs font-bold">

                                                    <?= $rank ?>

                                                </span>

                                                <?= $menu['name'] ?>

                                            </td>

                                            <td class="p-4"><?= $menu['orders'] ?></td>

                                            <td class="p-4">

                                                Rp <?= number_format($menu['revenue'], 0, ',', '.') ?>

                                            </td>

                                        </tr>

                                        <?php $rank++; ?>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </main>

        </div>

    </div>

    <script>
        window.analyticsData = {

            salesTrend: <?= json_encode($salesTrend ?? []) ?>,
            customerInsight: <?= json_encode($customerInsight ?? []) ?>,
            productProfit: <?= json_encode($productProfit ?? []) ?>,
            salesPrediction: <?= json_encode($salesPrediction ?? []) ?>,
            paymentDistribution: <?= json_encode($paymentDistribution ?? []) ?>,
            customerGrowth: <?= json_encode($customerGrowth ?? []) ?>

        };
    </script>

    <script src="/rkd-cafe/public/assets/js/analytics_menu.js"></script>
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
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

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        #breadcrumbContainer {
            will-change: transform;
        }
    </style>
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

            <!-- HEADER STACK -->
            <div id="headerStack" class="sticky top-0 z-50">

                <!-- NAVBAR -->
                <div
                    id="dashboardNavbar"
                    class="relative z-50 backdrop-blur-md transition-all duration-300">

                    <div
                        class="w-full px-4 mt-3 transition-all duration-300 bg-white/40 dark:bg-gray-900/40">

                        <?php require __DIR__ . '/../../resources/components/navbar.php'; ?>

                    </div>

                </div>

                <!-- BREADCRUMB MASK -->
                <div
                    id="breadcrumbMask"
                    class="relative overflow-hidden">

                    <!-- INDICATOR -->
                    <div
                        id="breadcrumbIndicator"
                        class="absolute left-1/2 -translate-x-1/2 top-0 -mt-3 hidden transition-all duration-300">

                        <button
                            id="breadcrumbIndicatorBtn"
                            class="flex items-center gap-1 px-3 py-1 text-xs rounded-full backdrop-blur-md bg-white/40 shadow-md hover:bg-white/60 active:scale-95 transition">

                            <i class="fa-solid fa-angle-down animate-bounce"></i>
                            Show Breadcrumb

                        </button>

                    </div>

                    <!-- BREADCRUMB -->
                    <div
                        id="breadcrumbContainer"
                        class="px-4 py-2 will-change-transform">

                        <?php require __DIR__ . '/../../resources/components/breadcrumb.php'; ?>

                    </div>

                </div>

            </div>

            <main id="dashboardScroll"
                class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6">

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

    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const navbar = document.getElementById("dashboardNavbar");
            const breadcrumb = document.getElementById("breadcrumbContainer");
            const scrollContainer = document.getElementById("dashboardScroll");
            const indicator = document.getElementById("breadcrumbIndicator");

            if (!navbar || !breadcrumb || !scrollContainer) return;

            let breadcrumbLocked = false;
            let ticking = false;

            const maxMove = 90;

            function clamp(v, min, max) {
                return Math.min(Math.max(v, min), max);
            }

            function update() {

                const scrollY = scrollContainer.scrollTop;

                const progress = clamp(scrollY / 120, 0, 1);

                const move = progress * maxMove;

                if (!breadcrumbLocked) {

                    breadcrumb.style.transform =
                        `translate3d(0,-${move}px,0)`;

                }

                /* navbar glass */

                if (scrollY > 20) {

                    navbar.classList.add(
                        "backdrop-blur-xl",
                        "dark:bg-gray-900/70"
                    );

                } else {

                    navbar.classList.remove(
                        "backdrop-blur-xl",
                        "dark:bg-gray-900/70"
                    );

                }

                /* indicator */

                if (scrollY > 80) {

                    indicator.classList.remove("hidden");

                } else {

                    indicator.classList.add("hidden");

                }

                if (scrollY < 5) {
                    breadcrumbLocked = false;
                }

                ticking = false;

            }

            scrollContainer.addEventListener("scroll", () => {

                if (!ticking) {
                    requestAnimationFrame(update);
                    ticking = true;
                }

            });

            indicator.addEventListener("click", () => {

                breadcrumbLocked = true;

                breadcrumb.style.transform = "translate3d(0,0,0)";

                indicator.classList.add("hidden");

            });

        });
    </script>

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
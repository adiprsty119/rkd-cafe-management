<?php
define('APP_INIT', true);

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
$sidebarCollapsed = $_SESSION['sidebar_collapsed'] ?? 0;

/* ==========================
   MENU ENGINE
========================== */

$allMenus = $_SESSION['menu_config'][$role] ?? [];
$menus = getMenusByRole($role);
$currentMenu = findMenuByRoute($allMenus);
$pageTitle = $currentMenu['menu']['title'] ?? 'Sales Analytics';
$breadcrumb = generateBreadcrumb($currentMenu);

/* ==========================
   PERIOD CONTROL (NEW)
========================== */

$period = $_GET['period'] ?? 'today';

if (!in_array($period, ['today', '7days', '30days'])) {
    $period = 'today';
}

/* ==========================
   SALES ANALYTICS ENGINE (FINAL)
========================== */

$analytics = getDashboardAnalytics($period);

$totalRevenue = $analytics['total_revenue'] ?? 0;
$totalOrders = $analytics['total_orders'] ?? 0;
$activeCustomers = $analytics['active_customers'] ?? 0;
$avgOrder = $analytics['avg_order'] ?? 0;
$salesInsight = $analytics['sales_insight'] ?? [];
/*
|--------------------------------------------------------------------------
| IMPORTANT: STRUCTURE MUST MATCH analytics.php
|--------------------------------------------------------------------------
*/

$salesHourly = getSalesHourly($period);
$salesDaily = getSalesDaily($period);
$paymentDistribution = getPaymentDistribution($period);


/*
|--------------------------------------------------------------------------
| KPI INTELLIGENCE
|--------------------------------------------------------------------------
*/

$previous = getDashboardAnalytics("previous_" . $period);

$growthRevenue = 0;

if (($previous['total_revenue'] ?? 0) > 0) {
    $growthRevenue = (
        ($totalRevenue - $previous['total_revenue']) /
        $previous['total_revenue']
    ) * 100;
}
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $pageTitle ?></title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- Vanila CSS -->
    <link href="/rkd-cafe/public/assets/css/utilities.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Global Seacrh [global-search.js] -->
    <script defer src="/rkd-cafe/public/assets/js/global-search.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .chart-card {
            transition: all .25s ease;
        }

        .chart-card:hover {
            transform: translateY(-2px);
        }
    </style>

</head>

<body
    x-data="{ dark: localStorage.theme === 'dark', sidebarOpen:<?= isset($sidebarCollapsed) && $sidebarCollapsed ? 'false' : 'true' ?> }"
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
                <div class="flex flex-col mb-12 md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm px-6 py-5">

                    <!-- LEFT -->
                    <div class="flex items-center gap-4">

                        <!-- PAGE ICON -->
                        <div class="w-11 h-11 flex items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-500/20">

                            <i class="fa-solid fa-chart-line text-blue-600 dark:text-blue-400"></i>

                        </div>

                        <!-- TITLE -->
                        <div>

                            <h1 class="text-xl md:text-2xl font-semibold tracking-tight">
                                <?= htmlspecialchars($pageTitle) ?>
                            </h1>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <?= $t['sales_analytics_desc'] ?? 'Sales performance and transaction insights' ?>
                            </p>

                        </div>

                    </div>

                </div>

                <!-- SALES INSIGHT PANEL -->
                <div class="bg-gradient-to-r from-indigo-500 to-blue-600 text-white rounded-xl shadow p-6">

                    <div class="flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-brain"></i>
                        <h2 class="font-semibold text-lg">Sales Insight</h2>
                    </div>

                    <div class="grid md:grid-cols-2 gap-3">

                        <?php foreach ($salesInsight as $ins): ?>
                            <div class="bg-white/10 px-4 py-3 rounded-lg text-sm">
                                <?= $ins['message'] ?>
                            </div>
                        <?php endforeach; ?>

                    </div>

                </div>

                <!-- KPI CARDS -->

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex items-center justify-between">

                            <p class="text-gray-500 text-sm">Total Revenue</p>

                            <i class="fa-solid fa-coins text-yellow-500"></i>

                        </div>

                        <h2 class="text-2xl font-bold mt-2">
                            Rp. <?= number_format($totalRevenue, 0, ',', '.') ?>
                        </h2>

                        <p class="text-sm mt-1 <?= $growthRevenue >= 0 ? 'text-green-500' : 'text-red-500' ?>">
                            <?= number_format($growthRevenue, 1) ?>%
                        </p>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex justify-between">

                            <p class="text-gray-500 text-sm">Total Orders</p>

                            <i class="fa-solid fa-receipt text-blue-500"></i>

                        </div>

                        <h2 class="text-2xl font-bold mt-2">
                            <?= $totalOrders ?>
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex justify-between">

                            <p class="text-gray-500 text-sm">Active Customers</p>

                            <i class="fa-solid fa-users text-purple-500"></i>

                        </div>

                        <h2 class="text-2xl font-bold mt-2">
                            <?= $activeCustomers ?>
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex justify-between">

                            <p class="text-gray-500 text-sm">Average Order</p>

                            <i class="fa-solid fa-chart-line text-green-500"></i>

                        </div>

                        <h2 class="text-2xl font-bold mt-2">
                            Rp. <?= number_format($avgOrder, 0, ',', '.') ?>
                        </h2>

                    </div>

                </div>


                <!-- SALES ANALYTICS WIDGETS -->
                <!-- ROW 1 : HOURLY + DAILY -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- HOURLY -->
                    <?php
                    $title = "Hourly Sales";
                    $chartId = "hourlySalesChart";
                    $insight = $salesHourly['insight'] ?? '';
                    require __DIR__ . '/../../resources/components/chart-card.php';
                    ?>

                    <!-- DAILY -->
                    <?php
                    $title = "Daily Sales";
                    $chartId = "dailySalesChart";
                    $insight = $salesDaily['insight'] ?? '';
                    require __DIR__ . '/../../resources/components/chart-card.php';
                    ?>

                </div>



                <!-- ROW 2 : PAYMENT DISTRIBUTION (FULL WIDTH) -->
                <div class="mt-6">

                    <?php
                    $title = "Payment Distribution";
                    $chartId = "paymentChart";
                    $insight = $paymentDistribution['insight'] ?? '';
                    require __DIR__ . '/../../resources/components/chart-card.php';
                    ?>

                </div>


            </main>

        </div>

    </div>



    <?php require __DIR__ . '/../../resources/components/toast.php'; ?>

    <script>
        window.salesAnalytics = {

            salesHourly: <?= json_encode($salesHourly ?? []) ?>,
            salesDaily: <?= json_encode($salesDaily ?? []) ?>,
            paymentDistribution: <?= json_encode($paymentDistribution ?? []) ?>

        };
    </script>

    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/analytics_sales.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>
    <script src="/rkd-cafe/public/assets/js/header.js"></script>
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

</body>

</html>
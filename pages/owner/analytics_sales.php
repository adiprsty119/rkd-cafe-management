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
$pageTitle = $currentMenu['menu']['title'] ?? 'Sales Analytics';
$breadcrumb = generateBreadcrumb($currentMenu);


/* ==========================
   SALES ANALYTICS ENGINE
========================== */

$analytics = getDashboardAnalytics();

$totalRevenue = $analytics['total_revenue'] ?? 0;
$totalOrders = $analytics['total_orders'] ?? 0;
$activeCustomers = $analytics['active_customers'] ?? 0;
$avgOrder = $analytics['avg_order'] ?? 0;

$salesHourly = getSalesHourly();
$salesDaily = getSalesDaily();
$paymentDistribution = getPaymentDistribution();

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
    </style>

</head>

<body
    x-data="{ dark: localStorage.theme === 'dark', sidebarOpen:true }"
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

                <div>

                    <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>

                    <p class="text-sm text-gray-500">
                        Sales performance and transaction insights
                    </p>

                </div>



                <!-- KPI CARDS -->

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


                <!-- SALES ANALYTICS WIDGETS -->
                <!-- ROW 1 : HOURLY + DAILY -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- HOURLY SALES -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                        <h2 class="font-semibold mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-clock text-indigo-500"></i>
                            Hourly Sales
                        </h2>

                        <div class="h-64">
                            <canvas id="hourlySalesChart"></canvas>
                        </div>

                    </div>


                    <!-- DAILY SALES -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                        <h2 class="font-semibold mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-day text-green-500"></i>
                            Daily Sales
                        </h2>

                        <div class="h-64">
                            <canvas id="dailySalesChart"></canvas>
                        </div>

                    </div>

                </div>



                <!-- ROW 2 : PAYMENT DISTRIBUTION (FULL WIDTH) -->
                <div class="mt-6">

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                        <h2 class="font-semibold mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-credit-card text-orange-500"></i>
                            Payment Distribution
                        </h2>

                        <div class="h-72">
                            <canvas id="paymentChart"></canvas>
                        </div>

                    </div>

                </div>


            </main>

        </div>

    </div>



    <?php require __DIR__ . '/../../resources/components/toast.php'; ?>


    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>


    <script>
        window.salesAnalytics = {

            salesHourly: <?= json_encode($salesHourly ?? []) ?>,
            salesDaily: <?= json_encode($salesDaily ?? []) ?>,
            paymentDistribution: <?= json_encode($paymentDistribution ?? []) ?>

        };
    </script>


    <script src="/rkd-cafe/public/assets/js/analytics_sales.js"></script>
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
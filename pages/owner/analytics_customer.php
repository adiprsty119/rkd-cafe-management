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
$pageTitle = $currentMenu['menu']['title'] ?? 'Customer Analytics';
$breadcrumb = generateBreadcrumb($currentMenu);


/* ==========================
   CUSTOMER ANALYTICS ENGINE
========================== */

$customerInsight = getCustomerInsight();
$customerGrowth = getCustomerGrowth();
$customerLifetime = getCustomerLifetime();

$totalCustomers = count($customerInsight);

$avgSpend = 0;
$avgOrders = 0;

if (!empty($customerInsight)) {

    $totalSpend = array_sum(array_column($customerInsight, 'total_spend'));
    $totalOrders = array_sum(array_column($customerInsight, 'orders'));

    $avgSpend = $totalSpend / $totalCustomers;
    $avgOrders = $totalOrders / $totalCustomers;
}

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

</head>

<body
    x-data="{ dark: localStorage.theme === 'dark', sidebarOpen:true }"
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

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

            <!-- NAVBAR -->
            <div class="p-4 border-t border-gray-700">

                <?php require __DIR__ . '/../../resources/components/navbar.php'; ?>

            </div>

            <?php require __DIR__ . '/../../resources/components/breadcrumb.php'; ?>

            <main class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6">

                <!-- HEADER -->

                <div>

                    <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>

                    <p class="text-sm text-gray-500">
                        Customer behavior and loyalty insights
                    </p>

                </div>



                <!-- KPI -->

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm">Total Customers</p>

                        <h2 class="text-2xl font-bold mt-2">
                            <?= $totalCustomers ?>
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm">Active Customers</p>

                        <h2 class="text-2xl font-bold mt-2">
                            <?= $totalCustomers ?>
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm">Average Spend</p>

                        <h2 class="text-2xl font-bold mt-2">
                            Rp <?= number_format($avgSpend, 0, ',', '.') ?>
                        </h2>

                    </div>


                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <p class="text-gray-500 text-sm">Average Orders</p>

                        <h2 class="text-2xl font-bold mt-2">
                            <?= number_format($avgOrders, 1) ?>
                        </h2>

                    </div>

                </div>



                <!-- CHARTS -->

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- CUSTOMER GROWTH -->

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                        <h2 class="font-semibold mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-user-plus text-indigo-500"></i>
                            Customer Growth
                        </h2>

                        <div class="h-64">
                            <canvas id="customerGrowthChart"></canvas>
                        </div>

                    </div>


                    <!-- CUSTOMER ORDERS -->

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                        <h2 class="font-semibold mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-users text-green-500"></i>
                            Customer Orders
                        </h2>

                        <div class="h-64">
                            <canvas id="customerOrdersChart"></canvas>
                        </div>

                    </div>

                </div>



                <!-- CUSTOMER LIFETIME VALUE -->

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

                    <h2 class="font-semibold mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-coins text-yellow-500"></i>
                        Customer Lifetime Value
                    </h2>

                    <div class="h-72">
                        <canvas id="customerLifetimeChart"></canvas>
                    </div>

                </div>

            </main>

        </div>

    </div>


    <script>
        window.customerAnalytics = {

            customerInsight: <?= json_encode($customerInsight ?? []) ?>,
            customerGrowth: <?= json_encode($customerGrowth ?? []) ?>,
            customerLifetime: <?= json_encode($customerLifetime ?? []) ?>

        };
    </script>


    <script src="/rkd-cafe/public/assets/js/analytics_customer.js"></script>

</body>

</html>
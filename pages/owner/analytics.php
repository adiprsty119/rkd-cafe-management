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


/* ==========================
    ANALYTICS ENGINE
========================== */

$analytics = json_decode(
    file_get_contents("http://localhost:5001/analytics"),
    true
);

$totalRevenue = $analytics['total_revenue'] ?? 0;
$totalOrders = $analytics['total_orders'] ?? 0;
$activeCustomers = $analytics['active_customers'] ?? 0;
$avgOrder = $analytics['avg_order'] ?? 0;

$topMenu = json_decode(
    file_get_contents("http://localhost:5001/top-menu"),
    true
);

$salesTrend = json_decode(
    file_get_contents("http://localhost:5001/sales-trend"),
    true
);

$customerInsight = json_decode(
    file_get_contents("http://localhost:5001/customer-insight"),
    true
);

$productProfit = json_decode(
    file_get_contents("http://localhost:5001/product-profit"),
    true
);

$salesPrediction = json_decode(
    file_get_contents("http://localhost:5001/sales-prediction"),
    true
);
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


                <!-- MENU POPULARITY -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">

                    <div class="p-4 border-b font-semibold dark:bg-gray-700">

                        Top Selling Menu

                    </div>

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="p-3 text-left">Menu</th>
                                <th class="p-3 text-left">Orders</th>
                                <th class="p-3 text-left">Revenue</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($topMenu as $menu): ?>

                                <tr class="border-t">

                                    <td class="p-3 font-semibold">
                                        <?= $menu['name'] ?>
                                    </td>

                                    <td class="p-3">
                                        <?= $menu['orders'] ?>
                                    </td>

                                    <td class="p-3 text-green-600">
                                        Rp <?= number_format($menu['revenue'], 0, ',', '.') ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>



                <!-- CUSTOMER INSIGHT -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">

                    <div class="p-4 border-b font-semibold dark:bg-gray-700">

                        Customer Insight

                    </div>

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="p-3 text-left">Customer</th>
                                <th class="p-3 text-left">Orders</th>
                                <th class="p-3 text-left">Total Spend</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr class="border-t">

                                <td class="p-3">
                                    Andi
                                </td>

                                <td class="p-3">
                                    24
                                </td>

                                <td class="p-3 text-green-600">
                                    Rp 1.200.000
                                </td>

                            </tr>

                            <tr class="border-t">

                                <td class="p-3">
                                    Budi
                                </td>

                                <td class="p-3">
                                    18
                                </td>

                                <td class="p-3 text-green-600">
                                    Rp 950.000
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

    <script>
        const salesTrend = <?= json_encode($salesTrend); ?>;

        /* ==========================
           FORMAT TANGGAL
        ========================== */

        const labels = salesTrend.map(item => {

            const date = new Date(item.date);

            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short'
            });

        });

        const revenue = salesTrend.map(item => item.revenue);


        /* ==========================
           GRADIENT BACKGROUND
        ========================== */

        const ctx = document.getElementById('salesChart').getContext('2d');

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);

        gradient.addColorStop(0, 'rgba(245,158,11,0.45)');
        gradient.addColorStop(1, 'rgba(245,158,11,0)');


        /* ==========================
           CHART CONFIG
        ========================== */

        new Chart(ctx, {

            type: 'line',

            data: {

                labels: labels,

                datasets: [{

                    label: 'Revenue',

                    data: revenue,

                    borderColor: '#f59e0b',

                    backgroundColor: gradient,

                    borderWidth: 3,

                    tension: 0.35,

                    fill: true,

                    pointRadius: 5,

                    pointBackgroundColor: '#f59e0b'

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                /* ==========================
                   ANIMASI
                ========================== */

                animation: {

                    duration: 1200,

                    easing: 'easeOutQuart'

                },

                plugins: {

                    legend: {

                        display: true,

                        labels: {

                            usePointStyle: true

                        }

                    },

                    /* ==========================
                       FORMAT TOOLTIP RUPIAH
                    ========================== */

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                return "Rp " + context.raw.toLocaleString('id-ID');

                            }

                        }

                    }

                },

                /* ==========================
                   SKALA Y
                ========================== */

                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {

                            callback: function(value) {

                                return "Rp " + value.toLocaleString('id-ID');

                            }

                        }

                    },

                    x: {

                        grid: {

                            display: false

                        }

                    }

                }

            }

        });

        /* ==========================
       CUSTOMER INSIGHT DATA
    ========================== */

        const customerInsight = <?= json_encode($customerInsight ?? []); ?>;

        const customerLabels = customerInsight.map(c => c.name);

        const customerOrders = customerInsight.map(c => c.orders);



        /* ==========================
           CUSTOMER CHART GRADIENT
        ========================== */

        const ctxCustomer = document.getElementById('customerChart').getContext('2d');

        const gradientCustomer = ctxCustomer.createLinearGradient(0, 0, 0, 300);

        gradientCustomer.addColorStop(0, 'rgba(59,130,246,0.6)');
        gradientCustomer.addColorStop(1, 'rgba(59,130,246,0.1)');



        /* ==========================
           CUSTOMER INSIGHT CHART
        ========================== */

        new Chart(ctxCustomer, {

            type: 'bar',

            data: {

                labels: customerLabels,

                datasets: [{

                    label: 'Orders',

                    data: customerOrders,

                    backgroundColor: gradientCustomer,

                    borderRadius: 8,

                    borderWidth: 0

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                animation: {

                    duration: 1200,

                    easing: 'easeOutQuart'

                },

                plugins: {

                    legend: {

                        display: true,

                        labels: {
                            usePointStyle: true
                        }

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                return context.raw + " Orders";

                            }

                        }

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {

                            stepSize: 1

                        }

                    },

                    x: {

                        grid: {
                            display: false
                        }

                    }

                }

            }

        });

        /* ==========================
   PRODUCT PROFIT DATA
========================== */

        const productProfit = <?= json_encode($productProfit ?? []) ?>;

        const productLabels = productProfit.map(p => p.name);

        const profitValues = productProfit.map(p => p.profit);


        /* ==========================
           PRODUCT PROFIT GRADIENT
        ========================== */

        const ctxProfit = document.getElementById('profitChart').getContext('2d');

        const gradientProfit = ctxProfit.createLinearGradient(0, 0, 400, 0);

        gradientProfit.addColorStop(0, 'rgba(16,185,129,0.7)');
        gradientProfit.addColorStop(1, 'rgba(16,185,129,0.2)');


        /* ==========================
           PRODUCT PROFIT CHART
        ========================== */

        new Chart(ctxProfit, {

            type: 'bar',

            data: {

                labels: productLabels,

                datasets: [{

                    label: 'Profit',

                    data: profitValues,

                    backgroundColor: gradientProfit,

                    borderRadius: 10,

                    borderSkipped: false

                }]

            },

            options: {

                indexAxis: 'y',

                responsive: true,

                maintainAspectRatio: false,

                animation: {

                    duration: 1200,

                    easing: 'easeOutQuart'

                },

                plugins: {

                    legend: {

                        display: true,

                        labels: {
                            usePointStyle: true
                        }

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                return "Rp " + context.raw.toLocaleString('id-ID');

                            }

                        }

                    }

                },

                scales: {

                    x: {

                        ticks: {

                            callback: function(value) {

                                return "Rp " + value.toLocaleString('id-ID');

                            }

                        },

                        grid: {

                            color: "rgba(200,200,200,0.1)"

                        }

                    },

                    y: {

                        grid: {

                            display: false

                        }

                    }

                }

            }

        });

        /* ==========================
   SALES PREDICTION DATA
========================== */

        const predictionData = <?= json_encode($salesPrediction ?? []) ?>;

        const predictionLabels = predictionData.map(p => {

            const date = new Date(p.date);

            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short'
            });

        });

        const predictionRevenue = predictionData.map(p => p.revenue);


        /* ==========================
           PREDICTION CHART GRADIENT
        ========================== */

        const ctxPrediction = document.getElementById('predictionChart').getContext('2d');

        const gradientPrediction = ctxPrediction.createLinearGradient(0, 0, 0, 300);

        gradientPrediction.addColorStop(0, 'rgba(139,92,246,0.45)');
        gradientPrediction.addColorStop(1, 'rgba(139,92,246,0)');


        /* ==========================
           SALES PREDICTION CHART
        ========================== */

        new Chart(ctxPrediction, {

            type: 'line',

            data: {

                labels: predictionLabels,

                datasets: [

                    {
                        label: 'Predicted Revenue',

                        data: predictionRevenue,

                        borderColor: '#8b5cf6',

                        backgroundColor: gradientPrediction,

                        borderDash: [8, 6],

                        borderWidth: 3,

                        tension: 0.35,

                        fill: true,

                        pointRadius: 5,

                        pointBackgroundColor: '#8b5cf6'
                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                animation: {

                    duration: 1200,

                    easing: 'easeOutQuart'

                },

                plugins: {

                    legend: {

                        display: true,

                        labels: {
                            usePointStyle: true
                        }

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                return "Rp " + context.raw.toLocaleString('id-ID');

                            }

                        }

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {

                            callback: function(value) {

                                return "Rp " + value.toLocaleString('id-ID');

                            }

                        }

                    },

                    x: {

                        grid: {

                            display: false

                        }

                    }

                }

            }

        });
    </script>
</body>

</html>
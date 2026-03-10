<?php

require __DIR__ . '/../../../middleware/AuthMiddleware.php';

/* ==========================
   ROLE VALIDATION
========================== */

if ($_SESSION['role'] !== 'owner') {
    header("Location: /rkd-cafe/resources/views/auth/login.php");
    exit;
}

/* ==========================
   LANGUAGE SYSTEM
========================== */

$lang = $_GET['lang'] ?? 'id';

if (!in_array($lang, ['id', 'en'])) {
    $lang = 'id';
}

$t = require __DIR__ . '/../../lang/' . $lang . '.php';

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $t['site_title'] ?> - Owner</title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>

<body
    x-data="{ dark: localStorage.theme === 'dark', loading:false, loadingTheme:false, sidebarOpen:<?= isset($sidebarCollapsed) && $sidebarCollapsed ? 'false' : 'true' ?> }"
    @toggle-theme.window="loadingTheme = true; setTimeout(() => {let newTheme = !dark; localStorage.theme = newTheme ? 'dark' : 'light'; location.reload();}, 800)"
    x-init=" document.documentElement.classList.toggle('dark', dark)"
    :class="{ 'dark': dark }"
    class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-300">

    <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900 relative">

        <!-- SIDEBAR -->
        <aside
            :class="sidebarOpen ? 'w-64' : 'w-20'"
            class="hidden md:flex flex-col h-screen bg-white dark:bg-gray-800 transition-all duration-300 overflow-x-hidden">

            <?php require __DIR__ . '/../../components/sidebar.php'; ?>

        </aside>

        <!-- MOBILE SIDEBAR -->
        <aside
            x-show="sidebarOpen"
            x-transition
            class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 z-40 md:hidden">

            <?php require __DIR__ . '/../../components/sidebar.php'; ?>

        </aside>

        <!-- OVERLAY -->
        <div
            x-show="sidebarOpen"
            @click="sidebarOpen=false"
            class="fixed inset-0 bg-black/40 z-30 md:hidden">
        </div>


        <!-- MAIN -->
        <div class="flex-1 flex flex-col min-w-0 md:ml-0 transition-all duration-300">


            <!-- NAVBAR -->
            <div class="p-4 border-b dark:border-gray-700">

                <?php require __DIR__ . '/../../components/navbar.php'; ?>

            </div>

            <!-- DASHBOARD CONTENT -->
            <main class="p-4 md:p-6 space-y-6 overflow-y-auto">


                <!-- STATISTIC CARDS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

                    <!-- TOTAL REVENUE -->
                    <div class="bg-white p-6 rounded-xl shadow dark:bg-gray-800">

                        <div class="flex justify-between items-center">

                            <div>

                                <p class="text-gray-500 text-sm">
                                    Total Revenue
                                </p>

                                <h2 class="text-2xl font-bold">
                                    Rp 82.000.000
                                </h2>

                            </div>

                            <i class="fa-solid fa-money-bill-wave text-green-500 text-2xl"></i>

                        </div>

                    </div>


                    <!-- TOTAL ORDERS -->
                    <div class="bg-white p-6 rounded-xl shadow dark:bg-gray-800">

                        <div class="flex justify-between items-center">

                            <div>

                                <p class="text-gray-500 text-sm">
                                    Total Orders
                                </p>

                                <h2 class="text-2xl font-bold">
                                    1.245
                                </h2>

                            </div>

                            <i class="fa-solid fa-receipt text-blue-500 text-2xl"></i>

                        </div>

                    </div>


                    <!-- MENU ITEMS -->
                    <div class="bg-white p-6 rounded-xl shadow dark:bg-gray-800">

                        <div class="flex justify-between items-center">

                            <div>

                                <p class="text-gray-500 text-sm">
                                    Menu Items
                                </p>

                                <h2 class="text-2xl font-bold">
                                    36
                                </h2>

                            </div>

                            <i class="fa-solid fa-utensils text-yellow-500 text-2xl"></i>

                        </div>

                    </div>


                    <!-- CUSTOMERS -->
                    <div class="bg-white p-6 rounded-xl shadow dark:bg-gray-800">

                        <div class="flex justify-between items-center">

                            <div>

                                <p class="text-gray-500 text-sm">
                                    Customers
                                </p>

                                <h2 class="text-2xl font-bold">
                                    328
                                </h2>

                            </div>

                            <i class="fa-solid fa-user-group text-purple-500 text-2xl"></i>

                        </div>

                    </div>

                </div>


                <!-- SALES REPORT TABLE -->
                <div class="bg-white rounded-xl shadow-xl dark:bg-gray-800 mt-6">

                    <div class="p-4 border-b font-semibold dark:border-gray-700">

                        Sales Report

                    </div>

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="p-3 text-left">Date</th>
                                <th class="p-3 text-left">Orders</th>
                                <th class="p-3 text-left">Revenue</th>
                                <th class="p-3 text-left">Status</th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr class="border-t dark:border-gray-700">

                                <td class="p-3">2026-03-09</td>
                                <td class="p-3">124</td>
                                <td class="p-3">Rp 8.200.000</td>

                                <td class="p-3">

                                    <span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">

                                        Completed

                                    </span>

                                </td>

                            </tr>


                            <tr class="border-t dark:border-gray-700">

                                <td class="p-3">2026-03-08</td>
                                <td class="p-3">98</td>
                                <td class="p-3">Rp 6.400.000</td>

                                <td class="p-3">

                                    <span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">

                                        Completed

                                    </span>

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

    <?php require __DIR__ . '/../../components/toast.php'; ?>


    <?php if (isset($_SESSION['toast'])): ?>

        <script>
            window.toastData = {
                type: "<?= $_SESSION['toast']['type'] ?>",
                message: "<?= $_SESSION['toast']['message'] ?>"
            }
        </script>

    <?php unset($_SESSION['toast']);
    endif; ?>

    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>
    <script src="/rkd-cafe/public/assets/js/sidebar-tooltip.js"></script>

</body>

</html>
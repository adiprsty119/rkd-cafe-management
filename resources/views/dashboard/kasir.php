<?php

require __DIR__ . '/../../../middleware/AuthMiddleware.php';

/* ==========================
   ROLE VALIDATION
========================== */

if ($_SESSION['role'] !== 'kasir') {
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

    <title><?= $t['site_title'] ?> - Kasir</title>

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

    <div class="flex h-screen">

        <!-- SIDEBAR -->
        <aside
            :class="sidebarOpen ? 'w-64' : 'w-20'"
            class="bg-white dark:bg-gray-800 transition-all duration-300">

            <?php require __DIR__ . '/../../components/sidebar.php'; ?>

        </aside>


        <!-- MAIN -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- NAVBAR -->
            <div class="p-4 border-b dark:border-gray-700">

                <?php require __DIR__ . '/../../components/navbar.php'; ?>

            </div>


            <!-- CONTENT -->
            <main class="p-6 space-y-6 overflow-y-auto">


                <!-- STAT CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                    <!-- ORDERS TODAY -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex justify-between items-center">

                            <div>

                                <p class="text-gray-500 text-sm">
                                    Pesanan Hari Ini
                                </p>

                                <h2 class="text-2xl font-bold">
                                    42
                                </h2>

                            </div>

                            <i class="fa-solid fa-receipt text-blue-500 text-2xl"></i>

                        </div>

                    </div>


                    <!-- ACTIVE ORDERS -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex justify-between items-center">

                            <div>

                                <p class="text-gray-500 text-sm">
                                    Pesanan Aktif
                                </p>

                                <h2 class="text-2xl font-bold">
                                    7
                                </h2>

                            </div>

                            <i class="fa-solid fa-clock text-yellow-500 text-2xl"></i>

                        </div>

                    </div>


                    <!-- TOTAL SALES -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex justify-between items-center">

                            <div>

                                <p class="text-gray-500 text-sm">
                                    Penjualan Hari Ini
                                </p>

                                <h2 class="text-2xl font-bold">
                                    Rp 2.450.000
                                </h2>

                            </div>

                            <i class="fa-solid fa-money-bill-wave text-green-500 text-2xl"></i>

                        </div>

                    </div>


                    <!-- CUSTOMERS -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">

                        <div class="flex justify-between items-center">

                            <div>

                                <p class="text-gray-500 text-sm">
                                    Pelanggan
                                </p>

                                <h2 class="text-2xl font-bold">
                                    18
                                </h2>

                            </div>

                            <i class="fa-solid fa-user-group text-purple-500 text-2xl"></i>

                        </div>

                    </div>

                </div>



                <!-- ORDER TABLE -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow mt-6">

                    <div class="p-4 border-b font-semibold dark:border-gray-700">

                        Pesanan Terbaru

                    </div>

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="p-3 text-left">Order ID</th>
                                <th class="p-3 text-left">Menu</th>
                                <th class="p-3 text-left">Total</th>
                                <th class="p-3 text-left">Status</th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr class="border-t dark:border-gray-700">

                                <td class="p-3">#ORD101</td>
                                <td class="p-3">Cappuccino</td>
                                <td class="p-3">Rp 30.000</td>

                                <td class="p-3">

                                    <span class="bg-yellow-100 text-yellow-600 px-2 py-1 rounded text-xs">

                                        Preparing

                                    </span>

                                </td>

                            </tr>


                            <tr class="border-t dark:border-gray-700">

                                <td class="p-3">#ORD102</td>
                                <td class="p-3">Latte + Sandwich</td>
                                <td class="p-3">Rp 55.000</td>

                                <td class="p-3">

                                    <span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">

                                        Paid

                                    </span>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


            </main>

        </div>

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

</body>

</html>
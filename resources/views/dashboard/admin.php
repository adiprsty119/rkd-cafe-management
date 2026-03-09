<?php

require __DIR__ . '/../../../middleware/AuthMiddleware.php';

/* ==========================
   LANGUAGE SYSTEM
========================== */

// bahasa default
$lang = $_GET['lang'] ?? 'id';

// validasi bahasa
if (!in_array($lang, ['id', 'en'])) {
    $lang = 'id';
}

// load translation
$t = require __DIR__ . '/../../lang/' . $lang . '.php';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $t['site_title'] ?></title>

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


        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col min-w-0 md:ml-0 transition-all duration-300">

            <!-- NAVBAR -->
            <div class="p-4 border-t border-gray-700">
                <?php require __DIR__ . '/../../components/navbar.php'; ?>
            </div>

            <!-- DASHBOARD CONTENT -->
            <main class="p-4 md:p-6 space-y-6 overflow-y-auto">

                <!-- STATISTIC CARDS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-14">

                    <div class="bg-white p-6 rounded-xl shadow-2xl cursor-pointer dark:bg-gray-800">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm"><?= $t['total_sales'] ?></p>
                                <h2 class="text-2xl font-bold">Rp 8.2 jt</h2>
                            </div>
                            <i class="fa-solid fa-money-bill-wave text-green-500 text-2xl"></i>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-2xl cursor-pointer dark:bg-gray-800">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm"><?= $t['orders_today'] ?></p>
                                <h2 class="text-2xl font-bold">124</h2>
                            </div>
                            <i class="fa-solid fa-receipt text-blue-500 text-2xl"></i>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-2xl cursor-pointer dark:bg-gray-800">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm"><?= $t['menu_items'] ?></p>
                                <h2 class="text-2xl font-bold">36</h2>
                            </div>
                            <i class="fa-solid fa-utensils text-yellow-500 text-2xl"></i>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-2xl cursor-pointer dark:bg-gray-800">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm"><?= $t['customers'] ?></p>
                                <h2 class="text-2xl font-bold">58</h2>
                            </div>
                            <i class="fa-solid fa-user-group text-purple-500 text-2xl"></i>
                        </div>
                    </div>

                </div>


                <!-- RECENT ORDERS TABLE -->
                <div class="bg-white rounded-xl shadow-xl dark:bg-gray-700 overflow-x-auto">

                    <div class="p-4 border-b font-semibold dark:bg-gray-800">
                        <?= $t['recent_orders'] ?>
                    </div>

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="p-3 text-left"><?= $t['order_id'] ?></th>
                                <th class="p-3 text-left"><?= $t['customer'] ?></th>
                                <th class="p-3 text-left"><?= $t['menu'] ?></th>
                                <th class="p-3 text-left"><?= $t['total'] ?></th>
                                <th class="p-3 text-left"><?= $t['status'] ?></th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr class="border-t">
                                <td class="p-3">#ORD001</td>
                                <td class="p-3">Andi</td>
                                <td class="p-3">Latte + Croissant</td>
                                <td class="p-3">Rp 45.000</td>
                                <td class="p-3">
                                    <span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">
                                        Paid
                                    </span>
                                </td>
                            </tr>

                            <tr class="border-t">
                                <td class="p-3">#ORD002</td>
                                <td class="p-3">Budi</td>
                                <td class="p-3">Espresso</td>
                                <td class="p-3">Rp 20.000</td>
                                <td class="p-3">
                                    <span class="bg-yellow-100 text-yellow-600 px-2 py-1 rounded text-xs">
                                        Pending
                                    </span>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </main>

        </div>

    </div>


    <!-- <div
        x-show="loadingTheme"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 cursor-wait">

        <div class="bg-white dark:bg-gray-800 px-6 py-4 rounded-xl shadow-lg flex items-center gap-3">

            <svg
                class="animate-spin h-5 w-5 text-yellow-500"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24">

                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"></circle>

                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v8z"></path>

            </svg>

            <span class="text-sm font-medium">
                Mengubah tema...
            </span>

        </div>

    </div> -->

    <?php require __DIR__ . '/../../components/toast.php'; ?>

    <?php if (isset($_SESSION['toast'])): ?>

        <script>
            window.toastData = {
                type: "<?= $_SESSION['toast']['type'] ?>",
                message: "<?= $_SESSION['toast']['message'] ?>"
            };
        </script>

    <?php unset($_SESSION['toast']);
    endif; ?>

    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>

</body>

</html>
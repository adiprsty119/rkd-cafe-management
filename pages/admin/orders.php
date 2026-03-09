<?php

require $_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/middleware/AuthMiddleware.php';

/* ==========================
   LANGUAGE SYSTEM
========================== */

$lang = $_GET['lang'] ?? 'id';

if (!in_array($lang, ['id', 'en'])) {
    $lang = 'id';
}

$t = require __DIR__ . '/../../resources/lang/' . $lang . '.php';

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $t['orders'] ?? 'Orders' ?></title>

    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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


    <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900 relative">


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


        <!-- OVERLAY -->
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



            <!-- PAGE CONTENT -->
            <main class="flex-1 p-4 md:p-6 space-y-6 overflow-y-auto">


                <!-- HEADER -->
                <div class="flex justify-between items-center">

                    <div>
                        <h1 class="text-2xl font-bold"><?= $t['orders'] ?? 'Orders' ?></h1>
                        <p class="text-sm text-gray-500"><?= $t['manage_orders'] ?? 'Manage customer orders' ?></p>
                    </div>

                </div>



                <!-- SEARCH + FILTER -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex flex-wrap gap-4">

                    <input
                        type="text"
                        placeholder="<?= $t['search_orders'] ?? 'Search order...' ?>"
                        class="border rounded-lg px-4 py-2 w-64 focus:ring-2 focus:ring-amber-400 dark:bg-gray-700">

                    <select class="border rounded-lg px-4 py-2 dark:bg-gray-700">

                        <option><?= $t['status'] ?? 'Status' ?></option>
                        <option>Paid</option>
                        <option>Pending</option>
                        <option>Cancelled</option>

                    </select>

                </div>



                <!-- ORDERS TABLE -->
                <div class="bg-white rounded-xl shadow-xl dark:bg-gray-800 overflow-x-auto">

                    <div class="p-4 border-b font-semibold dark:bg-gray-700">

                        <?= $t['order_list'] ?? 'Order List' ?>

                    </div>


                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="p-3 text-left"><?= $t['order_id'] ?? 'Order ID' ?></th>
                                <th class="p-3 text-left"><?= $t['customer'] ?? 'Customer' ?></th>
                                <th class="p-3 text-left"><?= $t['menu'] ?? 'Menu' ?></th>
                                <th class="p-3 text-left"><?= $t['total'] ?? 'Total' ?></th>
                                <th class="p-3 text-left"><?= $t['status'] ?? 'Status' ?></th>
                                <th class="p-3 text-left"><?= $t['date'] ?? 'Date' ?></th>
                                <th class="p-3 text-left"><?= $t['action'] ?? 'Action' ?></th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                                <td class="p-3 font-semibold">
                                    #ORD001
                                </td>

                                <td class="p-3">
                                    Andi
                                </td>

                                <td class="p-3">
                                    Latte + Croissant
                                </td>

                                <td class="p-3">
                                    Rp 45.000
                                </td>

                                <td class="p-3">

                                    <span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">
                                        Paid
                                    </span>

                                </td>

                                <td class="p-3">
                                    2026-03-10
                                </td>

                                <td class="p-3 space-x-3">

                                    <button class="text-blue-600 hover:text-blue-800">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <button class="text-red-600 hover:text-red-800">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </td>

                            </tr>



                            <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                                <td class="p-3 font-semibold">
                                    #ORD002
                                </td>

                                <td class="p-3">
                                    Budi
                                </td>

                                <td class="p-3">
                                    Espresso
                                </td>

                                <td class="p-3">
                                    Rp 20.000
                                </td>

                                <td class="p-3">

                                    <span class="bg-yellow-100 text-yellow-600 px-2 py-1 rounded text-xs">
                                        Pending
                                    </span>

                                </td>

                                <td class="p-3">
                                    2026-03-10
                                </td>

                                <td class="p-3 space-x-3">

                                    <button class="text-blue-600 hover:text-blue-800">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <button class="text-red-600 hover:text-red-800">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

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


</body>

</html>
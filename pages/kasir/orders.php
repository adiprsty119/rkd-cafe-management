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



            <!-- PAGE CONTENT -->
            <main class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6">


                <!-- HEADER -->
                <div class="flex justify-between items-center">

                    <div>

                        <h1 class="text-2xl font-bold">
                            <?= $t['orders'] ?? 'Orders' ?>
                        </h1>

                        <p class="text-sm text-gray-500">
                            Transaction history
                        </p>

                    </div>

                </div>



                <!-- SEARCH -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex gap-4">

                    <input
                        type="text"
                        placeholder="Search order..."
                        class="border rounded px-3 py-2 dark:bg-gray-700 w-64">

                    <select class="border rounded px-3 py-2 dark:bg-gray-700">

                        <option>Status</option>
                        <option>Pending</option>
                        <option>Paid</option>
                        <option>Completed</option>

                    </select>

                </div>



                <!-- ORDER TABLE -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">

                    <div class="p-4 border-b font-semibold dark:bg-gray-700">

                        Order List

                    </div>

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="p-3 text-left">Order ID</th>
                                <th class="p-3 text-left">Customer</th>
                                <th class="p-3 text-left">Items</th>
                                <th class="p-3 text-left">Total</th>
                                <th class="p-3 text-left">Status</th>
                                <th class="p-3 text-left">Time</th>
                                <th class="p-3 text-left">Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr class="border-t">

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
                                    10:20
                                </td>

                                <td class="p-3">

                                    <button class="text-blue-600 hover:underline">
                                        View
                                    </button>

                                </td>

                            </tr>



                            <tr class="border-t">

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
                                    10:35
                                </td>

                                <td class="p-3">

                                    <button class="text-blue-600 hover:underline">
                                        View
                                    </button>

                                </td>

                            </tr>


                            <tr class="border-t">

                                <td class="p-3 font-semibold">
                                    #ORD003
                                </td>

                                <td class="p-3">
                                    Sari
                                </td>

                                <td class="p-3">
                                    Matcha Latte
                                </td>

                                <td class="p-3">
                                    Rp 32.000
                                </td>

                                <td class="p-3">

                                    <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs">
                                        Completed
                                    </span>

                                </td>

                                <td class="p-3">
                                    11:05
                                </td>

                                <td class="p-3">

                                    <button class="text-blue-600 hover:underline">
                                        View
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
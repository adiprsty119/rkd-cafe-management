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

    <title><?= $t['customers'] ?? 'Customers' ?></title>

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
    x-data="{ dark: localStorage.theme === 'dark', loading:false, loadingTheme:false, sidebarOpen:true, openAdd:false }"
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
                            <?= $t['customers'] ?? 'Customers' ?>
                        </h1>

                        <p class="text-sm text-gray-500">
                            Customer management
                        </p>

                    </div>

                    <button
                        @click="openAdd=true"
                        class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded">

                        <i class="fa-solid fa-plus mr-2"></i>
                        Add Customer

                    </button>

                </div>



                <!-- SEARCH -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow">

                    <input
                        type="text"
                        placeholder="Search customer..."
                        class="border rounded px-3 py-2 dark:bg-gray-700 w-64">

                </div>



                <!-- CUSTOMER TABLE -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">

                    <div class="p-4 border-b font-semibold dark:bg-gray-700">

                        Customer List

                    </div>

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="p-3 text-left">Customer</th>
                                <th class="p-3 text-left">Phone</th>
                                <th class="p-3 text-left">Orders</th>
                                <th class="p-3 text-left">Last Visit</th>
                                <th class="p-3 text-left">Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr class="border-t">

                                <td class="p-3 font-semibold">
                                    Andi
                                </td>

                                <td class="p-3">
                                    08123456789
                                </td>

                                <td class="p-3">
                                    24
                                </td>

                                <td class="p-3">
                                    2026-03-09
                                </td>

                                <td class="p-3 space-x-2">

                                    <button class="text-blue-600 hover:underline">
                                        View
                                    </button>

                                </td>

                            </tr>


                            <tr class="border-t">

                                <td class="p-3 font-semibold">
                                    Budi
                                </td>

                                <td class="p-3">
                                    08234567891
                                </td>

                                <td class="p-3">
                                    18
                                </td>

                                <td class="p-3">
                                    2026-03-08
                                </td>

                                <td class="p-3 space-x-2">

                                    <button class="text-blue-600 hover:underline">
                                        View
                                    </button>

                                </td>

                            </tr>


                            <tr class="border-t">

                                <td class="p-3 font-semibold">
                                    Sari
                                </td>

                                <td class="p-3">
                                    08345678912
                                </td>

                                <td class="p-3">
                                    12
                                </td>

                                <td class="p-3">
                                    2026-03-07
                                </td>

                                <td class="p-3 space-x-2">

                                    <button class="text-blue-600 hover:underline">
                                        View
                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>



                <!-- ADD CUSTOMER MODAL -->
                <div
                    x-show="openAdd"
                    x-transition
                    class="fixed inset-0 bg-black/40 flex items-center justify-center">

                    <div
                        @click.outside="openAdd=false"
                        class="bg-white dark:bg-gray-800 rounded-xl p-6 w-96">

                        <h2 class="text-lg font-bold mb-4">
                            Add Customer
                        </h2>

                        <input
                            type="text"
                            placeholder="Customer Name"
                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">

                        <input
                            type="text"
                            placeholder="Phone Number"
                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">

                        <input
                            type="email"
                            placeholder="Email"
                            class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700">

                        <div class="flex justify-end gap-2">

                            <button
                                @click="openAdd=false"
                                class="px-4 py-2 border rounded">
                                Cancel
                            </button>

                            <button
                                class="bg-amber-500 text-white px-4 py-2 rounded">
                                Save
                            </button>

                        </div>

                    </div>

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
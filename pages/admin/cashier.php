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

    <title><?= $t['cashier'] ?? 'Cashier POS' ?></title>

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



            <!-- POS CONTENT -->
            <main class="p-4 md:p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 overflow-y-auto">


                <!-- MENU SECTION -->
                <div class="lg:col-span-2">

                    <div class="flex justify-between items-center mb-4">

                        <h1 class="text-xl font-bold">
                            <?= $t['cashier'] ?? 'Cashier POS' ?>
                        </h1>

                        <input
                            type="text"
                            placeholder="<?= $t['search_menu'] ?? 'Search menu...' ?>"
                            class="border px-3 py-2 rounded-lg dark:bg-gray-800">

                    </div>


                    <!-- MENU GRID -->
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">

                        <!-- ITEM -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-3 cursor-pointer hover:shadow-lg transition">

                            <img
                                src="/rkd-cafe/public/assets/images/latte.png"
                                class="h-28 w-full object-cover rounded">

                            <div class="mt-2">

                                <p class="font-semibold text-sm">
                                    Latte
                                </p>

                                <p class="text-xs text-gray-500">
                                    Coffee
                                </p>

                                <p class="text-amber-600 font-bold mt-1">
                                    Rp 30.000
                                </p>

                            </div>

                        </div>


                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-3 cursor-pointer hover:shadow-lg transition">

                            <img
                                src="/rkd-cafe/public/assets/images/croissant.png"
                                class="h-28 w-full object-cover rounded">

                            <div class="mt-2">

                                <p class="font-semibold text-sm">
                                    Croissant
                                </p>

                                <p class="text-xs text-gray-500">
                                    Bakery
                                </p>

                                <p class="text-amber-600 font-bold mt-1">
                                    Rp 25.000
                                </p>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- ORDER CART -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow flex flex-col">

                    <div class="p-4 border-b font-semibold">

                        <?= $t['order'] ?? 'Order' ?>

                    </div>


                    <div class="flex-1 overflow-y-auto p-4 space-y-3">

                        <!-- ITEM -->
                        <div class="flex justify-between text-sm">

                            <span>
                                Latte
                            </span>

                            <span>
                                Rp 30.000
                            </span>

                        </div>

                        <div class="flex justify-between text-sm">

                            <span>
                                Croissant
                            </span>

                            <span>
                                Rp 25.000
                            </span>

                        </div>

                    </div>


                    <div class="border-t p-4 space-y-3">

                        <div class="flex justify-between font-bold">

                            <span>
                                <?= $t['total'] ?? 'Total' ?>
                            </span>

                            <span>
                                Rp 55.000
                            </span>

                        </div>


                        <button
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-lg font-semibold">

                            <?= $t['checkout'] ?? 'Checkout' ?>

                        </button>

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
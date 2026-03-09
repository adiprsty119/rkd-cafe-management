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

    <title><?= $t['menu'] ?? 'Menu' ?></title>

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
                <div>

                    <h1 class="text-2xl font-bold">
                        <?= $t['menu'] ?? 'Menu List' ?>
                    </h1>

                    <p class="text-sm text-gray-500">
                        Available menu items
                    </p>

                </div>



                <!-- SEARCH -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow">

                    <input
                        type="text"
                        placeholder="Search menu..."
                        class="border rounded px-3 py-2 dark:bg-gray-700 w-64">

                </div>



                <!-- MENU GRID -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">


                    <!-- MENU ITEM -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 hover:shadow-lg transition cursor-pointer">

                        <img
                            src="/rkd-cafe/public/assets/images/latte.png"
                            class="h-28 w-full object-cover rounded">

                        <div class="mt-3">

                            <p class="font-semibold">
                                Latte
                            </p>

                            <p class="text-sm text-gray-500">
                                Coffee
                            </p>

                            <p class="text-amber-600 font-bold mt-1">
                                Rp 30.000
                            </p>

                        </div>

                    </div>


                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 hover:shadow-lg transition cursor-pointer">

                        <img
                            src="/rkd-cafe/public/assets/images/espresso.png"
                            class="h-28 w-full object-cover rounded">

                        <div class="mt-3">

                            <p class="font-semibold">
                                Espresso
                            </p>

                            <p class="text-sm text-gray-500">
                                Coffee
                            </p>

                            <p class="text-amber-600 font-bold mt-1">
                                Rp 25.000
                            </p>

                        </div>

                    </div>


                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 hover:shadow-lg transition cursor-pointer">

                        <img
                            src="/rkd-cafe/public/assets/images/croissant.png"
                            class="h-28 w-full object-cover rounded">

                        <div class="mt-3">

                            <p class="font-semibold">
                                Croissant
                            </p>

                            <p class="text-sm text-gray-500">
                                Bakery
                            </p>

                            <p class="text-amber-600 font-bold mt-1">
                                Rp 22.000
                            </p>

                        </div>

                    </div>


                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 hover:shadow-lg transition cursor-pointer">

                        <img
                            src="/rkd-cafe/public/assets/images/matcha.png"
                            class="h-28 w-full object-cover rounded">

                        <div class="mt-3">

                            <p class="font-semibold">
                                Matcha Latte
                            </p>

                            <p class="text-sm text-gray-500">
                                Non Coffee
                            </p>

                            <p class="text-amber-600 font-bold mt-1">
                                Rp 32.000
                            </p>

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
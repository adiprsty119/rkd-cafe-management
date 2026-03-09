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

    <title><?= $t['categories'] ?? 'Categories' ?></title>

    <!-- Tailwind -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine -->
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
                        <h1 class="text-2xl font-bold"><?= $t['categories'] ?? 'Categories' ?></h1>
                        <p class="text-sm text-gray-500"><?= $t['manage_categories'] ?? 'Manage menu categories' ?></p>
                    </div>

                    <button
                        class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg shadow">

                        <i class="fa-solid fa-plus mr-2"></i>
                        <?= $t['add_category'] ?? 'Add Category' ?>

                    </button>

                </div>



                <!-- SEARCH -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow">

                    <input
                        type="text"
                        placeholder="<?= $t['search_category'] ?? 'Search category...' ?>"
                        class="border rounded-lg px-4 py-2 w-64 focus:ring-2 focus:ring-amber-400 dark:bg-gray-700">

                </div>



                <!-- CATEGORY TABLE -->
                <div class="bg-white rounded-xl shadow-xl dark:bg-gray-800 overflow-x-auto">

                    <div class="p-4 border-b font-semibold dark:bg-gray-700">

                        <?= $t['category_list'] ?? 'Category List' ?>

                    </div>


                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">

                            <tr>

                                <th class="p-3 text-left"><?= $t['id'] ?? 'ID' ?></th>
                                <th class="p-3 text-left"><?= $t['category'] ?? 'Category' ?></th>
                                <th class="p-3 text-left"><?= $t['created'] ?? 'Created' ?></th>
                                <th class="p-3 text-left"><?= $t['action'] ?? 'Action' ?></th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                                <td class="p-3">1</td>

                                <td class="p-3 font-semibold">
                                    Coffee
                                </td>

                                <td class="p-3">
                                    2026-03-10
                                </td>

                                <td class="p-3 space-x-3">

                                    <button class="text-blue-600 hover:text-blue-800">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <button class="text-red-600 hover:text-red-800">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </td>

                            </tr>


                            <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                                <td class="p-3">2</td>

                                <td class="p-3 font-semibold">
                                    Bakery
                                </td>

                                <td class="p-3">
                                    2026-03-10
                                </td>

                                <td class="p-3 space-x-3">

                                    <button class="text-blue-600 hover:text-blue-800">
                                        <i class="fa-solid fa-pen"></i>
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
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
require_once __DIR__ . '/../../app/helpers/menu_helper.php';
require_once __DIR__ . '/../../app/helpers/menu_engine.php';

$role = $_SESSION['role'] ?? 'guest';
$sidebarCollapsed = $_SESSION['sidebar_collapsed'] ?? 0;

/* ==========================
   MENU ENGINE
========================== */

$menus = getMenusByRole($role);
$currentMenu = findMenuByRoute($menus);
$pageTitle = $currentMenu['menu']['title'] ?? 'Dashboard';
$breadcrumb = generateBreadcrumb($currentMenu);
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $pageTitle ?></title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- Vanilla CSS -->
    <link href="/rkd-cafe/public/assets/css/utilities.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Global Seacrh [global-search.js] -->
    <script defer src="/rkd-cafe/public/assets/js/global-search.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>


<body
    x-data="{ dark: localStorage.theme === 'dark', loading:false, loadingTheme:false, sidebarOpen:<?= isset($sidebarCollapsed) && $sidebarCollapsed ? 'false' : 'true' ?> }"
    @toggle-theme.window="loadingTheme = true; setTimeout(() => {let newTheme = !dark; localStorage.theme = newTheme ? 'dark' : 'light'; location.reload();}, 800)"
    x-init="document.documentElement.classList.toggle('dark', dark)"
    :class="{ 'dark': dark }"
    class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-300">

    <div class="flex min-h-screen bg-gradient-to-br from-gray-200 via-gray-100 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition">

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

            <!-- =========================
                    HEADER STACK
            ========================= -->
            <div id="headerStack" class="sticky top-0 z-50 relative">

                <!-- =========================
                    NAVBAR
                ========================= -->
                <div
                    id="dashboardNavbar"
                    class="relative z-50 transition-all duration-300">

                    <div class="w-full px-4 mt-3 transition-all duration-300 bg-gray-100 dark:bg-gray-800">
                        <?php require __DIR__ . '/../../resources/components/navbar.php'; ?>
                    </div>


                    <!-- =========================
                        BREADCRUMB INDICATOR
                    ========================= -->
                    <div
                        id="breadcrumbIndicator"
                        class="absolute left-auto -translate-x-1/2 top-full z-50 opacity-0 translate-y-2 pointer-events-none transition-all ease-out delay-75 duration-300">

                        <button
                            class="flex items-center ml-16 px-2 py-1 text-sm rounded-sm backdrop-blur-md bg-gray-100 dark:bg-gray-800 shadow-md hover:bg-white/60 active:scale-95 transition cursor-pointer">

                            <i class="fa-solid fa-angle-down animate-bounce"></i>

                        </button>

                    </div>

                </div>

            </div>



            <!-- =========================
                BREADCRUMB CONTAINER
            ========================= -->
            <div
                id="breadcrumbContainer"
                class="relative will-change-transform transition-opacity duration-200">

                <div
                    id="breadcrumbMask"
                    class="px-4 py-2 overflow-hidden">

                    <?php require __DIR__ . '/../../resources/components/breadcrumb.php'; ?>

                </div>

            </div>

            <!-- DASHBOARD CONTENT -->
            <main id="dashboardScroll"
                class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6 scrollbar-hide">


                <!-- MENU SECTION -->
                <div class="lg:col-span-2">

                    <!-- HEADER -->
                    <div class="flex flex-col mb-12 md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm px-6 py-5">

                        <!-- LEFT -->
                        <div class="flex items-center gap-4">

                            <!-- PAGE ICON -->
                            <div class="w-11 h-11 flex items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-500/20">

                                <i class="fa-solid fa-mug-hot text-amber-600 dark:text-amber-400"></i>

                            </div>

                            <!-- TITLE -->
                            <div>

                                <h1 class="text-xl md:text-2xl font-semibold tracking-tight">
                                    <?= htmlspecialchars($pageTitle) ?>
                                </h1>

                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    <?= $t['search_menu'] ?? 'Search menu items quickly' ?>
                                </p>

                            </div>

                        </div>

                        <!-- RIGHT SEARCH -->
                        <div class="relative w-full md:w-64">

                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>

                            <input
                                type="text"
                                placeholder="<?= $t['search_menu'] ?? 'Search menu...' ?>"
                                class="w-full pl-9 pr-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">

                        </div>

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

    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>
    <script src="/rkd-cafe/public/assets/js/header.js"></script>
    <script src="/rkd-cafe/public/assets/js/sidebar-tooltip.js"></script>

    <div
        id="global-tooltip"
        class="fixed hidden px-2 py-1 text-xs text-white bg-black rounded shadow-lg whitespace-nowrap z-[9999] pointer-events-none">
    </div>

    <?php require __DIR__ . '/../../resources/components/toast.php'; ?>
    <?php if (isset($_SESSION['toast'])): ?>

        <script>
            window.toastData = {
                type: "<?= $_SESSION['toast']['type'] ?>",
                message: "<?= $_SESSION['toast']['message'] ?>"
            }
        </script>

    <?php unset($_SESSION['toast']);
    endif; ?>
</body>

</html>
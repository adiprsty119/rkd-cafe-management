<?php
define('APP_INIT', true);

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
require_once __DIR__ . '/../../../app/helpers/menu_helper.php';
require_once __DIR__ . '/../../../app/helpers/menu_engine.php';

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

    <title><?= $t['site_title'] ?></title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
    x-init=" document.documentElement.classList.toggle('dark', dark)"
    :class="{ 'dark': dark }"
    class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-300">

    <div class="flex min-h-screen bg-gradient-to-br from-gray-200 via-gray-100 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition relative">

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
                        <?php require __DIR__ . '/../../components/navbar.php'; ?>
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

                    <?php require __DIR__ . '/../../components/breadcrumb.php'; ?>

                </div>

            </div>

            <!-- DASHBOARD CONTENT -->
            <main id="dashboardScroll"
                class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6 scrollbar-hide">

                <!-- HEADER -->
                <div
                    class="flex flex-col mb-12 md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm px-6 py-5 mb-8">

                    <!-- LEFT -->
                    <div class="flex items-center gap-4">

                        <!-- ICON -->
                        <div class="w-11 h-11 flex items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-500/20">

                            <i class="fa-solid fa-chart-line text-blue-600 dark:text-blue-400"></i>

                        </div>

                        <!-- TITLE -->
                        <div>

                            <h1 class="text-xl md:text-2xl font-semibold tracking-tight">
                                <?= htmlspecialchars($pageTitle ?? ($t['dashboard'] ?? 'Dashboard')) ?>
                            </h1>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <?= $t['dashboard_overview'] ?? 'Overview of your cafe performance today' ?>
                            </p>

                        </div>

                    </div>

                    <!-- RIGHT ACTION -->
                    <div
                        x-data="{loading:false}"
                        class="flex items-center gap-3">

                        <button
                            @click="loading=true; setTimeout(()=>window.location.reload(),400)"
                            class="flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">

                            <i
                                class="fa-solid fa-rotate"
                                :class="loading ? 'animate-spin' : ''"></i>

                            <span x-text="loading ? 'Refreshing...' : 'Refresh'"></span>

                        </button>

                    </div>

                </div>

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
            };
        </script>

    <?php unset($_SESSION['toast']);
    endif; ?>

    <script>
        window.csrfToken = "<?= $_SESSION['csrf_token'] ?? '' ?>";
    </script>

    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>
    <script src="/rkd-cafe/public/assets/js/sidebar-tooltip.js"></script>
</body>

</html>
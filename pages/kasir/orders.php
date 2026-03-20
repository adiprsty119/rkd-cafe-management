<?php
define('APP_INIT', true);

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
require_once __DIR__ . '/../../app/helpers/menu_helper.php';
require_once __DIR__ . '/../../app/helpers/menu_engine.php';

$role = $_SESSION['role'] ?? 'guest';
$sidebarCollapsed = $_SESSION['sidebar_collapsed'] ?? 0;

/* ==========================
   MENU ENGINE
========================== */

$allMenus = $_SESSION['menu_config'][$role] ?? [];
$menus = getMenusByRole($role);
$currentMenu = findMenuByRoute($allMenus);
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

    <!-- FontAwesome -->
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

    <div class="flex min-h-screen relative bg-gradient-to-br from-gray-200 via-gray-100 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition">

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


            <main id="dashboardScroll"
                class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6 scrollbar-hide">

                <!-- HEADER -->
                <div class="flex flex-col mb-12 md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm px-6 py-5">

                    <!-- LEFT -->
                    <div class="flex items-center gap-4">

                        <!-- PAGE ICON -->
                        <div class="w-11 h-11 flex items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-500/20">

                            <i class="fa-solid fa-clock-rotate-left text-indigo-600 dark:text-indigo-400"></i>

                        </div>

                        <!-- TITLE -->
                        <div>

                            <h1 class="text-xl md:text-2xl font-semibold tracking-tight">
                                <?= htmlspecialchars($pageTitle) ?>
                            </h1>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <?= $t['transaction_history'] ?? 'Transaction history' ?>
                            </p>

                        </div>

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
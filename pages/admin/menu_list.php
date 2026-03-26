<?php
define('APP_INIT', true);

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
require_once __DIR__ . '/../../app/helpers/buttons.php';

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

        <!-- OVERLAY -->
        <div
            x-show="sidebarOpen"
            @click="sidebarOpen=false"
            class="fixed inset-0 bg-black/40 z-30 md:hidden">
        </div>

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col min-w-0 h-screen md:ml-0 transition-all duration-300">

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
                x-data="menuManager()"
                x-init="init()"
                class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6 scrollbar-hide">

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
                                <?= $t['manage_menu'] ?? 'Manage cafe menu items' ?>
                            </p>

                        </div>

                    </div>

                    <!-- RIGHT ACTION -->
                    <div class="flex items-center gap-3">

                        <div class="relative" x-data="{ open:false }">

                            <!-- MAIN BUTTON -->
                            <button
                                @click="open = !open"
                                @keydown.window.ctrl.n.prevent="quickAdd()"
                                class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl shadow-sm text-sm transition-all hover:scale-105 active:scale-95 cursor-pointer">

                                <i class="fa-solid fa-plus"></i>
                                <?= $t['add_menu'] ?? 'Add Menu' ?>

                                <i class="fa-solid fa-chevron-down text-xs opacity-70"></i>
                            </button>

                            <!-- DROPDOWN -->
                            <div
                                x-show="open"
                                @click.outside="open = false"
                                x-transition
                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border dark:border-gray-700 overflow-hidden z-50">

                                <!-- ADD PAGE -->
                                <button
                                    @click="navigate('/rkd-cafe/pages/admin/menu/create.php'); open=false"
                                    class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm flex items-center gap-2 cursor-pointer">

                                    <i class="fa-solid fa-plus"></i>
                                    Add New Menu
                                </button>

                                <!-- QUICK ADD (MODAL) -->
                                <button
                                    @click="quickAdd(); open=false"
                                    class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm flex items-center gap-2 cursor-pointer">

                                    <i class="fa-solid fa-bolt"></i>
                                    Quick Add
                                </button>

                                <!-- DUPLICATE -->
                                <button
                                    @click="duplicateLast(); open=false"
                                    class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm flex items-center gap-2 cursor-pointer">

                                    <i class="fa-solid fa-copy"></i>
                                    Duplicate Last
                                </button>

                            </div>
                        </div>

                    </div>

                </div>

                <div :class="{ 'overflow-hidden': confirmModal }">
                    <!-- SEARCH + FILTER -->
                    <div class="flex flex-wrap gap-4 mb-5">

                        <input
                            x-model="search"
                            type="text"
                            placeholder="<?= $t['search_menu'] ?? 'Search menu...' ?>"
                            class="border rounded-lg px-4 py-2 w-64 focus:ring-2 focus:ring-amber-400 dark:bg-gray-800">

                        <!-- PRICE FILTER -->
                        <input type="number" x-model="minPrice" placeholder="Min price"
                            class="border px-3 py-2 rounded dark:bg-gray-800">

                        <input type="number" x-model="maxPrice" placeholder="Max price"
                            class="border px-3 py-2 rounded dark:bg-gray-800">

                        <select x-model="category" class="border rounded-lg px-4 py-2 dark:bg-gray-800">

                            <option value="">
                                <?= $t['all_categories'] ?? 'All Categories' ?>
                            </option>

                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.name" x-text="cat.name"></option>
                            </template>

                        </select>

                        <select x-model="status" class="border rounded-lg px-4 py-2 dark:bg-gray-800">

                            <option value=""><?= $t['status'] ?? 'Status' ?></option>
                            <option value="active">Available</option>
                            <option value="inactive">Unavailable</option>

                        </select>

                        <!-- USAGE FILTER -->
                        <select x-model="usage" class="border rounded-lg px-4 py-2 dark:bg-gray-800">
                            <option value="">Usage</option>
                            <option value="used">Used</option>
                            <option value="free">Free</option>
                        </select>

                    </div>

                    <!-- MENU TABLE -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">

                        <!-- BULK ACTION 🔥 -->
                        <div
                            x-show="data.some(i => i.selected)"
                            x-transition
                            class="flex gap-2 mb-4">

                            <button
                                @click="bulkDelete()"
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm cursor-pointer">
                                Delete Selected
                            </button>

                            <button
                                @click="bulkEnable()"
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm cursor-pointer">
                                Enable Selected
                            </button>

                        </div>

                        <!-- HEADER -->
                        <div class="flex items-center justify-between px-5 py-4 border-b dark:border-gray-700">

                            <h2 class="font-semibold text-lg">
                                <?= $t['menu_list'] ?? 'Menu List' ?>
                            </h2>

                            <span class="text-xs text-gray-400" x-text="filtered.length + ' items'"></span>

                        </div>

                        <!-- TABLE -->
                        <div class="overflow-x-auto">

                            <table class="w-full text-sm">

                                <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500">

                                    <tr>
                                        <th class="p-3 text-left">No.</th>
                                        <th class="p-3 text-left"><?= $t['image'] ?? 'Image' ?></th>
                                        <th class="p-3 text-left"><?= $t['menu'] ?? 'Menu' ?></th>
                                        <th class="p-3 text-left"><?= $t['category'] ?? 'Category' ?></th>
                                        <th class="p-3 text-left"><?= $t['price'] ?? 'Price' ?></th>
                                        <th class="p-3 text-left"><?= $t['status'] ?? 'Status' ?></th>
                                        <th class="p-3 text-left">Usage</th>
                                        <th class="p-3 text-left"><?= $t['action'] ?? 'Action' ?></th>
                                        <th class="p-3">
                                            <input type="checkbox" @change="toggleAll($event)" class="cursor-pointer">
                                        </th>
                                    </tr>

                                </thead>

                                <tbody>

                                    <template x-for="(item, index) in filtered" :key="item.id">

                                        <tr
                                            class="border-t hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300"
                                            :class="{
                                                'opacity-0 scale-95': item._removing,
                                                'bg-green-50 dark:bg-green-900/20 scale-[1.01]': item._highlight
                                            }">

                                            <td class="p-3 text-gray-400" x-text="index+1"></td>

                                            <td class="p-3">
                                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex items-center justify-center">

                                                    <img
                                                        x-show="item.image"
                                                        :src="item.image"
                                                        @error="item.image = null"
                                                        class="w-full h-full object-cover">

                                                    <template x-if="!item.image">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 100 100"
                                                            class="w-6 h-6 text-gray-400">

                                                            <rect x="25" y="35" width="40" height="30" rx="6" fill="currentColor" />
                                                            <path d="M65 40 Q80 50 65 60" stroke="currentColor" stroke-width="4" fill="none" />
                                                            <path d="M40 20 C35 10, 55 10, 50 20" stroke="currentColor" stroke-width="2" fill="none" />
                                                            <path d="M50 20 C45 10, 65 10, 60 20" stroke="currentColor" stroke-width="2" fill="none" />
                                                            <rect x="30" y="65" width="30" height="5" rx="2" fill="currentColor" />

                                                        </svg>
                                                    </template>

                                                </div>
                                            </td>

                                            <td class="p-3 font-semibold flex items-center gap-2">

                                                <span x-text="item.name"></span>

                                                <!-- 🔥 BADGE DISABLED -->
                                                <span
                                                    x-show="item.status === 'inactive'"
                                                    class="text-[10px] px-2 py-0.5 rounded bg-red-100 text-red-600">
                                                    Disabled
                                                </span>

                                            </td>

                                            <td class="p-3">
                                                <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded"
                                                    x-text="item.category"></span>
                                            </td>

                                            <td class="p-3 font-medium text-amber-500"
                                                x-text="formatRupiah(item.price)">
                                            </td>

                                            <td class="p-3">
                                                <span
                                                    class="text-xs px-2 py-1 rounded"
                                                    :class="item.status === 'active' ? 'bg-green-100 text-green-600' : 'bg-gray-200 text-gray-500'"
                                                    x-text="item.status">
                                                </span>
                                            </td>

                                            <td class="p-3">
                                                <span
                                                    class="text-xs px-2 py-1 rounded"
                                                    :class="item.used 
                                                        ? 'bg-red-100 text-red-600' 
                                                        : 'bg-gray-100 text-gray-600'">

                                                    <span x-text="item.used ? 'Used' : 'Free'"></span>

                                                </span>
                                            </td>

                                            <td class="p-3">
                                                <?= btnActionGroup(
                                                    "edit(item)",
                                                    "confirmDelete(item)",
                                                    "view(item)",
                                                    "enableItem(item)"
                                                ) ?>
                                            </td>

                                            <td class="p-3">
                                                <input type="checkbox" x-model="item.selected" class="cursor-pointer">
                                            </td>

                                        </tr>

                                    </template>

                                    <!-- EMPTY STATE -->
                                    <tr x-show="filtered.length === 0">
                                        <td colspan="7" class="text-center py-10 text-gray-400">
                                            <div class="flex flex-col items-center py-10 text-gray-400">
                                                <i class="fa-solid fa-mug-hot text-3xl mb-2"></i>
                                                <p class="font-medium">No menu found</p>
                                                <p class="text-xs">Try adjusting search or filters</p>
                                            </div>
                                        </td>
                                    </tr>

                                </tbody>

                            </table>

                            <!-- DELETE CONFIRM MODAL -->
                            <div
                                x-show="confirmModal"
                                x-transition
                                @keydown.escape.window="confirmModal = false; selectedItem = null; forceMode = false;"
                                class="fixed inset-0 z-50 flex items-center justify-center">

                                <!-- BACKDROP -->
                                <div
                                    class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                                    @click="confirmModal = false">
                                </div>

                                <div
                                    x-show="confirmModal"
                                    x-transition.opacity.scale
                                    @keydown.escape.window="confirmModal = false"
                                    class="fixed inset-0 z-50 flex items-center justify-center">

                                    <!-- BACKDROP -->
                                    <div
                                        class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
                                        @click="confirmModal = false">
                                    </div>

                                    <!-- MODAL -->
                                    <div
                                        class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md p-7 z-10 transform transition-all duration-300 scale-100">

                                        <!-- ICON -->
                                        <div class="flex items-center justify-center mb-5">
                                            <div class="w-16 h-16 flex items-center justify-center rounded-full  bg-red-100 dark:bg-red-500/20 shadow-inner">

                                                <i
                                                    x-show="!forceMode"
                                                    class="fa-solid fa-trash text-red-500 text-2xl">
                                                </i>

                                                <i
                                                    x-show="forceMode"
                                                    class="fa-solid fa-triangle-exclamation text-red-600 text-2xl animate-pulse">
                                                </i>

                                            </div>
                                        </div>

                                        <!-- TITLE -->
                                        <h2 class="text-xl font-semibold text-center mb-1">
                                            <span x-show="!forceMode">Delete Menu</span>
                                            <span x-show="forceMode" class="text-red-600">Force Delete</span>
                                        </h2>

                                        <!-- SUBTEXT -->
                                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6 leading-relaxed">
                                            <span x-show="!forceMode">
                                                Are you sure you want to remove
                                                <span class="font-semibold text-gray-800 dark:text-white" x-text="selectedItem?.name"></span> ?
                                            </span>

                                            <span x-show="forceMode" class="text-red-500 font-medium">
                                                This action is irreversible and may affect transaction data.
                                            </span>
                                        </p>

                                        <!-- ACTION -->
                                        <div class="flex flex-col gap-4">

                                            <!-- BUTTON GROUP -->
                                            <div class="flex justify-center gap-3">

                                                <!-- CANCEL -->
                                                <button
                                                    @click="confirmModal = false"
                                                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200  dark:bg-gray-700 dark:hover:bg-gray-600 text-sm font-medium transition-all hover:scale-105 active:scale-95 cursor-pointer">

                                                    <i class="fa-solid fa-ban"></i>

                                                    Cancel
                                                </button>

                                                <!-- DELETE -->
                                                <button
                                                    @click="executeDelete()"
                                                    class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-medium flex items-center gap-2 transition-all shadow-sm hover:shadow-md hover:scale-105 active:scale-95 cursor-pointer">

                                                    <i x-show="deletingId !== selectedItem?.id" class="fa-solid fa-trash"></i>
                                                    <i x-show="deletingId === selectedItem?.id" class="fa-solid fa-spinner fa-spin"></i>

                                                    <span>Delete</span>
                                                </button>

                                                <!-- FORCE DELETE -->
                                                <button
                                                    x-show="forceMode"
                                                    @click="executeDelete(true)"
                                                    class="px-4 py-2 rounded-xl bg-red-700 hover:bg-red-800  text-white text-sm font-semibold transition-all shadow-md hover:shadow-lg hover:scale-105 active:scale-95 cursor-pointer">

                                                    <i class="fa-solid fa-bolt"></i>
                                                    <span>Force</span>
                                                </button>

                                            </div>

                                            <!-- WARNING BOX -->
                                            <div
                                                x-show="forceMode"
                                                class="text-xs text-red-600 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg px-3 py-2 text-center">

                                                ⚠️ This will not remove related transactions, but product history remains linked.
                                            </div>

                                        </div>

                                    </div>
                                </div>

                            </div>

                            <!-- QUICK ADD MODAL -->
                            <div
                                x-show="quickAddModal"
                                x-transition.opacity
                                x-cloak
                                class="fixed inset-0 flex items-center justify-center z-50">

                                <!-- BACKDROP -->
                                <div
                                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                                    @click="quickAddModal = false">
                                </div>

                                <!-- MODAL -->
                                <div
                                    x-transition.scale
                                    class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-6">

                                    <!-- HEADER -->
                                    <div class="flex items-center justify-between mb-4">

                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/20">
                                                <i class="fa-solid fa-bolt text-amber-600 dark:text-amber-400"></i>
                                            </div>

                                            <div>
                                                <h2 class="font-semibold text-lg">Quick Add</h2>
                                                <p class="text-xs text-gray-500">Add menu instantly</p>
                                            </div>
                                        </div>

                                        <button
                                            @click="quickAddModal=false"
                                            class="text-gray-400 hover:text-red-600 dark:hover:text-red-500 cursor-pointer">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>

                                    </div>

                                    <!-- FORM -->
                                    <div class="space-y-4">

                                        <!-- NAME -->
                                        <div>
                                            <label class="text-xs text-gray-500">Menu Name</label>
                                            <input
                                                x-model="quickForm.name"
                                                x-ref="quickName"
                                                @keydown.enter.prevent="submitQuickAdd()"
                                                placeholder="e.g. Cappuccino"
                                                class="w-full mt-1 px-3 py-2 rounded-xl border focus:ring-2 focus:ring-amber-400 dark:bg-gray-700">
                                        </div>

                                        <!-- PRICE -->
                                        <div>
                                            <label class="text-xs text-gray-500">Price</label>
                                            <input
                                                type="text"
                                                x-model="quickForm.price"
                                                @input="formatInputPrice"
                                                placeholder="25.000"
                                                class="w-full mt-1 px-3 py-2 rounded-xl border focus:ring-2 focus:ring-amber-400 dark:bg-gray-700">
                                        </div>

                                        <!-- CATEGORY -->
                                        <div>
                                            <label class="text-xs text-gray-500">Category</label>
                                            <select
                                                x-model="quickForm.category_id"
                                                class="w-full mt-1 px-3 py-2 rounded-xl border focus:ring-2 focus:ring-amber-400 dark:bg-gray-700">

                                                <option value="">Select category</option>

                                                <template x-for="cat in categories" :key="cat.id">
                                                    <option :value="cat.id" x-text="cat.name"></option>
                                                </template>

                                            </select>
                                        </div>

                                    </div>

                                    <!-- ACTION -->
                                    <div class="flex justify-end gap-3 mt-6">

                                        <button
                                            @click="quickAddModal=false"
                                            class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-sm transition cursor-pointer">
                                            Cancel
                                        </button>

                                        <button
                                            @click="submitQuickAdd()"
                                            :disabled="loading"
                                            class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm flex items-center gap-2 transition shadow-sm hover:shadow-md cursor-pointer">

                                            <i x-show="!loading" class="fa-solid fa-save"></i>
                                            <i x-show="loading" class="fa-solid fa-spinner fa-spin"></i>

                                            Save
                                        </button>

                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>

            </main>

        </div>

    </div>

    <!-- SCRIPT -->
    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>
    <script src="/rkd-cafe/public/assets/js/header.js"></script>
    <script src="/rkd-cafe/public/assets/js/sidebar-tooltip.js"></script>
    <script src="/rkd-cafe/public/assets/js/menu-manager.js"></script>

    <!-- GLOBAL TOOLTIP -->
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
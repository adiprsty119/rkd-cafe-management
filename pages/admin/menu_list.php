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

                        <button
                            class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg shadow-sm text-sm transition cursor-pointer">

                            <i class="fa-solid fa-plus"></i>

                            <?= $t['add_menu'] ?? 'Add Menu' ?>

                        </button>

                    </div>

                </div>

                <!-- SEARCH + FILTER -->
                <div class="flex flex-wrap gap-4">

                    <input
                        x-model="search"
                        type="text"
                        placeholder="<?= $t['search_menu'] ?? 'Search menu...' ?>"
                        class="border rounded-lg px-4 py-2 w-64 focus:ring-2 focus:ring-amber-400 dark:bg-gray-800">

                    <select x-model="category" class="border rounded-lg px-4 py-2 dark:bg-gray-800">

                        <option><?= $t['all_categories'] ?? 'All Categories' ?></option>
                        <option>Coffee</option>
                        <option>Bakery</option>

                    </select>

                    <select x-model="status" class="border rounded-lg px-4 py-2 dark:bg-gray-800">

                        <option><?= $t['status'] ?? 'Status' ?></option>
                        <option>Available</option>
                        <option>Unavailable</option>

                    </select>

                </div>

                <!-- MENU TABLE -->
                <div x-data="menuManager()" x-init="init()"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">

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
                                    <th class="p-3 text-left">#</th>
                                    <th class="p-3 text-left"><?= $t['image'] ?? 'Image' ?></th>
                                    <th class="p-3 text-left"><?= $t['menu'] ?? 'Menu' ?></th>
                                    <th class="p-3 text-left"><?= $t['category'] ?? 'Category' ?></th>
                                    <th class="p-3 text-left"><?= $t['price'] ?? 'Price' ?></th>
                                    <th class="p-3 text-left"><?= $t['status'] ?? 'Status' ?></th>
                                    <th class="p-3 text-left"><?= $t['action'] ?? 'Action' ?></th>
                                </tr>

                            </thead>

                            <tbody>

                                <template x-for="(item, index) in filtered" :key="item.id">

                                    <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                                        <td class="p-3 text-gray-400" x-text="index+1"></td>

                                        <td class="p-3">
                                            <img :src="item.image"
                                                class="w-12 h-12 rounded-lg object-cover shadow">
                                        </td>

                                        <td class="p-3 font-semibold" x-text="item.name"></td>

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
                                                :class="item.status === 'active'
                                    ? 'bg-green-100 text-green-600'
                                    : 'bg-gray-200 text-gray-500'"
                                                x-text="item.status">
                                            </span>
                                        </td>

                                        <td class="p-3 space-x-2">

                                            <button @click="edit(item)"
                                                class="text-blue-500 hover:underline text-xs">
                                                Edit
                                            </button>

                                            <button @click="remove(item.id)"
                                                class="text-red-500 hover:underline text-xs">
                                                Delete
                                            </button>

                                        </td>

                                    </tr>

                                </template>

                                <!-- EMPTY STATE -->
                                <tr x-show="filtered.length === 0">
                                    <td colspan="7" class="text-center py-10 text-gray-400">
                                        No menu found
                                    </td>
                                </tr>

                            </tbody>

                        </table>

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


    <script>
        function menuManager() {
            return {

                search: '',
                category: '',
                status: '',

                data: [],
                filtered: [],

                init() {
                    this.fetchMenu();
                    this.$watch('search', () => this.applyFilter());
                    this.$watch('category', () => this.applyFilter());
                    this.$watch('status', () => this.applyFilter());
                },

                async fetchMenu() {

                    try {
                        const res = await fetch('/rkd-cafe/api/menu/menu.php');
                        const json = await res.json();

                        this.data = json;
                        this.filtered = json;

                    } catch (e) {
                        console.error("Fetch menu error:", e);
                    }
                },

                applyFilter() {

                    this.filtered = this.data.filter(item => {

                        const s = this.search.toLowerCase();

                        const matchSearch = item.name.toLowerCase().includes(s);

                        const matchCategory = this.category ?
                            item.category === this.category :
                            true;

                        const matchStatus = this.status ?
                            item.status === this.status :
                            true;

                        return matchSearch && matchCategory && matchStatus;
                    });
                },

                formatRupiah(val) {
                    return 'Rp ' + Number(val).toLocaleString('id-ID');
                },

                edit(item) {
                    window.dispatchEvent(new CustomEvent('app:navigate', {
                        detail: {
                            url: `/rkd-cafe/pages/menu/edit.php?id=${item.id}`,
                            message: 'Opening menu...'
                        }
                    }));
                },

                async remove(id) {

                    if (!confirm('Delete this menu?')) return;

                    await fetch(`/rkd-cafe/api/menu/menu_delete.php?id=${id}`);

                    this.data = this.data.filter(i => i.id !== id);
                    this.applyFilter();
                }

            }
        }
    </script>
</body>

</html>
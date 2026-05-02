<?php
define('APP_INIT', true);

require $_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/middleware/AuthMiddleware.php';

/* ==========================
   LANGUAGE SYSTEM
========================== */

// bahasa default
$lang = $_GET['lang'] ?? 'id';

// validasi bahasa
if (!in_array($lang, ['id', 'en'])) {
    $lang = 'id';
}

// LOAD
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
$pageTitle = $currentMenu['menu']['title'] ?? 'Manage Users';
$breadcrumb = generateBreadcrumb($currentMenu);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users</title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js?v=<?= time() ?>"></script>

    <!-- Global Seacrh [global-search.js] -->
    <script defer src="/rkd-cafe/public/assets/js/global-search.js?v=<?= time() ?>"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    x-data="{ dark: localStorage.theme === 'dark', loadingTheme:false, sidebarOpen:<?= isset($sidebarCollapsed) && $sidebarCollapsed ? 'false' : 'true' ?>, ...usersPage() }"
    @toggle-theme.window="loadingTheme = true; setTimeout(() => {let newTheme = !dark; localStorage.theme = newTheme ? 'dark' : 'light'; location.reload();}, 800)"
    x-init=" init(); document.documentElement.classList.toggle('dark', dark)"
    x-effect="document.documentElement.classList.toggle('dark', dark)"
    :class="{ 'dark': dark }"
    class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white">

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

        <!-- MAIN -->
        <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden md:ml-0 transition-all duration-300">

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

                    <div class="w-full px-4 mt-3 transition-all duration-300 bg-transparent">
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

            <!-- CONTENT -->
            <main
                id="dashboardScroll"
                class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6 scrollbar-hide">

                <!-- HEADER -->
                <div class="flex flex-col mb-12 md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm px-6 py-5">

                    <!-- LEFT -->
                    <div class="flex items-center gap-4">

                        <!-- PAGE ICON -->
                        <div class="w-11 h-11 flex items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-500/20">

                            <i class="fa-solid fa-users text-blue-600 dark:text-blue-400"></i>

                        </div>

                        <!-- TITLE -->
                        <div>

                            <h1 class="text-xl md:text-2xl font-semibold tracking-tight">
                                <?= htmlspecialchars($pageTitle) ?>
                            </h1>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Kelola semua user dalam sistem
                            </p>

                        </div>

                    </div>

                    <!-- RIGHT ACTION -->
                    <div x-data="{loading:false}" class="flex items-center gap-3">

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

                <!-- FILTER -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex flex-col md:flex-row gap-4">

                    <input
                        type="text"
                        x-model="search"
                        placeholder="Cari nama / email..."
                        class="flex-1 px-4 py-2 rounded-lg border dark:bg-gray-700">

                    <select
                        x-model="statusFilter"
                        class="px-4 py-2 rounded-lg border dark:bg-gray-700">

                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="request_pending">Pending Approval</option>

                    </select>

                </div>

                <!-- BULK ACTION -->
                <div
                    x-show="selectedUsers.length > 0"
                    x-transition
                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-3 rounded-lg flex items-center justify-between">

                    <span class="text-sm">
                        <strong x-text="selectedUsers.length"></strong> user dipilih
                    </span>

                    <div class="flex gap-2">

                        <!-- ACTIVATE -->
                        <button
                            @click="bulkUpdate('active')"
                            class="px-3 py-1 text-xs bg-green-500 hover:bg-green-600 text-white rounded cursor-pointer">
                            Activate
                        </button>

                        <!-- DEACTIVATE -->
                        <button
                            @click="bulkUpdate('inactive')"
                            class="px-3 py-1 text-xs bg-gray-500 hover:bg-gray-600 text-white rounded cursor-pointer">
                            Deactivate
                        </button>

                        <!-- CLEAR -->
                        <button
                            @click="selectedUsers = []"
                            class="px-3 py-1 text-xs bg-red-500 hover:bg-red-600 text-white rounded cursor-pointer">
                            Clear
                        </button>

                    </div>
                </div>

                <!-- TABLE -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">

                    <table class="w-full text-sm">

                        <thead class="bg-gray-100 dark:bg-gray-700 text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 border-b sticky top-0 z-10">
                            <tr>
                                <th class="p-3 text-left">
                                    <input
                                        type="checkbox"
                                        x-ref="selectAll"
                                        @change="toggleAll($event)"
                                        class="w-4 h-4 cursor-pointer">
                                </th>
                                <th class="p-3 text-left">User</th>
                                <th class="p-3 text-left">Login</th>
                                <th class="p-3 text-left">Status</th>
                                <th class="p-3 text-left">Request</th>
                                <th class="p-3 text-left">Joined</th>
                                <th class="p-3 text-left">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <!-- LOADING -->
                            <template x-if="loading">
                                <tr>
                                    <td colspan="7" class="p-6">
                                        <div class="space-y-3 animate-pulse">
                                            <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                                            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <!-- EMPTY -->
                            <template x-if="!loading && filtered().length === 0">
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-gray-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fa-solid fa-users-slash text-2xl"></i>
                                            <span x-text="search ? 'User tidak ditemukan' : 'Tidak ada data user'"></span>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <!-- DATA -->
                            <template x-for="user in filtered()" :key="user.id">

                                <tr
                                    class="border-t transition duration-150"
                                    :class="{
                                        'bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400': user.request_status === 'pending',
                                        'hover:bg-gray-50 dark:hover:bg-gray-700': true
                                    }">

                                    <!-- CHECKBOX -->
                                    <td class="p-3">
                                        <input
                                            type="checkbox"
                                            :value="user.id"
                                            x-model="selectedUsers"
                                            class="w-4 h-4 cursor-pointer">
                                    </td>

                                    <!-- USER -->
                                    <td class="p-3">
                                        <div class="flex items-center gap-3">

                                            <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center text-xs font-bold">

                                                <template x-if="user.foto">
                                                    <img :src="user.foto" class="w-full h-full object-cover">
                                                </template>

                                                <template x-if="!user.foto">
                                                    <span x-text="(user.name || '?').trim().charAt(0) || '?'"></span>
                                                </template>

                                            </div>

                                            <div class="flex flex-col">
                                                <span class="font-semibold" x-text="user.name"></span>

                                                <span class="text-xs text-gray-400"
                                                    :class="user.email === '-' ? 'italic' : ''"
                                                    x-text="user.email || '-'">
                                                </span>
                                            </div>

                                        </div>
                                    </td>

                                    <!-- LOGIN METHOD -->
                                    <td class="p-3">
                                        <span
                                            class="px-2 py-1 text-xs rounded font-medium"
                                            :class="{
                                                'bg-blue-100 text-blue-700': user.login_method === 'google',
                                                'bg-gray-100 text-gray-600': user.login_method !== 'google'
                                            }"
                                            x-text="user.login_method === 'google' ? 'Google' : 'Manual'">
                                        </span>
                                    </td>

                                    <!-- STATUS -->
                                    <td class="p-3">
                                        <span
                                            class="px-2 py-1 text-xs rounded font-medium"
                                            :class="{
                                                'bg-green-100 text-green-700': user.status === 'active',
                                                'bg-gray-200 text-gray-600': user.status === 'inactive',
                                                'bg-yellow-100 text-yellow-700': user.status === 'pending'
                                            }"
                                            x-text="user.status">
                                        </span>
                                    </td>

                                    <!-- REQUEST -->
                                    <td class="p-3">
                                        <span
                                            class="px-2 py-1 text-xs rounded font-medium"
                                            :class="{
                                                'bg-yellow-100 text-yellow-700': user.request_status === 'pending',
                                                'bg-green-100 text-green-700': user.request_status === 'approved',
                                                'bg-red-100 text-red-700': user.request_status === 'rejected'
                                            }">

                                            <span x-show="!user.request_status">-</span>
                                            <span x-show="user.request_status" x-text="user.request_status"></span>

                                        </span>
                                    </td>

                                    <!-- JOINED -->
                                    <td class="p-3 text-xs text-gray-500">
                                        <span x-text="user.created_at 
                                            ? new Date(user.created_at).toLocaleDateString() 
                                            : '-'">
                                        </span>
                                    </td>

                                    <!-- ACTION -->
                                    <td class="p-3">
                                        <div class="flex items-center gap-2">

                                            <!-- APPROVE -->
                                            <button
                                                x-show="user.request_status === 'pending' && user.request_id"
                                                @click.stop="approve(user.request_id)"
                                                :disabled="Boolean(_approvingMap[user.request_id])"
                                                class="flex items-center gap-1 px-3 py-1 text-xs font-medium bg-green-500 hover:bg-green-600 text-white rounded disabled:opacity-50 disabled:cursor-not-allowed transition cursor-pointer">

                                                <!-- ICON -->
                                                <i class="fa-solid"
                                                    :class="_approvingMap?.[user.request_id] 
                                                        ? 'fa-spinner animate-spin' 
                                                        : 'fa-check'">
                                                </i>

                                                <!-- TEXT -->
                                                <span x-text="_approvingMap?.[user.request_id] 
                                                    ? 'Processing...' 
                                                    : 'Approve'">
                                                </span>

                                            </button>

                                            <!-- TOGGLE STATUS -->
                                            <button
                                                @click.stop="toggleStatus(user)"
                                                :disabled="Boolean(_togglingMap[user.id])"
                                                class="flex items-center gap-1 px-3 py-1 text-xs font-medium rounded transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                                :class="user.status === 'active' 
                                                    ? 'bg-gray-500 hover:bg-gray-600 text-white' 
                                                    : 'bg-green-500 hover:bg-green-600 text-white'">

                                                <!-- ICON -->
                                                <i class="fa-solid"
                                                    :class="_togglingMap[user.id] 
                                                        ? 'fa-spinner animate-spin' 
                                                        : (user.status === 'active' ? 'fa-ban' : 'fa-check')">
                                                </i>

                                                <!-- TEXT -->
                                                <span x-text="_togglingMap[user.id]
                                                    ? 'Processing...'
                                                    : (user.status === 'active' ? 'Deactivate' : 'Activate')">
                                                </span>

                                            </button>

                                        </div>
                                    </td>

                                </tr>

                            </template>

                        </tbody>

                    </table>

                </div>

            </main>

        </div>

    </div>

    <script src="/rkd-cafe/public/assets/js/toast.js?v=<?= time() ?>"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js?v=<?= time() ?>"></script>
    <script src="/rkd-cafe/public/assets/js/header.js?v=<?= time() ?>"></script>
    <script src="/rkd-cafe/public/assets/js/sidebar-tooltip.js?v=<?= time() ?>"></script>
    <script src="/rkd-cafe/public/assets/js/users-page.js?v=<?= time() ?>"></script>

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
        window.csrfToken = "<?= $_SESSION['csrf_token'] ?? '' ?>";
    </script>

</body>

</html>
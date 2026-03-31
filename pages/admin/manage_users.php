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

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    x-data="{ dark: localStorage.theme === 'dark', loading:false, loadingTheme:false, sidebarOpen:<?= isset($sidebarCollapsed) && $sidebarCollapsed ? 'false' : 'true' ?>, usersPage() }"
    @toggle-theme.window="loadingTheme = true; setTimeout(() => {let newTheme = !dark; localStorage.theme = newTheme ? 'dark' : 'light'; location.reload();}, 800)"
    x-init=" document.documentElement.classList.toggle('dark', dark)"
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
                x-data="dashboard()"
                x-init="init()"
                id="dashboardScroll"
                class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6 scrollbar-hide">

                <!-- HEADER -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow px-6 py-5 flex justify-between items-center">

                    <div class="flex items-center gap-4">
                        <div class="w-11 h-11 flex items-center justify-center bg-blue-100 dark:bg-blue-500/20 rounded-lg">
                            <i class="fa-solid fa-users text-blue-600"></i>
                        </div>

                        <div>
                            <h1 class="text-xl font-semibold"><?= $pageTitle ?></h1>
                            <p class="text-sm text-gray-500">Kelola semua user dalam sistem</p>
                        </div>
                    </div>

                    <button
                        @click="fetchUsers()"
                        class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">

                        <i class="fa-solid fa-rotate"></i>
                    </button>

                </div>

                <!-- FILTER -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex flex-col md:flex-row gap-4">

                    <input
                        type="text"
                        x-model="search"
                        placeholder="Cari nama / email..."
                        class="flex-1 px-4 py-2 rounded-lg border dark:bg-gray-700">

                    <select x-model="statusFilter"
                        class="px-4 py-2 rounded-lg border dark:bg-gray-700">

                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>

                    </select>

                </div>

                <!-- TABLE -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="p-3 text-left">Nama</th>
                                <th class="p-3 text-left">Email</th>
                                <th class="p-3 text-left">Status</th>
                                <th class="p-3 text-left">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <template x-for="user in filteredUsers()" :key="user.id">

                                <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700 transition">

                                    <td class="p-3 font-medium" x-text="user.name"></td>

                                    <td class="p-3 text-gray-500" x-text="user.email"></td>

                                    <!-- STATUS -->
                                    <td class="p-3">
                                        <span
                                            class="px-2 py-1 text-xs rounded"
                                            :class="statusClass(user.status)"
                                            x-text="user.status">
                                        </span>
                                    </td>

                                    <!-- ACTION -->
                                    <td class="p-3 flex gap-2">

                                        <button
                                            x-show="user.status === 'pending'"
                                            @click="approve(user.request_id)"
                                            class="px-3 py-1 text-xs bg-green-500 text-white rounded">

                                            Approve
                                        </button>

                                        <button
                                            @click="deleteUser(user.id)"
                                            class="px-3 py-1 text-xs bg-red-500 text-white rounded">

                                            Delete
                                        </button>

                                    </td>

                                </tr>

                            </template>

                        </tbody>

                    </table>

                </div>

            </main>

        </div>

    </div>

    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>
    <script src="/rkd-cafe/public/assets/js/header.js?v=<?= time() ?>"></script>
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
        function usersPage() {
            return {
                users: [],
                search: '',
                statusFilter: '',

                async init() {
                    await this.fetchUsers()
                },

                async fetchUsers() {
                    const res = await fetch('/auth?action=getUsers')
                    this.users = await res.json()
                },

                filteredUsers() {
                    return this.users.filter(u => {

                        let matchSearch =
                            u.name.toLowerCase().includes(this.search.toLowerCase()) ||
                            u.email.toLowerCase().includes(this.search.toLowerCase())

                        let matchStatus = !this.statusFilter || u.status === this.statusFilter

                        return matchSearch && matchStatus
                    })
                },

                statusClass(status) {
                    return {
                        'bg-yellow-100 text-yellow-600': status === 'pending',
                        'bg-green-100 text-green-600': status === 'active',
                        'bg-gray-200 text-gray-600': status === 'inactive'
                    }
                },

                async approve(id) {
                    await fetch('/auth?action=approve', {
                        method: 'POST',
                        body: new URLSearchParams({
                            request_id: id,
                            csrf_token: window.csrfToken
                        })
                    })

                    this.fetchUsers()
                },

                async deleteUser(id) {
                    if (!confirm("Yakin hapus user?")) return

                    await fetch('/auth?action=deleteUser', {
                        method: 'POST',
                        body: new URLSearchParams({
                            user_id: id,
                            csrf_token: window.csrfToken
                        })
                    })

                    this.fetchUsers()
                }
            }
        }
    </script>

</body>

</html>
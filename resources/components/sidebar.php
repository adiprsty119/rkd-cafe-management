<?php
$role = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);

function activeMenu($page)
{
    global $currentPage;

    if ($currentPage === $page) {
        return "bg-gray-200 dark:bg-gray-700 font-semibold";
    }

    return "";
}
?>

<div class="p-6 flex items-center justify-between border-b border-gray-700 dark:text-white transition-all duration-300 ease-in-out">
    <span x-show="sidebarOpen" class="text-2xl font-bold">
        ☕ <?= $t['app_name'] ?>
    </span>

    <button
        @click="sidebarOpen = !sidebarOpen;

        fetch('/rkd-cafe/api/sidebar/state.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({collapsed: !sidebarOpen})
        })"
        class="mr-4 text-xl text-gray-600 dark:text-gray-300 cursor-pointer">

        <i class="fa-solid fa-bars"></i>

    </button>
</div>

<nav class="flex-1 p-4 space-y-2 dark:text-white">

    <!-- DASHBOARD (SEMUA ROLE) -->
    <a href="/rkd-cafe/resources/views/dashboard/<?= $role ?>.php"
        class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700 <?= activeMenu($role . '.php') ?>">
        <i class="fa-solid fa-gauge mr-3"></i>
        <span x-show="sidebarOpen" class="ml-3">
            <?= $t['dashboard'] ?>
        </span>
    </a>

    <?php if ($role === 'admin'): ?>

        <!-- MENU MANAGEMENT -->
        <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">
            <i class="fa-solid fa-mug-hot mr-3"></i>
            <span x-show="sidebarOpen" class="ml-3">
                <?= $t['menu'] ?>
            </span>
        </a>

        <!-- CASHIER -->
        <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">
            <i class="fa-solid fa-cash-register mr-3"></i>
            <span x-show="sidebarOpen" class="ml-3">
                <?= $t['cashier'] ?>
            </span>
        </a>

        <!-- REPORTS -->
        <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">
            <i class="fa-solid fa-chart-line mr-3"></i>
            <span x-show="sidebarOpen" class="ml-3">
                <?= $t['reports'] ?>
            </span>
        </a>

        <!-- USERS -->
        <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">
            <i class="fa-solid fa-users mr-3"></i>
            <span x-show="sidebarOpen" class="ml-3">
                <?= $t['users'] ?>
            </span>
        </a>

    <?php endif; ?>

    <?php if ($role === 'kasir'): ?>

        <!-- POS -->
        <a href="#"
            class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">

            <i class="fa-solid fa-cash-register mr-3"></i>

            <span x-show="sidebarOpen" class="ml-3">
                POS
            </span>

        </a>

        <!-- ORDERS -->
        <a href="#"
            class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">

            <i class="fa-solid fa-receipt mr-3"></i>

            <span x-show="sidebarOpen" class="ml-3">
                Orders
            </span>

        </a>

        <!-- MENU -->
        <a href="#"
            class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">

            <i class="fa-solid fa-mug-hot mr-3"></i>

            <span x-show="sidebarOpen" class="ml-3">
                <?= $t['menu'] ?>
            </span>

        </a>

        <!-- CUSTOMERS -->
        <a href="#"
            class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">

            <i class="fa-solid fa-user-group mr-3"></i>

            <span x-show="sidebarOpen" class="ml-3">
                Customers
            </span>

        </a>

    <?php endif; ?>

    <?php if ($role === 'owner'): ?>

        <!-- SALES REPORT -->
        <a href="#"
            class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">

            <i class="fa-solid fa-chart-line mr-3"></i>

            <span x-show="sidebarOpen" class="ml-3">
                Sales Report
            </span>

        </a>


        <!-- REVENUE -->
        <a href="#"
            class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">

            <i class="fa-solid fa-money-bill-trend-up mr-3"></i>

            <span x-show="sidebarOpen" class="ml-3">
                Revenue
            </span>

        </a>


        <!-- MENU ANALYTICS -->
        <a href="#"
            class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">

            <i class="fa-solid fa-chart-pie mr-3"></i>

            <span x-show="sidebarOpen" class="ml-3">
                Menu Analytics
            </span>

        </a>


        <!-- CUSTOMERS -->
        <a href="#"
            class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">

            <i class="fa-solid fa-user-group mr-3"></i>

            <span x-show="sidebarOpen" class="ml-3">
                Customers
            </span>

        </a>

    <?php endif; ?>

    <!-- SETTINGS -->
    <a href="#"
        class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">

        <i class="fa-solid fa-gear mr-3"></i>

        <span x-show="sidebarOpen" class="ml-3">
            Settings
        </span>

    </a>

</nav>

<div class="p-4 border-t border-gray-700">
    <a href="/rkd-cafe/resources/views/auth/logout.php"
        class="flex items-center p-3 rounded-lg hover:bg-red-600 dark:text-white">

        <i class="fa-solid fa-right-from-bracket mr-3"></i>
        <span x-show="sidebarOpen" class="ml-3">
            <?= $t['logout'] ?>
        </span>

    </a>
</div>
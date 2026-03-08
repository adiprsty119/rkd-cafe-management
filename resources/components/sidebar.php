<?php

require_once __DIR__ . '/../../app/helpers/menu_helper.php';

$role = $_SESSION['role'] ?? 'guest';

/*
|--------------------------------------------------------------------------
| RBAC MENU CONFIGURATION
|--------------------------------------------------------------------------
*/

$menuConfig = [

    'admin' => [

        [
            'title' => $t['dashboard'],
            'icon'  => 'fa-gauge',
            'url'   => '/rkd-cafe/resources/views/dashboard/admin.php'
        ],

        [
            'title' => $t['menu'],
            'icon'  => 'fa-mug-hot',
            'url'   => '/rkd-cafe/resources/views/menu/index.php'
        ],

        [
            'title' => $t['cashier'],
            'icon'  => 'fa-cash-register',
            'url'   => '/rkd-cafe/resources/views/pos/index.php'
        ],

        [
            'title' => $t['reports'],
            'icon'  => 'fa-chart-line',
            'url'   => '/rkd-cafe/resources/views/reports/index.php'
        ],

        [
            'title' => $t['users'],
            'icon'  => 'fa-users',
            'url'   => '/rkd-cafe/resources/views/users/index.php'
        ]

    ],

    'kasir' => [

        [
            'title' => $t['dashboard'],
            'icon'  => 'fa-gauge',
            'url'   => '/rkd-cafe/resources/views/dashboard/kasir.php'
        ],

        [
            'title' => 'POS',
            'icon'  => 'fa-cash-register',
            'url'   => '/rkd-cafe/resources/views/pos/index.php'
        ],

        [
            'title' => 'Orders',
            'icon'  => 'fa-receipt',
            'url'   => '/rkd-cafe/resources/views/orders/index.php'
        ],

        [
            'title' => $t['menu'],
            'icon'  => 'fa-mug-hot',
            'url'   => '/rkd-cafe/resources/views/menu/index.php'
        ],

        [
            'title' => 'Customers',
            'icon'  => 'fa-user-group',
            'url'   => '/rkd-cafe/resources/views/customers/index.php'
        ]

    ],

    'owner' => [

        [
            'title' => $t['dashboard'],
            'icon'  => 'fa-gauge',
            'url'   => '/rkd-cafe/resources/views/dashboard/owner.php'
        ],

        [
            'title' => 'Sales Report',
            'icon'  => 'fa-chart-line',
            'url'   => '/rkd-cafe/resources/views/reports/sales.php'
        ],

        [
            'title' => 'Revenue',
            'icon'  => 'fa-money-bill-trend-up',
            'url'   => '/rkd-cafe/resources/views/reports/revenue.php'
        ],

        [
            'title' => 'Menu Analytics',
            'icon'  => 'fa-chart-pie',
            'url'   => '/rkd-cafe/resources/views/analytics/menu.php'
        ],

        [
            'title' => 'Customers',
            'icon'  => 'fa-user-group',
            'url'   => '/rkd-cafe/resources/views/customers/index.php'
        ]

    ]

];

$menus = $menuConfig[$role] ?? [];

?>

<!-- SIDEBAR HEADER -->

<div class="p-6 flex items-center justify-between border-b border-gray-700 dark:text-white">

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


<!-- SIDEBAR MENU -->

<nav class="flex-1 p-4 space-y-2 dark:text-white">

    <?php foreach ($menus as $menu): ?>

        <a href="<?= $menu['url'] ?>"
            class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700 <?= activeMenu($menu['url']) ?>">

            <i class="fa-solid <?= $menu['icon'] ?> mr-3"></i>

            <span x-show="sidebarOpen" class="ml-3">
                <?= $menu['title'] ?>
            </span>

        </a>

    <?php endforeach; ?>

    <!-- SETTINGS -->

    <a href="/rkd-cafe/resources/views/settings/index.php"
        class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">

        <i class="fa-solid fa-gear mr-3"></i>

        <span x-show="sidebarOpen" class="ml-3">
            Settings
        </span>

    </a>

</nav>


<!-- LOGOUT -->

<div class="p-4 border-t border-gray-700">

    <a href="/rkd-cafe/resources/views/auth/logout.php"
        class="flex items-center p-3 rounded-lg hover:bg-red-600 dark:text-white">

        <i class="fa-solid fa-right-from-bracket mr-3"></i>

        <span x-show="sidebarOpen" class="ml-3">
            <?= $t['logout'] ?>
        </span>

    </a>

</div>
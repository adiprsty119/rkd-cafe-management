<?php

require_once __DIR__ . '/../../app/helpers/menu_helper.php';
require_once __DIR__ . '/../../app/helpers/childmenu_helper.php';

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
            'icon' => 'fa-gauge',
            'url' => '/rkd-cafe/resources/views/dashboard/admin.php'
        ],

        [
            'title' => 'Menu Management',
            'icon' => 'fa-mug-hot',
            'children' => [

                [
                    'title' => 'Menu List',
                    'icon' => 'fa-list',
                    'url' => '/rkd-cafe/pages/admin/menu_list.php'
                ],

                [
                    'title' => 'Categories',
                    'icon' => 'fa-layer-group',
                    'url' => '/rkd-cafe/pages/admin/categories.php'
                ]

            ]
        ],

        [
            'title' => 'POS',
            'icon' => 'fa-cash-register',
            'children' => [

                [
                    'title' => 'Cashier',
                    'icon' => 'fa-computer',
                    'url' => '/rkd-cafe/pages/admin/cashier.php'
                ],

                [
                    'title' => 'Orders',
                    'icon' => 'fa-receipt',
                    'url' => '/rkd-cafe/pages/admin/orders.php'
                ]

            ]
        ],

        [
            'title' => 'Reports',
            'icon' => 'fa-chart-line',
            'children' => [

                [
                    'title' => 'Sales Report',
                    'icon' => 'fa-chart-column',
                    'url' => '/rkd-cafe/pages/admin/sales_report.php'
                ],

                [
                    'title' => 'Revenue',
                    'icon' => 'fa-money-bill-trend-up',
                    'url' => '/rkd-cafe/pages/admin/revenue.php'
                ]

            ]
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
            'children' => [

                [
                    'title' => 'Cashier',
                    'icon'  => 'fa-computer',
                    'url' => '/rkd-cafe/pages/kasir/cashier.php'
                ],

                [
                    'title' => 'Orders',
                    'icon'  => 'fa-receipt',
                    'url' => '/rkd-cafe/pages/kasir/orders.php'
                ]

            ]
        ],

        [
            'title' => $t['menu'],
            'icon'  => 'fa-mug-hot',
            'url'   => '/rkd-cafe/pages/kasir/menu.php'
        ],

        [
            'title' => 'Customers',
            'icon'  => 'fa-user-group',
            'url'   => '/rkd-cafe/pages/kasir/customers.php'
        ]

    ],

    'owner' => [

        [
            'title' => $t['dashboard'],
            'icon'  => 'fa-gauge',
            'url'   => '/rkd-cafe/resources/views/dashboard/owner.php'
        ],

        [
            'title' => 'Reports',
            'icon'  => 'fa-chart-line',
            'children' => [

                [
                    'title' => 'Sales Report',
                    'icon'  => 'fa-chart-column',
                    'url' => '/rkd-cafe/pages/owner/sales_report.php'
                ],

                [
                    'title' => 'Revenue',
                    'icon'  => 'fa-money-bill-trend-up',
                    'url' => '/rkd-cafe/pages/owner/revenue.php'
                ]

            ]
        ],

        [
            'title' => 'Analytics',
            'icon'  => 'fa-chart-pie',
            'children' => [

                [
                    'title' => 'Menu Analytics',
                    'icon'  => 'fa-chart-simple',
                    'url' => '/rkd-cafe/pages/owner/analytics.php'
                ]

            ]
        ],

        [
            'title' => 'Customers',
            'icon'  => 'fa-user-group',
            'url'   => '/rkd-cafe/pages/owner/customers.php'
        ]

    ]

];

$menus = $menuConfig[$role] ?? [];

?>

<div
    x-show="sidebarOpen"
    @click="sidebarOpen=false"
    class="fixed inset-0 bg-black/40 z-30 md:hidden"
    x-transition.opacity>
</div>

<!-- SIDEBAR HEADER -->
<div class="flex items-center justify-between px-4 py-4 border-b border-gray-200 dark:border-gray-700 dark:text-white">

    <span x-show="sidebarOpen" class="text-2xl font-bold">
        ☕ <?= $t['app_name'] ?>
    </span>

    <button
        @click="sidebarOpen = !sidebarOpen;

        if(window.innerWidth >= 768){
            fetch('/rkd-cafe/api/sidebar/state.php',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({collapsed: !sidebarOpen})
            });
        }"

        class="mr-4 text-xl text-gray-600 dark:text-gray-300 cursor-pointer">

        <i class="fa-solid fa-bars"></i>

    </button>

</div>


<!-- SIDEBAR MENU -->

<nav class="flex-1 p-2 md:p-4 space-y-2 dark:text-white overflow-y-auto">

    <?php foreach ($menus as $menu): ?>

        <?php if (isset($menu['children'])): ?>

            <div x-data="{open: <?= isChildActive($menu['children']) ? 'true' : 'false' ?>}"
                class="relative">

                <button
                    @click="open=!open"
                    data-tooltip="<?= $menu['title'] ?>"
                    class="flex items-center w-full px-3 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">

                    <i class="fa-solid <?= $menu['icon'] ?> w-5 text-center"></i>

                    <span
                        class="ml-3 flex-1 text-left transition-opacity duration-200"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 md:hidden'">
                        <?= $menu['title'] ?>
                    </span>

                    <i x-show="sidebarOpen"
                        class="fa-solid fa-chevron-down text-xs transition-transform"
                        :class="{'rotate-180':open}">
                    </i>

                </button>

                <!-- SUBMENU -->
                <div
                    x-show="open && sidebarOpen"
                    x-transition
                    class="ml-8 mt-1 space-y-1">

                    <?php foreach ($menu['children'] as $child): ?>

                        <a
                            href="<?= $child['url'] ?>"
                            data-tooltip="<?= $child['title'] ?>"
                            class="flex items-center w-full px-3 py-3 rounded-lg border-l-4 border-transparent hover:bg-gray-100 dark:hover:bg-gray-700 <?= activeMenu($child['url']) ?>">

                            <i class="fa-solid <?= $child['icon'] ?? 'fa-circle' ?> w-4 text-center"></i>

                            <span class="ml-2"><?= $child['title'] ?></span>

                        </a>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php else: ?>

            <a
                href="<?= $menu['url'] ?>"
                data-tooltip="<?= $menu['title'] ?>"
                class="flex items-center w-full px-3 py-3 rounded-lg border-l-4 border-transparent transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 <?= activeMenu($menu['url']) ?>">

                <i class="fa-solid <?= $menu['icon'] ?> w-5 text-center"></i>

                <span
                    class="ml-3 flex-1 transition-opacity duration-200"
                    x-show="sidebarOpen">

                    <?= $menu['title'] ?>

                </span>

            </a>

        <?php endif; ?>

    <?php endforeach; ?>


    <!-- ============================= -->
    <!-- GLOBAL MENU -->
    <!-- ============================= -->
    <?php
    $settingsUrl = "/rkd-cafe/pages/$role/settings.php";
    ?>

    <a
        href="<?= $settingsUrl ?>"
        data-tooltip="Settings"
        class="flex items-center w-full px-3 py-3 rounded-lg border-l-4 border-transparent hover:bg-gray-100 dark:hover:bg-gray-700 <?= activeMenu('settings.php') ?>">

        <i class="fa-solid fa-gear w-5 text-center"></i>

        <span
            class="ml-3 flex-1 transition-opacity duration-200"
            x-show="sidebarOpen">
            Settings
        </span>

    </a>

</nav>


<!-- LOGOUT -->
<div class="p-4 border-t border-gray-200 dark:border-gray-700">

    <a
        href="/rkd-cafe/resources/views/auth/logout.php"
        data-tooltip="Logout"
        class="flex items-center w-full px-3 py-3 rounded-lg hover:bg-red-600 dark:text-white cursor-pointer">

        <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>

        <span
            class="ml-3 flex-1 transition-opacity duration-200"
            x-show="sidebarOpen">

            <?= $t['logout'] ?>

        </span>

    </a>

</div>
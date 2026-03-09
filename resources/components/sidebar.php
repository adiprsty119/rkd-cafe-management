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
                    'url' => '/rkd-cafe/resources/views/menu/index.php'
                ],

                [
                    'title' => 'Categories',
                    'url' => '/rkd-cafe/resources/views/menu/categories.php'
                ]

            ]
        ],

        [
            'title' => 'POS',
            'icon' => 'fa-cash-register',
            'children' => [

                [
                    'title' => 'Cashier',
                    'url' => '/rkd-cafe/resources/views/pos/index.php'
                ],

                [
                    'title' => 'Orders',
                    'url' => '/rkd-cafe/resources/views/orders/index.php'
                ]

            ]
        ],

        [
            'title' => 'Reports',
            'icon' => 'fa-chart-line',
            'children' => [

                [
                    'title' => 'Sales Report',
                    'url' => '/rkd-cafe/resources/views/reports/sales.php'
                ],

                [
                    'title' => 'Revenue',
                    'url' => '/rkd-cafe/resources/views/reports/revenue.php'
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
                    'url' => '/rkd-cafe/resources/views/pos/index.php'
                ],

                [
                    'title' => 'Orders',
                    'url' => '/rkd-cafe/resources/views/orders/index.php'
                ]

            ]
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
            'title' => 'Reports',
            'icon'  => 'fa-chart-line',
            'children' => [

                [
                    'title' => 'Sales Report',
                    'url' => '/rkd-cafe/resources/views/reports/sales.php'
                ],

                [
                    'title' => 'Revenue',
                    'url' => '/rkd-cafe/resources/views/reports/revenue.php'
                ]

            ]
        ],

        [
            'title' => 'Analytics',
            'icon'  => 'fa-chart-pie',
            'children' => [

                [
                    'title' => 'Menu Analytics',
                    'url' => '/rkd-cafe/resources/views/analytics/menu.php'
                ]

            ]
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

<nav class="flex-1 p-2 md:p-4 space-y-2 dark:text-white overflow-y-auto overflow-x-visible">

    <?php foreach ($menus as $menu): ?>

        <?php if (isset($menu['children'])): ?>

            <div x-data="{open: <?= isChildActive($menu['children']) ? 'true' : 'false' ?>, hovered:false}"
                @mouseenter="hovered=true"
                @mouseleave="hovered=false"
                class="relative">

                <button
                    @click="open=!open"
                    class="flex items-center w-full gap-3 px-3 py-3 rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">

                    <i class="fa-solid <?= $menu['icon'] ?> mr-3"></i>

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

                <!-- TOOLTIP -->
                <div
                    x-show="!sidebarOpen && hovered"
                    x-transition.opacity.scale.origin.left
                    class="hidden md:block absolute left-16 top-1/2 -translate-y-1/2 bg-black text-white text-xs px-2 py-1 rounded shadow-lg whitespace-nowrap pointer-events-none">

                    <?= $menu['title'] ?>

                </div>

                <!-- SUBMENU -->
                <div
                    x-show="open && sidebarOpen"
                    x-transition
                    class="ml-8 mt-1 space-y-1">

                    <?php foreach ($menu['children'] as $child): ?>

                        <a
                            href="<?= $child['url'] ?>"
                            class="block p-2 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700 <?= activeMenu($child['url']) ?>">

                            <?= $child['title'] ?>

                        </a>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php else: ?>

            <a
                href="<?= $menu['url'] ?>"
                class="relative flex items-center p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 <?= activeMenu($menu['url']) ?>"
                x-data="{hovered:false}"
                @mouseenter="hovered=true"
                @mouseleave="hovered=false">

                <i class="fa-solid <?= $menu['icon'] ?> mr-3"></i>

                <span x-show="sidebarOpen" class="ml-3">
                    <?= $menu['title'] ?>
                </span>

                <!-- TOOLTIP -->

                <div
                    x-show="!sidebarOpen && hovered"
                    x-transition
                    class="absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-black text-white text-xs px-2 py-1 rounded shadow-lg whitespace-nowrap">

                    <?= $menu['title'] ?>

                </div>

            </a>

        <?php endif; ?>

    <?php endforeach; ?>


    <!-- ============================= -->
    <!-- GLOBAL MENU -->
    <!-- ============================= -->

    <a
        href="/rkd-cafe/resources/views/settings/index.php"
        x-data="{hovered:false}"
        @mouseenter="hovered=true"
        @mouseleave="hovered=false"
        class="relative flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700 <?= activeMenu('index.php') ?>">

        <i class="fa-solid fa-gear mr-3"></i>

        <span x-show="sidebarOpen" class="ml-3">
            Settings
        </span>

        <!-- TOOLTIP -->
        <div
            x-show="!sidebarOpen && hovered"
            x-transition.opacity.scale.origin.left
            class="hidden md:block absolute left-full ml-3 top-1/2 -translate-y-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded shadow-lg whitespace-nowrap pointer-events-none z-50">

            Settings

        </div>

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
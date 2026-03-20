<?php

// load translation

$lang = $lang ?? 'en';
$allowedLang = ['id', 'en'];

if (!in_array($lang, $allowedLang, true)) {
    $lang = 'en';
}

$langPath = realpath(__DIR__ . '/../resources/lang/' . $lang . '.php');

if ($langPath === false) {
    $t = [];
} else {
    $t = require $langPath;
}


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
            'url' => '/rkd-cafe/resources/views/dashboard/admin.php',
            'permission' => 'view_dashboard'
        ],

        [
            'title' => 'Menu Management',
            'icon' => 'fa-mug-hot',
            'prefix' => 'menu_list|categories',
            'children' => [

                [
                    'title' => 'Menu List',
                    'icon' => 'fa-list',
                    'url' => '/rkd-cafe/pages/admin/menu_list.php',
                    'permission' => 'view_menu_list'
                ],

                [
                    'title' => 'Categories',
                    'icon' => 'fa-layer-group',
                    'url' => '/rkd-cafe/pages/admin/categories.php',
                    'permission' => 'view_categories'
                ]

            ]
        ],

        [
            'title' => 'POS',
            'icon' => 'fa-cash-register',
            'prefix' => 'cashier|orders',
            'children' => [

                [
                    'title' => 'Cashier',
                    'icon' => 'fa-computer',
                    'url' => '/rkd-cafe/pages/admin/cashier.php',
                    'permission' => 'view_cashier'
                ],

                [
                    'title' => 'Orders',
                    'icon' => 'fa-receipt',
                    'url' => '/rkd-cafe/pages/admin/orders.php',
                    'permission' => 'view_orders'
                ]

            ]
        ],

        [
            'title' => 'Reports',
            'icon' => 'fa-chart-line',
            'prefix' => 'sales_report|revenue',
            'children' => [

                [
                    'title' => 'Sales Report',
                    'icon' => 'fa-chart-column',
                    'url' => '/rkd-cafe/pages/admin/sales_report.php',
                    'permission' => 'view_sales_report'
                ],

                [
                    'title' => 'Revenue',
                    'icon' => 'fa-money-bill-trend-up',
                    'url' => '/rkd-cafe/pages/admin/revenue.php',
                    'permission' => 'view_revenue'
                ]

            ]
        ]

    ],

    'kasir' => [

        [
            'title' => $t['dashboard'],
            'icon'  => 'fa-gauge',
            'url'   => '/rkd-cafe/resources/views/dashboard/kasir.php',
            'permission' => 'view_dashboard'
        ],

        [
            'title' => 'POS',
            'icon'  => 'fa-cash-register',
            'prefix' => 'cashier|orders',
            'children' => [

                [
                    'title' => 'Cashier',
                    'icon'  => 'fa-computer',
                    'url' => '/rkd-cafe/pages/kasir/cashier.php',
                    'permission' => 'view_cashier'
                ],

                [
                    'title' => 'Orders',
                    'icon'  => 'fa-receipt',
                    'url' => '/rkd-cafe/pages/kasir/orders.php',
                    'permission' => 'view_orders'
                ]

            ]
        ],

        [
            'title' => $t['menu'],
            'icon'  => 'fa-mug-hot',
            'url'   => '/rkd-cafe/pages/kasir/menu.php',
            'permission' => 'view_menu'
        ],

        [
            'title' => 'Customers',
            'icon'  => 'fa-user-group',
            'url'   => '/rkd-cafe/pages/kasir/customers.php',
            'permission' => 'view_customers'
        ]

    ],

    'owner' => [

        [
            'title' => $t['dashboard'],
            'icon'  => 'fa-gauge',
            'url'   => '/rkd-cafe/resources/views/dashboard/owner.php',
            'permission' => 'view_dashboard'
        ],

        [
            'title' => 'Reports',
            'icon'  => 'fa-chart-line',
            'prefix' => 'sales_report|revenue',
            'children' => [

                [
                    'title' => 'Sales Report',
                    'icon'  => 'fa-chart-column',
                    'url' => '/rkd-cafe/pages/owner/sales_report.php',
                    'permission' => 'view_sales_report'
                ],

                [
                    'title' => 'Revenue',
                    'icon'  => 'fa-money-bill-trend-up',
                    'url' => '/rkd-cafe/pages/owner/revenue.php',
                    'permission' => 'view_revenue'
                ]

            ]
        ],

        [
            'title' => 'Analytics',
            'icon'  => 'fa-chart-pie',
            'prefix' => 'analytics',
            'children' => [

                [
                    'title' => 'Performance Analytics',
                    'icon'  => 'fa-chart-simple',
                    'url' => '/rkd-cafe/pages/owner/analytics.php',
                    'permission' => 'view_performance_analytics'
                ],

                [
                    'title' => 'Sales Analytics',
                    'icon'  => 'fa-chart-line',
                    'url' => '/rkd-cafe/pages/owner/analytics_sales.php',
                    'permission' => 'view_sales_analytics'
                ],

                [
                    'title' => 'Customer Analytics',
                    'icon'  => 'fa-users',
                    'url' => '/rkd-cafe/pages/owner/analytics_customer.php',
                    'permission' => 'view_customer_analytics'
                ]

            ]
        ],

        [
            'title' => 'Customers',
            'icon'  => 'fa-user-group',
            'url'   => '/rkd-cafe/pages/owner/customers.php',
            'permission' => 'view_customer'
        ]

    ]

];

return $menuConfig;

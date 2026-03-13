<?php

// load translation
$allowedLang = ['id', 'en'];
if (!in_array($lang, $allowedLang, true)) {
    $lang = 'en';
}
$t = require __DIR__ . '/../resources/lang/' . $lang . '.php';


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
            'prefix' => 'menu_list|categories',
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
            'prefix' => 'cashier|orders',
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
            'prefix' => 'sales_report|revenue',
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
            'prefix' => 'cashier|orders',
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
            'prefix' => 'sales_report|revenue',
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
            'prefix' => 'analytics',
            'children' => [

                [
                    'title' => 'Performance Analytics',
                    'icon'  => 'fa-chart-simple',
                    'url' => '/rkd-cafe/pages/owner/analytics.php'
                ],

                [
                    'title' => 'Sales Analytics',
                    'icon'  => 'fa-chart-line',
                    'url' => '/rkd-cafe/pages/owner/analytics_sales.php'
                ],

                [
                    'title' => 'Customer Analytics',
                    'icon'  => 'fa-users',
                    'url' => '/rkd-cafe/pages/owner/analytics_customer.php'
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

return $menuConfig;

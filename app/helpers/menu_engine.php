<?php

function getMenusByRole($role)
{
    $lang = $_SESSION['lang'] ?? 'en';

    $menuConfig = require __DIR__ . '/../../config/sidebar_menu.php';

    return $menuConfig[$role] ?? [];
}


function findMenuByRoute(array $menus)
{
    $current = currentRoute();

    foreach ($menus as $menu) {

        if (isset($menu['url']) && basename($menu['url']) === $current) {
            return [
                'menu'   => $menu,
                'parent' => null
            ];
        }

        if (!empty($menu['children'])) {

            foreach ($menu['children'] as $child) {

                if (basename($child['url']) === $current) {
                    return [
                        'menu'   => $child,
                        'parent' => $menu
                    ];
                }
            }
        }
    }

    return null;
}


function generateBreadcrumb($menuData)
{
    if (!$menuData) return [];

    $breadcrumb = [];

    if (!empty($menuData['parent'])) {
        $breadcrumb[] = $menuData['parent'];
    }

    $breadcrumb[] = $menuData['menu'];

    return $breadcrumb;
}

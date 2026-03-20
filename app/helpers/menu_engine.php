<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function getMenusByRole($role): array
{
    $permissions = $_SESSION['permissions'] ?? [];
    $current = currentRoute();

    $menuConfig = require __DIR__ . '/../../config/sidebar_menu.php';

    $menus = $menuConfig[$role] ?? []; // ✅ BENAR
    $filteredMenus = [];

    foreach ($menus as $menu) { // 🔥 FIX DI SINI

        if (!empty($menu['permission']) && !in_array($menu['permission'], $permissions, true)) {
            continue;
        }

        $menu['active'] = false;

        if (!empty($menu['children'])) {

            $children = [];

            foreach ($menu['children'] as $child) {

                if (empty($child['permission']) || in_array($child['permission'], $permissions, true)) {

                    $child['active'] = basename(parse_url($child['url'], PHP_URL_PATH)) === $current;

                    if ($child['active']) {
                        $menu['active'] = true;
                    }

                    $children[] = $child;
                }
            }

            if (!empty($children)) {
                $menu['children'] = $children;
                $filteredMenus[] = $menu;
            }
        } else {

            $menu['active'] = isset($menu['url']) &&
                basename(parse_url($menu['url'], PHP_URL_PATH)) === $current;

            $filteredMenus[] = $menu;
        }
    }

    return $filteredMenus;
}


function findMenuByRoute(array $menus)
{
    $current = currentRoute();

    foreach ($menus as $menu) {

        if (isset($menu['url'])) {

            $menuRoute = basename(parse_url($menu['url'], PHP_URL_PATH)); // 🔥 FIX

            if ($menuRoute === $current) {
                return [
                    'menu'   => $menu,
                    'parent' => null
                ];
            }
        }

        if (!empty($menu['children'])) {

            foreach ($menu['children'] as $child) {

                $childRoute = basename(parse_url($child['url'], PHP_URL_PATH)); // 🔥 FIX

                if ($childRoute === $current) {
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

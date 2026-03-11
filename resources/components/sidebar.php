<?php

require_once __DIR__ . '/../../app/helpers/menu_helper.php';
require_once __DIR__ . '/../../app/helpers/childmenu_helper.php';
require_once __DIR__ . '/../../app/helpers/icon_helper.php';
require_once __DIR__ . '/../../app/helpers/menu_engine.php';

$allowedRoles = ['admin', 'kasir', 'owner'];
$role = $_SESSION['role'] ?? 'guest';

if (!in_array($role, $allowedRoles)) {
    $role = 'guest';
}

$lang = $_SESSION['lang'] ?? 'en';
$menuConfig = require __DIR__ . '/../../config/sidebar_menu.php';
$menus = $menuConfig[$role] ?? [];
$currentMenu = findMenuByRoute($menus);

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
                headers:{'Content-Type':'application/json', 'X-CSRF-TOKEN': window.csrfToken},
                body: JSON.stringify({collapsed: !sidebarOpen})
            });
        }"

        class="mr-4 text-xl text-gray-600 dark:text-gray-300 cursor-pointer"
        :class="!sidebarOpen ? 'ml-4' : ''">

        <i class="fa-solid fa-bars"></i>

    </button>

</div>


<!-- SIDEBAR MENU -->

<nav class="flex-1 p-2 md:p-4 space-y-2 dark:text-white overflow-y-auto">

    <?php foreach ($menus as $menu): ?>

        <?php if (isset($menu['children'])): ?>

            <div x-data="{open: <?= isActivePrefix($menu['prefix'] ?? '') ? 'true' : 'false' ?>}"
                class="relative">

                <button
                    @click="open=!open"
                    data-tooltip="<?= htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8') ?>"
                    class="flex items-center w-full px-3 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer <?= activeParent($menu['prefix'] ?? '') ?>">

                    <i class="fa-solid <?= $menu['icon'] ?> w-5 text-center <?= iconSpacing($menu) ?>"></i>

                    <span
                        class="ml-3 flex-1 text-left transition-opacity duration-200"
                        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 md:hidden'">
                        <?= htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8') ?>
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

                            <i class="fa-solid <?= $child['icon'] ?? 'fa-circle' ?> w-5 text-center"></i>

                            <span class="ml-2"><?= $child['title'] ?></span>

                        </a>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php else: ?>

            <a
                href="<?= $menu['url'] ?>"
                data-tooltip="<?= htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8') ?>"
                class="flex items-center w-full px-3 py-3 rounded-lg border-l-4 border-transparent transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 <?= activeMenu($menu['url']) ?>">

                <i class="fa-solid <?= $menu['icon'] ?> w-5 text-center"></i>

                <span
                    class="ml-3 flex-1 transition-opacity duration-200"
                    x-show="sidebarOpen">

                    <?= htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8') ?>

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
        class="flex items-center w-full px-3 py-3 rounded-lg border-l-4 border-transparent hover:bg-gray-100 dark:hover:bg-gray-700 <?= activeMenu($settingsUrl) ?>">

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

    <form method="POST" action="/rkd-cafe/resources/views/auth/logout.php">

        <input type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <button
            type="submit"
            data-tooltip="Logout"
            class="flex items-center w-full px-3 py-3 rounded-lg hover:bg-red-600 dark:text-white cursor-pointer">

            <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>

            <span
                class="-ml-24 flex-1 transition-opacity duration-200"
                x-show="sidebarOpen">

                <?= htmlspecialchars($t['logout'] ?? 'Logout', ENT_QUOTES, 'UTF-8') ?>

            </span>

        </button>

    </form>

</div>
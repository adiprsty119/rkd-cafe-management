<div class="p-6 flex items-center justify-between border-b border-gray-700 dark:text-white transition-all duration-300 ease-in-out">
    <span x-show="sidebarOpen" class="text-2xl font-bold">
        ☕ <?= $t['app_name'] ?>
    </span>

    <button
        @click="sidebarOpen = !sidebarOpen;

        fetch('api/sidebar_state.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({collapsed: !sidebarOpen})
        })"
        class="mr-4 text-xl text-gray-600 dark:text-gray-300 cursor-pointer">

        <i class="fa-solid fa-bars"></i>

    </button>
</div>

<nav class="flex-1 p-4 space-y-2 dark:text-white">

    <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">
        <i class="fa-solid fa-gauge mr-3"></i>
        <span x-show="sidebarOpen" class="ml-3">
            <?= $t['dashboard'] ?>
        </span>
    </a>

    <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">
        <i class="fa-solid fa-mug-hot mr-3"></i>
        <span x-show="sidebarOpen" class="ml-3">
            <?= $t['menu'] ?>
        </span>
    </a>

    <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">
        <i class="fa-solid fa-cash-register mr-3"></i>
        <span x-show="sidebarOpen" class="ml-3">
            <?= $t['cashier'] ?>
        </span>
    </a>

    <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">
        <i class="fa-solid fa-chart-line mr-3"></i>
        <span x-show="sidebarOpen" class="ml-3">
            <?= $t['reports'] ?>
        </span>
    </a>

    <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-100 hover:dark:bg-gray-700">
        <i class="fa-solid fa-users mr-3"></i>
        <span x-show="sidebarOpen" class="ml-3">
            <?= $t['users'] ?>
        </span>
    </a>

</nav>

<div class="p-4 border-t border-gray-700">
    <a href="auth/logout.php"
        class="flex items-center p-3 rounded-lg hover:bg-red-600 dark:text-white">

        <i class="fa-solid fa-right-from-bracket mr-3"></i>
        <span x-show="sidebarOpen" class="ml-3">
            <?= $t['logout'] ?>
        </span>

    </a>
</div>
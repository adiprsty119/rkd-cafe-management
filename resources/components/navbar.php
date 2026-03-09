<?php

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id=? AND is_read=0");
$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();
$notificationCount = $row['total'];


$foto = "/rkd-cafe/public/assets/images/user.png";

if (!empty($_SESSION['foto'])) {

    if ($_SESSION['login_method'] === 'google') {
        $foto = $_SESSION['foto'];
    } else {
        $foto = "/rkd-cafe/public/storage/users/" . $_SESSION['foto'];
    }
}

$displayName = ucfirst($_SESSION['username']);
?>

<header class="bg-white dark:bg-gray-800 p-4 flex items-center justify-between shadow-2xl">

    <div class="flex items-center gap-3">

        <button
            @click="sidebarOpen = !sidebarOpen"
            class="md:hidden mr-3 text-xl text-gray-600 dark:text-gray-300">

            <i class="fa-solid fa-bars"></i>

        </button>

        <!-- TITLE -->
        <h1 class="text-xl font-semibold cursor-pointer">
            <?= $t['dashboard'] ?>
        </h1>

    </div>

    <!-- RIGHT MENU -->
    <div class="flex items-center space-x-3 md:space-x-6">

        <!-- SEARCH -->
        <div class="relative">

            <input
                type="text"
                placeholder="<?= $t['search'] ?>..."
                class="w-32 sm:w-48 lg:w-96 px-4 py-2 pr-10 border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 dark:bg-gray-700 dark:text-white">

            <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-amber-300 hover:dark:text-amber-400 cursor-pointer">
            </i>

        </div>

        <!-- LANGUAGE BUTTON -->
        <div x-data="{open:false, loadingLang:false}" class="relative">

            <button
                @click="open=!open"
                class="flex items-center gap-3 px-4 py-1 bg-yellow-400 border-2 border-yellow-500 rounded-full cursor-pointer">

                <i class="fa-solid fa-globe"></i>

                <span><?= strtoupper($lang) ?></span>

                <i
                    class="fa-solid fa-caret-down transition-transform duration-200"
                    :style="`transform: rotate(${open ? 180 : 0}deg)`">
                </i>

            </button>

            <div
                x-show="open"
                x-cloak
                x-transition.scale.origin.top
                @click.outside="open=false"
                class="absolute right-0 mt-2 w-24 bg-white shadow rounded-lg dark:bg-gray-700">

                <a href="?lang=id"
                    @click.prevent="loadingLang=true; setTimeout(()=>window.location='?lang=id',400)"
                    class="block px-3 py-2 hover:bg-gray-100 hover:dark:bg-gray-500 <?= $lang == 'id' ? 'font-bold' : '' ?>">
                    ID
                </a>

                <a href="?lang=en"
                    @click.prevent="loadingLang=true; setTimeout(()=>window.location='?lang=en',400)"
                    class="block px-3 py-2 hover:bg-gray-100 hover:dark:bg-gray-500 <?= $lang == 'en' ? 'font-bold' : '' ?>">
                    EN
                </a>

            </div>

            <div
                x-show="loadingLang"
                x-transition.opacity
                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">

                <div class="bg-white dark:bg-gray-800 px-6 py-4 rounded-xl shadow-lg flex items-center gap-3">

                    <svg
                        class="animate-spin h-5 w-5 text-yellow-500"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24">

                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"></circle>

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v8z"></path>

                    </svg>

                    <span class="text-sm font-medium hidden sm:block">
                        Mengubah bahasa...
                    </span>

                </div>

            </div>

        </div>

        <!-- DARK MODE BUTTON -->
        <button
            @click="$dispatch('toggle-theme')"
            class="flex items-center gap-3 px-4 py-1 bg-yellow-400 border-2 border-yellow-500 rounded-full font-semibold hover:bg-yellow-300 transition cursor-pointer">

            <span class="w-6 flex justify-center">
                <i class="fa-fw transition-transform duration-300" :class="dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
            </span>

            <span class="w-14 text-center" x-text="dark ? '<?= $t['light'] ?>' : '<?= $t['dark'] ?>'">></span>

        </button>

        <!-- NOTIFICATION -->
        <div x-data="notificationSystem()" x-init="init()" class="relative">

            <button @click="toggle()"
                class="relative text-amber-300 hover:text-amber-400 cursor-pointer">

                <i class="fa-solid fa-bell text-lg"></i>

                <!-- BADGE -->
                <span
                    x-show="count>0"
                    x-text="count"
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 rounded-full">
                </span>

            </button>

            <!-- DROPDOWN -->
            <div
                x-show="open"
                x-cloak
                @click.outside="open=false"
                x-transition.origin.top.right
                class="absolute -right-32 mt-4 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700">

                <!-- HEADER -->
                <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">

                    <span class="font-semibold">
                        Notifikasi
                    </span>

                    <button
                        @click.stop="markRead(item.id)"
                        class="text-xs text-blue-500 hover:underline">
                        Tandai semua dibaca
                    </button>

                </div>

                <!-- LIST -->
                <template x-for="item in notifications" :key="item.id">

                    <a
                        @click="markRead(item.id)"
                        class="block px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700"
                        :class="item.is_read == 0 ? 'bg-yellow-50 dark:bg-gray-700' : ''">

                        <p class="text-sm font-medium" x-text="item.title"></p>

                        <span class="text-xs text-gray-500" x-text="item.time"></span>

                    </a>

                </template>

                <!-- FOOTER -->
                <div class="p-3 border-t text-center dark:border-gray-700">

                    <a href="#" class="text-sm text-blue-500 hover:underline">

                        Lihat semua notifikasi

                    </a>

                </div>

            </div>

        </div>

        <!-- USER PROFILE -->
        <div x-data="{ open:false }"
            @mouseenter="open=true"
            @mouseleave="open=false"
            class="relative flex items-center space-x-2 cursor-pointer">

            <!-- AVATAR -->
            <img src="<?= $foto ?>"
                class="w-8 h-8 rounded-full object-cover">

            <!-- USERNAME -->
            <span class="text-sm font-medium hidden sm:block">
                <?= htmlspecialchars(ucfirst($_SESSION['username'])); ?>
            </span>

            <!-- DROPDOWN -->
            <div x-show="open"
                x-transition
                x-cloak
                class="absolute -right-4 top-9 w-32 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">

                <!-- PROFILE -->
                <a href="/rkd-cafe/public/profile.php"
                    class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">

                    <i class="fa-solid fa-user text-gray-500"></i>
                    Profile
                </a>

                <!-- SETTINGS -->
                <a href="/rkd-cafe/public/settings.php"
                    class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">

                    <i class="fa-solid fa-gear text-gray-500"></i>
                    Settings
                </a>

                <!-- DIVIDER -->
                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>

                <!-- LOGOUT -->
                <a href="/rkd-cafe/resources/views/auth/logout.php"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-red-500 hover:bg-gray-100 dark:hover:bg-gray-700">

                    <i class="fa-solid fa-right-from-bracket"></i>
                    Logout
                </a>

            </div>

        </div>

    </div>

</header>
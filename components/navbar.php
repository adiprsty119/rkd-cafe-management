<header class="bg-white dark:bg-gray-800 p-4 flex justify-between items-center shadow-2xl">

    <!-- TITLE -->
    <h1 class="text-xl font-semibold cursor-pointer">
        <?= $t['dashboard'] ?>
    </h1>

    <!-- RIGHT MENU -->
    <div class="flex items-center space-x-6">

        <!-- SEARCH -->
        <div class="relative">

            <input
                type="text"
                placeholder="<?= $t['search'] ?>..."
                class="w-96 px-4 py-2 pr-10 border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 dark:bg-gray-700 dark:text-white">

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

                    <span class="text-sm font-medium">
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
        <i class="fa-solid fa-bell text-amber-300 hover:text-amber-400 cursor-pointer"></i>

        <!-- USER PROFILE -->
        <div class="flex items-center space-x-2 cursor-pointer">
            <img src="https://i.pravatar.cc/40" class="w-8 h-8 rounded-full">
            <span class="text-sm font-medium">
                <?= $_SESSION['username']; ?>
            </span>
        </div>

    </div>

</header>
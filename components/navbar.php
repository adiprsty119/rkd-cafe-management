<header class="bg-white dark:bg-gray-800 p-4 flex justify-between items-center shadow-2xl">

    <!-- TITLE -->
    <h1 class="text-xl font-semibold cursor-pointer">
        Dashboard
    </h1>

    <!-- RIGHT MENU -->
    <div class="flex items-center space-x-6">

        <!-- SEARCH -->
        <div class="relative">

            <input
                type="text"
                placeholder="Cari..."
                class="w-96 px-4 py-2 pr-10 border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 dark:bg-gray-700 dark:text-white">

            <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-amber-300 hover:dark:text-amber-400 cursor-pointer">
            </i>

        </div>

        <!-- LANGUAGE BUTTON -->
        <div x-data="{open:false}" class="relative">

            <button
                @click="open=!open"
                class="flex items-center gap-3 px-4 py-1 bg-yellow-400 border-2 border-yellow-500 rounded-full cursor-pointer">

                <span>ID</span>
                <i class="fa-solid fa-caret-down"></i>

            </button>

            <div
                x-show="open"
                @click.outside="open=false"
                class="absolute right-0 mt-2 w-24 bg-white shadow rounded-lg dark:bg-gray-700">

                <a href="#" class="block px-3 py-2 hover:bg-gray-100 hover:dark:bg-gray-500">ID</a>
                <a href="#" class="block px-3 py-2 hover:bg-gray-100 hover:dark:bg-gray-500">EN</a>

            </div>

        </div>

        <!-- DARK MODE BUTTON -->
        <button
            @click="dark = !dark"
            class="flex items-center gap-3 px-4 py-1 bg-yellow-400 border-2 border-yellow-500 rounded-full font-semibold hover:bg-yellow-300 transition cursor-pointer">

            <span class="w-6 flex justify-center">
                <i class="fa-fw" :class="dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
            </span>

            <span class="w-14 text-center" x-text="dark ? 'Terang' : 'Gelap'"></span>

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
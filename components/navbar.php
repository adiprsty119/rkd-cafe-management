<header class="bg-white shadow p-4 flex justify-between items-center">

    <h1 class="text-xl font-semibold">
        Dashboard
    </h1>

    <div class="flex items-center space-x-4">

        <i class="fa-solid fa-bell text-gray-600"></i>

        <div class="flex items-center space-x-2">
            <img src="https://i.pravatar.cc/40" class="w-8 h-8 rounded-full">
            <span class="text-sm font-medium">
                <?= $_SESSION['username']; ?>
            </span>
        </div>

    </div>

</header>
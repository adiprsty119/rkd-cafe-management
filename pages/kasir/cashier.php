<?php

require $_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/middleware/AuthMiddleware.php';

/* ==========================
   LANGUAGE SYSTEM
========================== */

$lang = $_GET['lang'] ?? 'id';

if (!in_array($lang, ['id', 'en'])) {
    $lang = 'id';
}

$t = require __DIR__ . '/../../resources/lang/' . $lang . '.php';
require_once __DIR__ . '/../../app/helpers/menu_helper.php';
require_once __DIR__ . '/../../app/helpers/menu_engine.php';

$role = $_SESSION['role'] ?? 'guest';

/* ==========================
   MENU ENGINE
========================== */

$menus = getMenusByRole($role);
$currentMenu = findMenuByRoute($menus);
$pageTitle = $currentMenu['menu']['title'] ?? 'Dashboard';
$breadcrumb = generateBreadcrumb($currentMenu);
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $pageTitle ?></title>

    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>


<body
    x-data="{ dark: localStorage.theme === 'dark', loading:false, loadingTheme:false, sidebarOpen:true, cashierPOS() }"
    @toggle-theme.window="loadingTheme = true; setTimeout(() => {let newTheme = !dark; localStorage.theme = newTheme ? 'dark' : 'light'; location.reload();}, 800)"
    x-init="document.documentElement.classList.toggle('dark', dark)"
    :class="{ 'dark': dark }"
    class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-300">


    <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900 relative">


        <!-- SIDEBAR -->
        <aside
            :class="sidebarOpen ? 'w-64' : 'w-20'"
            class="hidden md:flex flex-col h-screen bg-white dark:bg-gray-800 transition-all duration-300 overflow-x-hidden">

            <?php require __DIR__ . '/../../resources/components/sidebar.php'; ?>

        </aside>


        <!-- MOBILE SIDEBAR -->
        <aside
            x-show="sidebarOpen"
            x-transition
            class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 z-40 md:hidden">

            <?php require __DIR__ . '/../../resources/components/sidebar.php'; ?>

        </aside>


        <!-- OVERLAY -->
        <div
            x-show="sidebarOpen"
            @click="sidebarOpen=false"
            class="fixed inset-0 bg-black/40 z-30 md:hidden">
        </div>


        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col min-w-0 md:ml-0 transition-all duration-300">


            <!-- NAVBAR -->
            <div class="p-4 border-t border-gray-700">

                <?php require __DIR__ . '/../../resources/components/navbar.php'; ?>

            </div>

            <!-- BREADCRUMB NAVIGATION -->
            <?php require __DIR__ . '/../../resources/components/breadcrumb.php'; ?>

            <!-- POS CONTENT -->
            <main class="flex-1 grid grid-cols-1 lg:grid-cols-3 overflow-hidden">


                <!-- MENU SECTION -->
                <div class="lg:col-span-2 p-6 overflow-y-auto space-y-6">

                    <h1 class="text-2xl font-bold"><?= $pageTitle ?></h1>

                    <!-- SEARCH -->
                    <input
                        type="text"
                        placeholder="Search menu..."
                        class="border rounded px-3 py-2 w-64 dark:bg-gray-700">


                    <!-- MENU GRID -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">


                        <!-- MENU CARD -->
                        <div
                            @click="addItem('Latte',30000)"
                            class="bg-white dark:bg-gray-800 rounded-xl shadow p-3 cursor-pointer hover:shadow-lg transition">

                            <img
                                src="/rkd-cafe/public/assets/images/latte.png"
                                class="h-28 w-full object-cover rounded">

                            <p class="font-semibold mt-2">
                                Latte
                            </p>

                            <p class="text-amber-600 font-bold">
                                Rp 30.000
                            </p>

                        </div>


                        <div
                            @click="addItem('Espresso',25000)"
                            class="bg-white dark:bg-gray-800 rounded-xl shadow p-3 cursor-pointer hover:shadow-lg transition">

                            <img
                                src="/rkd-cafe/public/assets/images/espresso.png"
                                class="h-28 w-full object-cover rounded">

                            <p class="font-semibold mt-2">
                                Espresso
                            </p>

                            <p class="text-amber-600 font-bold">
                                Rp 25.000
                            </p>

                        </div>


                        <div
                            @click="addItem('Croissant',22000)"
                            class="bg-white dark:bg-gray-800 rounded-xl shadow p-3 cursor-pointer hover:shadow-lg transition">

                            <img
                                src="/rkd-cafe/public/assets/images/croissant.png"
                                class="h-28 w-full object-cover rounded">

                            <p class="font-semibold mt-2">
                                Croissant
                            </p>

                            <p class="text-amber-600 font-bold">
                                Rp 22.000
                            </p>

                        </div>


                    </div>

                </div>



                <!-- CART SECTION -->
                <div class="bg-white dark:bg-gray-800 p-6 flex flex-col border-l">


                    <h2 class="font-semibold mb-4">
                        Order Cart
                    </h2>


                    <!-- CART ITEMS -->
                    <div class="flex-1 overflow-y-auto space-y-2">

                        <template x-for="item in cart">

                            <div class="flex justify-between items-center border-b py-2">

                                <div>

                                    <p x-text="item.name"></p>

                                    <p class="text-sm text-gray-500">
                                        Rp <span x-text="item.price"></span>
                                    </p>

                                </div>

                                <div class="flex items-center gap-2">

                                    <button
                                        @click="item.qty--"
                                        class="px-2 border rounded">
                                        -
                                    </button>

                                    <span x-text="item.qty"></span>

                                    <button
                                        @click="item.qty++"
                                        class="px-2 border rounded">
                                        +
                                    </button>

                                </div>

                            </div>

                        </template>

                    </div>


                    <!-- TOTAL -->
                    <div class="border-t pt-4 space-y-3">

                        <div class="flex justify-between font-bold">

                            <span>Total</span>

                            <span>
                                Rp <span x-text="total"></span>
                            </span>

                        </div>


                        <button
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white py-3 rounded font-semibold">

                            Checkout

                        </button>

                    </div>

                </div>


            </main>

        </div>

    </div>



    <?php require __DIR__ . '/../../resources/components/toast.php'; ?>


    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>



    <script>
        function cashierPOS() {

            return {

                cart: [],

                addItem(name, price) {

                    let existing = this.cart.find(i => i.name === name)

                    if (existing) {

                        existing.qty++

                    } else {

                        this.cart.push({
                            name: name,
                            price: price,
                            qty: 1
                        })

                    }

                },

                get total() {

                    return this.cart.reduce((t, i) => t + (i.price * i.qty), 0)

                }

            }

        }
    </script>


</body>

</html>
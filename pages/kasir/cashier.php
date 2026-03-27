<?php
define('APP_INIT', true);

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
$sidebarCollapsed = $_SESSION['sidebar_collapsed'] ?? 0;

/* ==========================
   MENU ENGINE
========================== */

$allMenus = $_SESSION['menu_config'][$role] ?? [];
$menus = getMenusByRole($role);
$currentMenu = findMenuByRoute($allMenus);
$pageTitle = $currentMenu['menu']['title'] ?? 'Dashboard';
$breadcrumb = generateBreadcrumb($currentMenu);
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $pageTitle ?></title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- Vanilla CSS -->
    <link href="/rkd-cafe/public/assets/css/utilities.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Global Seacrh [global-search.js] -->
    <script defer src="/rkd-cafe/public/assets/js/global-search.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>


<body
    x-data="{
        dark: localStorage.theme === 'dark',
        loading:false,
        loadingTheme:false,
        sidebarOpen:<?= isset($sidebarCollapsed) && $sidebarCollapsed ? 'false' : 'true' ?>,
        ...cashierPOS()
    }"
    @toggle-theme.window="loadingTheme = true; setTimeout(() => {let newTheme = !dark; localStorage.theme = newTheme ? 'dark' : 'light'; location.reload();}, 800)"
    x-init="document.documentElement.classList.toggle('dark', dark)"
    :class="{ 'dark': dark }"
    class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-300">


    <div class="flex min-h-screen bg-gradient-to-br from-gray-200 via-gray-100 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition">


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
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">

            <!-- =========================
                HEADER STACK
            ========================= -->
            <div id="headerStack" class="sticky top-0 z-50 relative">

                <!-- =========================
                    NAVBAR
                ========================= -->
                <div
                    id="dashboardNavbar"
                    class="relative z-50 transition-all duration-300">

                    <div class="w-full px-4 mt-3 transition-all duration-300 bg-transparent">
                        <?php require __DIR__ . '/../../resources/components/navbar.php'; ?>
                    </div>


                    <!-- =========================
                        BREADCRUMB INDICATOR
                    ========================= -->
                    <div
                        id="breadcrumbIndicator"
                        class="absolute left-auto -translate-x-1/2 top-full z-50 opacity-0 translate-y-2 pointer-events-none transition-all ease-out delay-75 duration-300">

                        <button
                            class="flex items-center ml-16 px-2 py-1 text-sm rounded-sm backdrop-blur-md bg-gray-100 dark:bg-gray-800 shadow-md hover:bg-white/60 active:scale-95 transition cursor-pointer">

                            <i class="fa-solid fa-angle-down animate-bounce"></i>

                        </button>

                    </div>

                </div>

            </div>


            <!-- =========================
                BREADCRUMB CONTAINER
            ========================= -->
            <div
                id="breadcrumbContainer"
                class="relative will-change-transform transition-opacity duration-200">

                <div
                    id="breadcrumbMask"
                    class="px-4 py-2 overflow-hidden">

                    <?php require __DIR__ . '/../../resources/components/breadcrumb.php'; ?>

                </div>

            </div>


            <main id="dashboardScroll"
                class="flex-1 p-4 md:p-6 overflow-y-auto space-y-6 scrollbar-hide">


                <!-- HEADER -->
                <div class="flex flex-col mb-12 md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm px-6 py-5">

                    <!-- LEFT -->
                    <div class="flex items-center gap-4">

                        <!-- PAGE ICON -->
                        <div class="w-11 h-11 flex items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-500/20">

                            <i class="fa-solid fa-cash-register text-emerald-600 dark:text-emerald-400"></i>

                        </div>

                        <!-- TITLE -->
                        <div>

                            <h1 class="text-xl md:text-2xl font-semibold tracking-tight">
                                <?= htmlspecialchars($pageTitle) ?>
                            </h1>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <?= $t['pos_interface'] ?? 'Process customer orders and payments' ?>
                            </p>

                        </div>

                    </div>

                </div>

                <!-- POS GRID -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-200px)]">

                    <!-- MENU SECTION -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 overflow-y-auto space-y-6">


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
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 flex flex-col">


                    <h2 class="font-semibold mb-4">
                        Order Cart
                    </h2>


                    <!-- CART ITEMS -->
                    <div class="flex-1 overflow-y-auto space-y-2">

                        <template x-for="item in cart" :key="item.name">

                            <div class="flex justify-between items-center border-b py-2">

                                <div>

                                    <p x-text="item.name"></p>

                                    <p class="text-sm text-gray-500">
                                        Rp <span x-text="item.price"></span>
                                    </p>

                                </div>

                                <div class="flex items-center gap-2">

                                    <button
                                        @click="item.qty > 1 ? item.qty-- : removeItem(item)"
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
                                Rp <span x-text="total.toLocaleString('id-ID')"></span>
                            </span>

                        </div>


                        <button
                            @click="checkout()"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white py-3 rounded font-semibold">

                            Checkout

                        </button>

                    </div>

                </div>

                </div> <!-- END POS GRID -->

                <!-- PAYMENT MODAL -->
                <div
                    x-show="showPaymentModal"
                    x-transition
                    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
                    @click.self="showPaymentModal = false">

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md mx-4">

                        <h3 class="text-lg font-semibold mb-4">Pembayaran</h3>

                        <div class="space-y-4">

                            <div>
                                <label class="block text-sm font-medium mb-2">Total Pembayaran</label>
                                <div class="text-2xl font-bold text-amber-600">
                                    Rp <span x-text="total.toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Metode Pembayaran</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        @click="paymentMethod = 'cash'"
                                        :class="paymentMethod === 'cash' ? 'bg-amber-500 text-white' : 'bg-gray-200 dark:bg-gray-700'"
                                        class="p-3 rounded-lg font-medium transition">
                                        <i class="fas fa-money-bill-wave mr-2"></i>Tunai
                                    </button>
                                    <button
                                        @click="paymentMethod = 'card'"
                                        :class="paymentMethod === 'card' ? 'bg-amber-500 text-white' : 'bg-gray-200 dark:bg-gray-700'"
                                        class="p-3 rounded-lg font-medium transition">
                                        <i class="fas fa-credit-card mr-2"></i>Kartu
                                    </button>
                                </div>
                            </div>

                            <template x-if="paymentMethod === 'cash'">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Jumlah Bayar</label>
                                    <input
                                        x-model="cashAmount"
                                        type="number"
                                        class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600"
                                        placeholder="Masukkan jumlah bayar">
                                </div>
                                <div x-show="cashAmount" class="text-sm">
                                    Kembalian: Rp <span x-text="(cashAmount - total).toLocaleString('id-ID')"></span>
                                </div>
                            </template>

                            <div class="flex gap-3 pt-4">
                                <button
                                    @click="showPaymentModal = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    Batal
                                </button>
                                <button
                                    @click="processPayment()"
                                    :disabled="!canProcessPayment"
                                    :class="canProcessPayment ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-300 cursor-not-allowed'"
                                    class="flex-1 px-4 py-2 text-white rounded-lg transition">
                                    Bayar
                                </button>
                            </div>

                        </div>

                    </div>

                </div>


            </main>

        </div>

    </div>


    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
    <script src="/rkd-cafe/public/assets/js/notifications.js"></script>
    <script src="/rkd-cafe/public/assets/js/header.js"></script>
    <script src="/rkd-cafe/public/assets/js/sidebar-tooltip.js"></script>

    <div
        id="global-tooltip"
        class="fixed hidden px-2 py-1 text-xs text-white bg-black rounded shadow-lg whitespace-nowrap z-[9999] pointer-events-none">
    </div>

    <?php require __DIR__ . '/../../resources/components/toast.php'; ?>
    <?php if (isset($_SESSION['toast'])): ?>

        <script>
            window.toastData = {
                type: "<?= $_SESSION['toast']['type'] ?>",
                message: "<?= $_SESSION['toast']['message'] ?>"
            }
        </script>

    <?php unset($_SESSION['toast']);
    endif; ?>


    <script>
        function cashierPOS() {

            return {

                cart: [],
                showPaymentModal: false,
                paymentMethod: '',
                cashAmount: '',

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

                removeItem(item) {
                    this.cart = this.cart.filter(i => i !== item)
                },

                checkout() {
                    if (this.cart.length === 0) {
                        alert('Keranjang kosong!');
                        return;
                    }
                    this.showPaymentModal = true;
                    this.paymentMethod = 'cash';
                    this.cashAmount = '';
                },

                processPayment() {
                    if (!this.canProcessPayment) return;
                    
                    // Simulasi pemrosesan pembayaran
                    alert('Pembayaran berhasil diproses!');
                    this.cart = [];
                    this.showPaymentModal = false;
                    this.paymentMethod = '';
                    this.cashAmount = '';
                },

                get total() {

                    return this.cart.reduce((t, i) => t + (i.price * i.qty), 0)

                },

                get canProcessPayment() {
                    if (this.paymentMethod === 'cash') {
                        return this.cashAmount >= this.total;
                    }
                    return this.paymentMethod === 'card';
                }

            }

        }
    </script>


</body>

</html>

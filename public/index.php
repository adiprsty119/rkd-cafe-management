<?php

define('APP_INIT', true);

require __DIR__ . '/../middleware/AuthMiddleware.php';

/* MENCEGAH CACHE LOGIN PAGE */
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

/* CSRF TOKEN */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>RKD Cafe POS</title>

    <link rel="stylesheet" href="/rkd-cafe/public/assets/css/output.css">

    <link rel="stylesheet" href="/rkd-cafe/public/assets/css/landing.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>

<body class="bg-gray-50 text-gray-800 antialiased">

    <!-- NAVBAR -->
    <header x-data="{open:false}" class="bg-white/80 backdrop-blur shadow-sm sticky top-0 z-50">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">

            <h1 class="text-lg sm:text-xl font-bold text-blue-600 flex items-center gap-2">
                ☕ RKD Cafe
            </h1>

            <div class="hidden md:flex items-center gap-8">

                <a href="#features" class="hover:text-blue-600">Fitur</a>
                <a href="#preview" class="hover:text-blue-600">Preview</a>

                <a href="/rkd-cafe/resources/views/auth/login.php"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Login
                </a>

            </div>

            <button @click="open=!open" class="md:hidden text-2xl">
                ☰
            </button>

        </div>

        <div x-show="open" x-transition class="md:hidden px-6 pb-6 space-y-4">

            <a href="#features" class="block">Fitur</a>
            <a href="#preview" class="block">Preview</a>

            <a href="/rkd-cafe/resources/views/auth/login.php"
                class="block bg-blue-600 text-white px-4 py-2 rounded text-center">
                Login
            </a>

        </div>

    </header>


    <!-- HERO -->
    <section
        x-data="parallaxHero()"
        @mousemove="move($event)"
        class="hero-gradient py-20 sm:py-28 lg:py-32 text-white relative overflow-hidden">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-2 gap-12 items-center">

            <!-- TEXT -->
            <div data-aos="fade-right" class="text-center lg:text-left">

                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight mb-6">

                    POS Modern
                    <br>
                    Untuk Bisnis Cafe

                </h2>

                <p class="text-blue-100 text-base sm:text-lg mb-8 max-w-xl mx-auto lg:mx-0">

                    Kelola transaksi, menu, stok dan laporan
                    penjualan secara real-time dengan sistem
                    POS modern yang cepat dan mudah digunakan.

                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">

                    <a href="/rkd-cafe/resources/views/auth/login.php"
                        class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 text-center">
                        Login Sistem
                    </a>

                    <a href="#features"
                        class="glass px-6 py-3 rounded-lg text-center">
                        Pelajari Fitur
                    </a>

                </div>

                <!-- STATISTICS -->
                <div class="grid grid-cols-3 gap-4 sm:gap-6 mt-12 text-center max-w-md mx-auto lg:mx-0">

                    <div x-data="counter(1200)">
                        <h4 class="text-2xl sm:text-3xl font-bold">
                            <span x-text="count"></span>+
                        </h4>
                        <p class="text-blue-200 text-xs sm:text-sm">
                            Transaksi
                        </p>
                    </div>

                    <div x-data="counter(150)">
                        <h4 class="text-2xl sm:text-3xl font-bold">
                            <span x-text="count"></span>+
                        </h4>
                        <p class="text-blue-200 text-xs sm:text-sm">
                            Produk
                        </p>
                    </div>

                    <div x-data="counter(5)">
                        <h4 class="text-2xl sm:text-3xl font-bold">
                            <span x-text="count"></span>
                        </h4>
                        <p class="text-blue-200 text-xs sm:text-sm">
                            Kasir Aktif
                        </p>
                    </div>

                </div>

            </div>


            <!-- DASHBOARD IMAGE -->
            <div class="relative max-w-xl mx-auto lg:max-w-none">

                <div class="glass rounded-xl p-3 sm:p-4 shadow-2xl relative z-10">

                    <img src="/rkd-cafe/public/assets/images/pos-dashboard.png"
                        class="rounded-lg w-full">

                </div>

                <!-- FLOATING ICONS -->

                <img :style="style(30)"
                    class="hidden md:block absolute w-16 -top-6 -left-10"
                    src="/rkd-cafe/public/assets/images/coffee-cup.png">

                <img :style="style(20)"
                    class="hidden md:block absolute w-12 top-10 -right-10"
                    src="/rkd-cafe/public/assets/images/coffee-beans.png">

                <img :style="style(15)"
                    class="hidden md:block absolute w-14 bottom-0 -left-10"
                    src="/rkd-cafe/public/assets/images/croissant.png">

                <img :style="style(25)"
                    class="hidden md:block absolute w-16 -bottom-10 right-10"
                    src="/rkd-cafe/public/assets/images/latte.png">

            </div>

        </div>

    </section>



    <!-- FEATURES -->
    <section id="features" class="py-20 sm:py-24 bg-white">

        <div class="max-w-7xl mx-auto px-4 sm:px-6">

            <h3 class="text-2xl sm:text-3xl font-bold text-center mb-16">
                Fitur Sistem
            </h3>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">

                <div data-aos="fade-up"
                    class="p-8 bg-gray-50 rounded-xl shadow-sm hover:shadow-lg transition hover:-translate-y-2">

                    <div class="text-3xl mb-4">💳</div>

                    <h4 class="font-semibold text-lg mb-2">
                        POS Kasir
                    </h4>

                    <p class="text-gray-600">
                        Proses transaksi cepat dengan sistem kasir modern.
                    </p>

                </div>

                <div data-aos="fade-up" data-aos-delay="150"
                    class="p-8 bg-gray-50 rounded-xl shadow-sm hover:shadow-lg transition hover:-translate-y-2">

                    <div class="text-3xl mb-4">📦</div>

                    <h4 class="font-semibold text-lg mb-2">
                        Manajemen Produk
                    </h4>

                    <p class="text-gray-600">
                        Kelola menu cafe, kategori dan harga produk dengan mudah.
                    </p>

                </div>

                <div data-aos="fade-up" data-aos-delay="300"
                    class="p-8 bg-gray-50 rounded-xl shadow-sm hover:shadow-lg transition hover:-translate-y-2">

                    <div class="text-3xl mb-4">📊</div>

                    <h4 class="font-semibold text-lg mb-2">
                        Laporan Penjualan
                    </h4>

                    <p class="text-gray-600">
                        Pantau penjualan harian dan performa bisnis cafe.
                    </p>

                </div>

            </div>

        </div>

    </section>



    <!-- PREVIEW -->
    <section id="preview" class="py-20 sm:py-24 bg-gray-50">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center">

            <h3 class="text-2xl sm:text-3xl font-bold mb-8">
                Preview Dashboard POS
            </h3>

            <p class="text-gray-600 mb-12 max-w-2xl mx-auto">
                Antarmuka kasir dirancang untuk mempercepat transaksi
                dan mempermudah pengelolaan pesanan.
            </p>

            <div data-aos="zoom-in"
                class="shadow-2xl rounded-xl overflow-hidden">

                <img
                    src="/rkd-cafe/public/assets/images/pos-preview.png"
                    class="w-full">

            </div>

        </div>

    </section>



    <!-- CTA -->
    <section class="py-20 sm:py-24 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-center">

        <div class="max-w-4xl mx-auto px-6">

            <h3 class="text-3xl sm:text-4xl font-bold mb-6">
                Kelola Bisnis Cafe Lebih Mudah
            </h3>

            <p class="text-blue-100 mb-8">
                Gunakan RKD Cafe POS untuk meningkatkan efisiensi
                operasional bisnis Anda.
            </p>

            <a href="/rkd-cafe/resources/views/auth/login.php"
                class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200">
                Masuk ke Sistem
            </a>

        </div>

    </section>

    <!-- FOOTER -->
    <footer class="bg-gray-900 text-gray-400 py-8">

        <div class="max-w-7xl mx-auto px-6 text-center">
            © <?php echo date("Y"); ?> RKD Cafe POS System
        </div>

    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="/rkd-cafe/public/assets/js/landing.js"></script>

</body>

</html>
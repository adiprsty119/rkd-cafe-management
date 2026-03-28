<?php

define('APP_INIT', true);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../../../app/helpers/auth_helper.php';

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // aktif jika HTTPS

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* MENCEGAH CACHE LOGIN PAGE */
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

guestOnly();

/* CSRF TOKEN */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="id"
    x-data="{dark: localStorage.theme === 'dark', loading:false}"
    x-init="$watch('dark', val => {localStorage.theme = val ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', val)});"
    :class="{ 'dark': dark }">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - RKD Cafe</title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- Vanila CSS -->
    <link href="/rkd-cafe/public/assets/css/auth.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>

<body class="min-h-screen flex bg-gradient-to-br from-gray-200 via-gray-100 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition">

    <div class="relative flex w-full min-h-screen overflow-hidden">

        <!-- IMAGE -->
        <div
            class="hidden lg:flex w-1/2 bg-cover bg-center transition-all duration-700"
            style="background-image:url('https://images.unsplash.com/photo-1509042239860-f550ce710b93');">

            <div class="w-full h-full bg-black/40 flex items-center justify-center">

                <h1 class="text-white text-4xl font-bold">
                    RKD Cafe
                </h1>

            </div>
        </div>


        <!-- AUTH CARD -->
        <div
            class="flex w-full lg:w-1/2 items-center justify-center p-6 transition-all duration-700">

            <div class="w-full max-w-md">

                <!-- DARK MODE -->
                <div class="flex justify-end mb-4">

                    <button
                        @click="dark=!dark"
                        title="Toggle dark mode"
                        class="text-gray-600 dark:text-yellow-400 text-xl cursor-pointer transition transform hover:rotate-180 duration-500">

                        <i :class="dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>

                    </button>

                </div>

                <!-- CARD -->
                <div
                    class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 border border-white/40 rounded-2xl p-5 sm:p-6 overflow-y-auto shadow-[0_20px_50px_rgba(0,0,0,0.15)] dark:shadow-[0_0_35px_rgba(252,211,77,0.35)] hover:dark:shadow-amber-300 transition-all duration-500 ease-in-out <?= isset($_SESSION['error']) ? 'shake' : '' ?>">

                    <!-- LOGIN -->
                    <div>

                        <h2 class="text-3xl font-bold text-center mb-8 text-gray-700 dark:text-white">
                            Masuk ke Akun
                        </h2>

                        <button
                            onclick="window.location.href='/rkd-cafe/app/controllers/AuthController.php?action=loginGoogle'"
                            type="button"
                            class="w-full border rounded-xl py-4 flex items-center justify-center gap-3 text-lg hover:bg-gray-50 dark:bg-gray-600 dark:hover:bg-gray-700 transition transform hover:scale-105 cursor-pointer">

                            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-6">

                            <span class="font-medium text-gray-700 dark:text-white text-sm sm:text-lg">
                                Masuk dengan Google
                            </span>

                        </button>

                        <div class="flex items-center gap-4 my-8">
                            <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                            <span class="text-gray-500 dark:text-gray-300 text-sm">Atau login dengan akun</span>
                            <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                        </div>

                        <form
                            action="/rkd-cafe/app/controllers/AuthController.php?action=login"
                            method="POST"
                            @submit.prevent="loading=true; $el.submit()">

                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                            <!-- USERNAME -->
                            <div class="mb-5">

                                <label class="block mb-1 text-gray-700 dark:text-gray-200">
                                    Username
                                </label>

                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    placeholder="username"
                                    autocomplete="username"
                                    autofocus
                                    required
                                    class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                            </div>


                            <!-- PASSWORD -->
                            <div class="mb-2 relative" x-data="{show:false}">

                                <label class="block mb-1 text-gray-700 dark:text-gray-200">
                                    Kata Sandi
                                </label>

                                <div class="relative">

                                    <input
                                        :type="show ? 'text' : 'password'"
                                        name="password"
                                        id="password"
                                        autocomplete="current-password"
                                        spellcheck="false"
                                        minlength="8"
                                        placeholder="••••••••"
                                        required
                                        class="w-full border rounded-xl px-4 py-3 pr-12 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                                    <i
                                        @click="show=!show"
                                        title="Tampilkan password"
                                        class="fa-solid absolute right-4 top-4 cursor-pointer text-gray-600 hover:scale-110 transition duration-200 dark:text-white"
                                        :class="show ? 'fa-eye-slash text-blue-500':'fa-eye text-gray-500'">
                                    </i>

                                </div>

                            </div>


                            <!-- REMEMBER -->
                            <div class="flex items-center mb-6 mt-4 pl-1.5">

                                <input type="checkbox" name="remember" value="1" class="mr-2 cursor-pointer">

                                <label class="text-sm text-gray-700 dark:text-gray-200">
                                    Ingat saya
                                </label>

                            </div>

                            <div class="mb-6">

                                <a
                                    href="/rkd-cafe/resources/views/auth/forgot_password.php"
                                    class="flex items-center gap-1 text-blue-500 text-sm hover:text-blue-600 hover:underline transition">

                                    <i class="fa-solid fa-key text-xs"></i>

                                    <span>Lupa password?</span>

                                </a>

                            </div>

                            <button
                                type="submit"
                                :disabled="loading"
                                class="w-full bg-green-600 text-white py-3 rounded-xl hover:bg-green-700 flex items-center justify-center gap-2 disabled:opacity-70 cursor-pointer">

                                <span x-show="!loading">Masuk</span>

                                <span x-show="loading" x-cloak class="flex items-center gap-2">

                                    <svg class="animate-spin h-5 w-5 text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24">

                                        <circle class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"></circle>

                                        <path class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8v8z"></path>

                                    </svg>

                                    Memproses...

                                </span>

                            </button>

                        </form>


                        <p class="text-center mt-6 text-gray-600 dark:text-gray-300">

                            Belum punya akun?

                            <button
                                type="button"
                                data-url="/rkd-cafe/resources/views/auth/register.php"
                                onclick="window.location.href=this.dataset.url"
                                class="text-blue-500 hover:underline cursor-pointer">

                                Registrasi
                            </button>

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <?php require '../../components/toast.php'; ?>
    <?php if (isset($_SESSION['toast'])): ?>

        <script>
            window.toastData = {
                type: "<?= $_SESSION['toast']['type'] ?>",
                message: "<?= htmlspecialchars($_SESSION['toast']['message']) ?>"
            };
        </script>

    <?php unset($_SESSION['toast']);
    endif; ?>

    <script src="/rkd-cafe/public/assets/js/toast.js"></script>
</body>

</html>
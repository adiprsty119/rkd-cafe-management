<?php
session_start();

/* MENCEGAH CACHE LOGIN PAGE */
header("Cache-Control: no-store, no-cache, must-revalidate");

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

/* CSRF TOKEN */
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="id"
    x-data="{ dark: localStorage.theme === 'dark', loading:false }"
    x-init="$watch('dark', val => {localStorage.theme = val ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', val)})"
    :class="{ 'dark': dark }">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - RKD Cafe</title>

    <!-- Tailwind CSS -->
    <link href="../assets/css/output.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- x-cloak -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .shake {
            animation: shake 0.4s;
        }
    </style>
</head>

<body class="min-h-screen flex bg-gray-200 dark:bg-gray-900 transition">

    <!-- LEFT IMAGE -->
    <div class="hidden lg:flex w-1/2 bg-cover bg-center"
        style="background-image:url('https://images.unsplash.com/photo-1509042239860-f550ce710b93');">

        <div class="w-full h-full bg-black/40 flex items-center justify-center">

            <h1 class="text-white text-4xl font-bold">
                RKD Cafe
            </h1>

        </div>

    </div>


    <!-- RIGHT LOGIN -->
    <div class="flex w-full lg:w-1/2 items-center justify-center p-6">

        <div class="w-full max-w-md">

            <!-- DARK MODE TOGGLE -->
            <div class="flex justify-end mb-4">

                <button
                    @click="dark = !dark"
                    title="Toggle dark mode"
                    class="text-gray-600 dark:text-yellow-400 text-xl cursor-pointer">

                    <i :class="dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>

                </button>

            </div>


            <!-- GLASS CARD -->
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 border border-white/40 rounded-2xl p-6 sm:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.15)] <?= isset($_SESSION['error']) ? 'shake' : '' ?>">

                <h2 class="text-3xl font-bold text-center mb-8 text-gray-700 dark:text-white">
                    Masuk ke Akun
                </h2>

                <?php if (isset($_SESSION['error'])): ?>

                    <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded-lg mb-4 text-sm">
                        <i class="fa-solid fa-circle-exclamation mr-2"></i>
                        <?= $_SESSION['error']; ?>
                    </div>

                <?php unset($_SESSION['error']);
                endif; ?>


                <!-- GOOGLE LOGIN -->
                <button type="button" class="w-full border rounded-xl py-4 flex items-center justify-center gap-3 text-lg hover:bg-gray-50 dark:bg-gray-600 dark:hover:bg-gray-700 transition cursor-pointer">

                    <img src="https://www.svgrepo.com/show/475656/google-color.svg"
                        class="w-6">

                    <span class="font-medium text-gray-700 dark:text-white">
                        Masuk dengan Google
                    </span>

                </button>


                <!-- DIVIDER -->
                <div class="flex items-center gap-4 my-8">

                    <div class="flex-1 h-px bg-gray-300"></div>

                    <span class="text-gray-500 dark:text-white text-sm">
                        Atau login dengan akun
                    </span>

                    <div class="flex-1 h-px bg-gray-300"></div>

                </div>


                <form
                    action="process_login.php"
                    method="POST"
                    @submit.prevent="loading = true; $el.submit()"
                    :disabled="loading">
                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                    <!-- USERNAME -->
                    <div class="mb-5">

                        <label class="block mb-1 text-gray-700 dark:text-gray-200">
                            Username
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            autocomplete="username"
                            placeholder="username"
                            autofocus
                            required
                            class="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">

                    </div>


                    <!-- PASSWORD -->
                    <div class="mb-2 relative" x-data="{show:false}">

                        <label class="block mb-1 text-gray-700 dark:text-gray-200">
                            Kata Sandi
                        </label>

                        <div class="relative">

                            <input
                                : type="show ? 'text' : 'password'"
                                id="password"
                                name="password"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                required
                                class="w-full border rounded-xl px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">

                            <i
                                @click="show = !show"
                                class="fa-solid absolute right-4 top-4 inset-y-0 flex items-center cursor-pointer text-gray-600 hover:text-gray-900 hover:scale-110 transition dark:text-gray-300"
                                :class="show ? 'fa-eye-slash' : 'fa-eye'">
                            </i>

                        </div>
                    </div>

                    <!-- REMEMBER ME -->
                    <div class="flex items-center mb-6 mt-4 pl-1.5">

                        <input
                            type="checkbox"
                            name="remember"
                            class="mr-2 cursor-pointer">

                        <label class="text-sm text-gray-700 dark:text-gray-200">
                            Ingat saya
                        </label>

                    </div>


                    <div class="mb-6">

                        <a href="#" class="text-blue-500 text-sm hover:underline">
                            Lupa password?
                        </a>

                    </div>


                    <!-- LOGIN BUTTON -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full bg-green-600 text-white py-3 rounded-xl hover:bg-green-700 transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed">

                        <span x-show="!loading">
                            Masuk
                        </span>

                        <span x-show="loading" x-cloak class="flex items-center gap-2">

                            <svg
                                class="animate-spin h-5 w-5 text-white"
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

                            Memproses...

                        </span>

                    </button>

                </form>


                <p class="text-center mt-6 text-gray-600 dark:text-gray-300">

                    Belum punya akun?

                    <a href="#" class="text-blue-500 hover:underline">
                        Registrasi
                    </a>

                </p>

            </div>

        </div>

    </div>

</body>

</html>
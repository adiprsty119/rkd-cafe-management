<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);

session_start();

header("Content-Security-Policy: default-src 'self'; script-src 'self' https://unpkg.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' https://cdnjs.cloudflare.com 'unsafe-inline'; font-src 'self' https://cdnjs.cloudflare.com data:; img-src 'self' data:; connect-src 'self'; form-action 'self'; frame-ancestors 'none';");

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="id"
    x-cloak
    x-data="{dark: localStorage.theme === 'dark', loading:false}"
    x-init="
$watch('dark', val => {
localStorage.theme = val ? 'dark' : 'light';
document.documentElement.classList.toggle('dark', val)
})
"
    :class="{ 'dark': dark }">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reset Password - RKD Cafe</title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>


<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-200 via-gray-100 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition">

    <div class="w-full max-w-md px-6">

        <!-- HEADER -->

        <div class="flex justify-end items-end mb-6">

            <button
                @click="dark=!dark"
                class="text-gray-600 dark:text-yellow-400 text-xl transition hover:rotate-180 duration-500 cursor-pointer">

                <i :class="dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>

            </button>

        </div>


        <!-- CARD -->

        <div class="backdrop-blur-xl bg-white/80 dark:bg-gray-800/70 border border-gray-200 dark:border-gray-700 shadow-[0_20px_50px_rgba(0,0,0,0.15)] dark:shadow-[0_0_35px_rgba(252,211,77,0.35)] hover:dark:shadow-amber-300 rounded-2xl p-8 shadow-xl transition">

            <!-- STEP INDICATOR -->

            <div class="flex items-center justify-center gap-3 mb-8 text-xs font-medium">

                <div class="flex items-center gap-2 text-blue-500">
                    <div class="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs">1</div>
                    <span>Cari Akun</span>
                </div>

                <div class="h-px w-6 bg-gray-300 dark:bg-gray-600"></div>

                <div class="flex items-center gap-2 text-gray-400">
                    <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs">2</div>
                    <span>Verifikasi</span>
                </div>

                <div class="h-px w-6 bg-gray-300 dark:bg-gray-600"></div>

                <div class="flex items-center gap-2 text-gray-400">
                    <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs">3</div>
                    <span>Reset</span>
                </div>

            </div>


            <h2 class="text-3xl font-bold text-center text-gray-800 dark:text-white mb-2">
                Reset Password
            </h2>

            <p class="text-center text-gray-500 dark:text-gray-400 text-sm mb-8 leading-relaxed">
                Masukkan username dan email untuk menemukan akun Anda
            </p>


            <form
                action="/rkd-cafe/app/controllers/AuthController.php?action=findAccount"
                method="POST"
                @submit="loading=true"
                novalidate
                class="space-y-6">

                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                <input type="hidden" name="form_time" value="<?= time() ?>">
                <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">


                <!-- USERNAME -->

                <div>

                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Username
                    </label>

                    <div class="relative group">

                        <i class="fa-solid fa-user absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-500 transition"></i>

                        <input
                            type="text"
                            maxlength="50"
                            name="username"
                            pattern="[a-zA-Z0-9_]{3,30}"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            required
                            title="Username hanya boleh huruf, angka dan underscore"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-xl pl-11 pr-4 py-3 bg-white dark:bg-gray-700 text-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition outline-none">

                    </div>

                </div>


                <!-- EMAIL -->

                <div>

                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Email
                    </label>

                    <div class="relative group">

                        <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-gray-400 group-focus-within:text-blue-500 transition"></i>

                        <input
                            type="email"
                            name="email"
                            maxlength="100"
                            placeholder="Masukkan email"
                            autocomplete="email"
                            required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-xl pl-11 pr-4 py-3 bg-white dark:bg-gray-700 text-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition outline-none">

                    </div>

                </div>


                <!-- BUTTON -->

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-semibold flex items-center justify-center gap-2 transition-all duration-200 hover:shadow-lg hover:scale-[1.02] disabled:opacity-70 cursor-pointer">

                    <span x-show="!loading" x-cloak>
                        Cari Akun
                    </span>

                    <span x-show="loading" x-cloak class="flex items-center gap-2">

                        <svg class="animate-spin h-5 w-5 text-white"
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


            <!-- BACK LOGIN -->

            <p class="text-center mt-8 text-sm text-gray-500 dark:text-gray-400">

                Ingat password?

                <a
                    href="/rkd-cafe/resources/views/auth/login.php"
                    class="text-blue-500 hover:text-blue-600 hover:underline font-medium">

                    Kembali ke login

                </a>

            </p>

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